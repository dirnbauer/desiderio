"""Preserve published raster order only for exactly equivalent icon outlines."""

from collections import Counter, defaultdict
import copy
import hashlib
import json
from pathlib import Path
import sys

from fontTools.ttLib import TTFont


def glyph_state(font, name):
    glyph = font['glyf'][name]
    if glyph.isComposite():
        return None
    coordinates, endpoints, flags = glyph.getCoordinates(font['glyf'])
    start = 0
    contours = []
    for end in endpoints:
        contours.append(tuple((int(x), int(y), int(flag)) for (x, y), flag in
                              zip(coordinates[start:end + 1], flags[start:end + 1])))
        start = end + 1
    return {
        'contours': tuple(contours),
        'metrics': font['hmtx'].metrics[name],
        'vertical_metrics': font['vmtx'].metrics[name] if 'vmtx' in font else None,
        'bbox': tuple(getattr(glyph, key, None) for key in ('xMin', 'yMin', 'xMax', 'yMax')),
        'instructions': bytes(glyph.program.getBytecode()) if hasattr(glyph, 'program') else b'',
    }


def equivalent(left, right):
    return (left is not None and right is not None
            and not left['instructions'] and not right['instructions']
            and Counter(left['contours']) == Counter(right['contours'])
            and all(left[key] == right[key] for key in
                    ('metrics', 'vertical_metrics', 'bbox', 'instructions')))


def preserve_published_glyphs(current, published):
    if current['head'].unitsPerEm != published['head'].unitsPerEm:
        raise ValueError('Published and generated font units differ')
    if any(table in font for font in (current, published) for table in ('fpgm', 'prep', 'cvt ')):
        raise ValueError('Expected unhinted icon fonts')

    # Fantasticon emits an empty liga table. Allow that exact no-op form, but
    # reject glyph-referencing layout rules before reusing any physical glyph.
    if 'GPOS' in current:
        raise ValueError('Expected icon fonts without layout positioning')
    if 'GSUB' in current:
        lookups = getattr(getattr(current['GSUB'], 'table', None), 'LookupList', None)
        if lookups is None or any(lookup.LookupType != 4 or any(
                getattr(subtable, 'ligatures', None) != {} for subtable in lookup.SubTable)
                for lookup in lookups.Lookup):
            raise ValueError('Expected icon fonts without layout substitutions')
    if any(current['glyf'][name].isComposite() for name in current.getGlyphOrder()):
        raise ValueError('Expected simple icon glyphs without component references')

    cmap = current.getBestCmap()
    previous = published.getBestCmap()
    unicode_tables = [table for table in current['cmap'].tables if table.isUnicode()]
    for table in unicode_tables:
        if table.cmap != cmap:
            raise ValueError('Unicode cmap subtables disagree')

    users = defaultdict(set)
    for cp, name in cmap.items():
        users[name].add(cp)
    selected = defaultdict(list)
    current_states = {}
    previous_states = {}
    for cp, old_name in sorted(previous.items()):
        if cp not in cmap:
            raise ValueError(f'Missing published codepoint U+{cp:04X}')
        name = cmap[cp]
        if name not in current_states:
            current_states[name] = glyph_state(current, name)
        if old_name not in previous_states:
            previous_states[old_name] = glyph_state(published, old_name)
        old_state = previous_states[old_name]
        if equivalent(current_states[name], old_state):
            selected[name].append((cp, old_name, old_state))

    order = current.getGlyphOrder()[:]
    initial_order = order[:]
    preserved = split = 0
    for name in initial_order:
        entries = selected.get(name, [])
        if not entries:
            continue
        variants = defaultdict(list)
        for entry in entries:
            variants[entry[2]['contours']].append(entry)
        canonical = current_states[name]['contours']
        all_users_selected = {entry[0] for entry in entries} == users[name]
        # Keep canonical outlines for every changed or newly published alias.
        # Otherwise reuse the original glyph for one deterministic legacy order.
        reused = canonical if canonical in variants or not all_users_selected else entries[0][2]['contours']
        for contours, variant in sorted(variants.items(), key=lambda item: item[1][0][0]):
            cp, old_name, _ = variant[0]
            target = name
            if contours != reused:
                target = f'published_{cp:04X}'
                if target in current['glyf'].glyphs:
                    raise ValueError(f'Reserved published glyph name already exists: {target}')
                order.append(target)
                split += 1
            if contours != canonical:
                current['glyf'].glyphs[target] = copy.deepcopy(published['glyf'][old_name])
                current['hmtx'].metrics[target] = published['hmtx'].metrics[old_name]
                if 'vmtx' in current:
                    current['vmtx'].metrics[target] = published['vmtx'].metrics[old_name]
            for codepoint, _, _ in variant:
                for table in unicode_tables:
                    if codepoint in table.cmap:
                        table.cmap[codepoint] = target
                preserved += 1
    if len(order) > 65535:
        raise ValueError('Published glyph aliases exceed TrueType glyph capacity')
    current.setGlyphOrder(order)
    current['maxp'].numGlyphs = len(order)
    return {'preserved_codepoints': preserved, 'split_aliases': split}


def preserve_font_file(font_path, reference_path, metadata_path, manifest_path):
    metadata = json.loads(metadata_path.read_text())
    if hashlib.sha256(reference_path.read_bytes()).hexdigest() != metadata['sha256']:
        raise ValueError('Published reference font checksum mismatch')
    if font_path.stem != metadata['font']:
        raise ValueError('Published preservation applies only to the original font family')
    manifest = json.loads(manifest_path.read_text())
    first = metadata['firstCodepoint']
    count = metadata['codepointCount']
    shard = manifest['fonts'][0]
    if shard['name'] != metadata['font'] or len(shard['icons']) < count:
        raise ValueError('Published font family or mapping count changed')
    pairs = sorted([[slug, first + index] for index, slug in enumerate(shard['icons'][:count])])
    mapping_hash = hashlib.sha256(json.dumps(pairs, separators=(',', ':')).encode()).hexdigest()
    if mapping_hash != metadata['mappingSha256']:
        raise ValueError('Published class/codepoint mappings changed')
    with TTFont(reference_path, recalcTimestamp=False, recalcBBoxes=False) as published, \
            TTFont(font_path, recalcTimestamp=False, recalcBBoxes=False) as current:
        if set(published.getBestCmap()) != set(range(first, first + count)):
            raise ValueError('Unexpected codepoints in published reference font')
        result = preserve_published_glyphs(current, published)
        temporary = font_path.with_suffix('.preserved.woff2')
        current.save(temporary)
    temporary.replace(font_path)
    print(json.dumps(result))
    return result


if __name__ == '__main__':
    preserve_font_file(*(Path(value) for value in sys.argv[1:]))
