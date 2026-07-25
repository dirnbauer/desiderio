/**
 * Measures the layout anatomy of a rendered element: header alignment, content
 * width, section padding, and heading/body sizes.
 *
 * Exists because "check centering, size, spacing" across 288 elements cannot be
 * eyeballed reliably — but it CAN be measured, compared against the catalog's
 * own dominant conventions, and reported as outliers. The conventions asserted
 * here were derived from the catalog itself (see DesignPhilosophy.md), so this
 * file is the executable form of the design philosophy:
 *
 *  1. A section header (eyebrow + heading + subheadline) is either centered or
 *     start-aligned as a UNIT. A centered heading over a start-aligned
 *     subheadline is a defect.
 *  2. Reading text is measured: a subheadline paragraph wider than ~75ch has
 *     outgrown its measure.
 *  3. Sections breathe the same: vertical padding within the shared rhythm,
 *     not a per-element invention.
 *  4. One h-level per slot: the section heading sizes within the shared band,
 *     card titles within theirs.
 */

const IN_PAGE = () => {
    const root = document.querySelector('section[data-d-section], .desiderio-section, body > section') ?? document.body;
    const result = { checks: [] };
    const add = (check, detail, value) => result.checks.push({ check, detail, value });

    const visible = (el) => {
        const r = el.getBoundingClientRect();
        const s = getComputedStyle(el);
        return r.width > 1 && r.height > 1 && s.visibility !== 'hidden' && s.display !== 'none';
    };

    // ---- header block anatomy -------------------------------------------
    const heading = root.querySelector('h1, h2');
    const headerKids = [];
    if (heading && visible(heading)) {
        // The header UNIT is eyebrow + heading + immediate subheadline. It ends
        // at the first structural block (a form, a list, a grid, an accordion):
        // a centered heading over start-aligned CONTENT is a legitimate and
        // common composition, and treating content siblings as header text is
        // exactly what produced four false "mixed alignment" findings on the
        // first survey (accordion, hero-form, callback-request,
        // testimonial-video — all correctly composed).
        const parent = heading.parentElement;
        const structural = (el) =>
            el.querySelector('input, button, select, textarea, details, table, ul, ol, dl, img, svg, video, iframe, article')
            || el.children.length > 3;
        let past = false;
        for (const child of parent.children) {
            if (!visible(child)) continue;
            if (child !== heading && structural(child)) {
                if (past) break;      // content after the heading ends the unit
                continue;             // decorative/leading block before it
            }
            if (/^(H1|H2|H3|P|SPAN)$/.test(child.tagName) && (child.textContent ?? '').trim() !== '') {
                headerKids.push(child);
            }
            if (child === heading) past = true;
        }
    }

    if (heading && headerKids.length >= 2) {
        const rootRect = root.getBoundingClientRect();
        const mid = rootRect.left + rootRect.width / 2;
        const alignments = headerKids.map((el) => {
            const r = el.getBoundingClientRect();
            const centreOffset = Math.abs((r.left + r.width / 2) - mid);
            const ta = getComputedStyle(el).textAlign;
            // An element is "centered" when its box midpoint sits on the section
            // midline AND its text is not explicitly start-aligned wide text.
            const boxCentered = centreOffset < 8;
            const textCentered = ta === 'center';
            return { tag: el.tagName, boxCentered, textCentered, ta };
        });
        const textAligns = new Set(alignments.map((a) => a.textCentered ? 'center' : 'start'));
        if (textAligns.size > 1) {
            add('header-alignment-mixed',
                alignments.map((a) => `${a.tag}:${a.textCentered ? 'center' : 'start'}`).join(' '),
                null);
        }
        result.headerAlign = alignments[0]?.textCentered ? 'center' : 'start';
    }

    // ---- measure (reading width) ----------------------------------------
    for (const p of root.querySelectorAll('p')) {
        if (!visible(p)) continue;
        const text = (p.textContent ?? '').trim();
        if (text.length < 120) continue; // short captions may be any width
        const style = getComputedStyle(p);
        const chPx = (() => {
            const probe = document.createElement('span');
            probe.textContent = '0';
            probe.style.cssText = `position:absolute;visibility:hidden;font:${style.font}`;
            document.body.appendChild(probe);
            const w = probe.getBoundingClientRect().width || 8;
            probe.remove();
            return w;
        })();
        const chars = p.getBoundingClientRect().width / chPx;
        if (chars > 85) {
            add('measure-too-wide', `${Math.round(chars)}ch paragraph`, Math.round(chars));
            break; // one report per element is enough
        }
    }

    // ---- section padding rhythm -----------------------------------------
    const sec = getComputedStyle(root);
    result.padBlock = [parseFloat(sec.paddingTop) || 0, parseFloat(sec.paddingBottom) || 0];

    // ---- type scale in situ ---------------------------------------------
    if (heading && visible(heading)) {
        result.headingPx = parseFloat(getComputedStyle(heading).fontSize);
    }
    const sub = heading?.parentElement?.querySelector('p');
    if (sub && visible(sub)) {
        result.subPx = parseFloat(getComputedStyle(sub).fontSize);
    }

    return result;
};

export async function collectAnatomy(page) {
    return page.evaluate(IN_PAGE);
}
