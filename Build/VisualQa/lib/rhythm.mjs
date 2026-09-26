/**
 * Measures the space a rendered element leaves above and below its content.
 *
 * "Inset" is the distance from the section's border edge to the first painted
 * thing inside it — a line of text, an image or icon, a form control, a
 * surface with its own background, border or shadow — and from the last one
 * to the bottom edge. That is the gap a visitor reads between two elements,
 * which the section's padding alone does not tell (an intro with a top margin
 * or a wrapper with its own padding moves the content, not the padding).
 */

/**
 * Elements that are page chrome or bars rather than content sections: they
 * sit flush by design (a navbar, a cookie banner, a divider, a footer) and are
 * reported but never counted against the shared rhythm.
 */
export const RHYTHM_EXCEPTIONS = new Set([
    'desiderio_announcementbar',
    'desiderio_backtotop',
    'desiderio_breadcrumb',
    'desiderio_contentdivider',
    'desiderio_cookiebanner',
    'desiderio_ctafloating',
    'desiderio_footer',
    'desiderio_footerapplinks',
    'desiderio_footerbrand',
    'desiderio_footercolumns',
    'desiderio_footercontact',
    'desiderio_footerdark',
    'desiderio_footermega',
    'desiderio_footerminimal',
    'desiderio_footernewsletter',
    'desiderio_footersocial',
    'desiderio_footersplit',
    'desiderio_gdprbanner',
    // A full-bleed photograph behind the copy: the image IS the section.
    'desiderio_heroparallax',
    'desiderio_legallinks',
    'desiderio_megamenu',
    'desiderio_navbar',
    'desiderio_navbarcentered',
    'desiderio_navbardropdown',
    'desiderio_navbaricon',
    'desiderio_navbarmobile',
    'desiderio_navbarsidebar',
    'desiderio_navbarsplit',
    'desiderio_navbarstacked',
    'desiderio_navbartabbed',
    'desiderio_navbartransparent',
    'desiderio_utilitybar',
    // Core's structural types: a divider and a pointer to other records.
    'div',
    'shortcut',
]);

/**
 * Deliberate, documented departures from the shared inset, per edge. The
 * survey cannot see pseudo-elements or moving geometry, so these read as
 * extra space although something is painted there.
 */
export const RHYTHM_NOTES = {
    // The connector line (::before) runs 1rem past the last card and fades out.
    desiderio_timeline: { bottom: 'connector line runs past the last card' },
    // Items orbit on the ring; the lowest one is not always at the ring's foot.
    innesto_orbitingcircles: { bottom: 'orbit geometry, the items move' },
};

/**
 * Elements that open a page: they breathe with the hero token (--d-hero-y),
 * not with the section rhythm.
 */
export const HERO_RHYTHM = (cType) => /^desiderio_(hero|headerpage|headerprofile|headerbanner)/.test(cType);

const IN_PAGE = () => {
    const root = document.querySelector('section[data-d-section], .desiderio-section')
        ?? document.querySelector('.desiderio-element-preview > *')
        ?? document.body;
    const rootRect = root.getBoundingClientRect();
    const rootStyle = getComputedStyle(root);

    const describe = (element) => {
        if (!element) return '';
        const cls = typeof element.className === 'string' && element.className.trim() !== ''
            ? '.' + element.className.trim().split(/\s+/).slice(0, 2).join('.')
            : (element.className?.baseVal ? '.' + element.className.baseVal.trim().split(/\s+/)[0] : '');
        return `${element.tagName.toLowerCase()}${cls}`;
    };

    const hiddenCache = new Map();
    const isHidden = (element) => {
        if (!element || element === root || element === document.body) return false;
        if (hiddenCache.has(element)) return hiddenCache.get(element);
        let hidden = false;
        const style = getComputedStyle(element);
        // A closed <details> keeps its panel in the DOM with its styles intact;
        // only the summary is rendered.
        const closedPanel = element.parentElement?.tagName === 'DETAILS'
            && !element.parentElement.open
            && element.tagName !== 'SUMMARY';
        if (closedPanel || element.hasAttribute?.('hidden') || style.display === 'none' || style.visibility === 'hidden' || Number(style.opacity) === 0) {
            hidden = true;
        } else {
            const rect = element.getBoundingClientRect();
            const clipped = style.clipPath !== 'none' || (style.clip && style.clip !== 'auto');
            if (clipped && (rect.width <= 2 || rect.height <= 2)) hidden = true;
            else if (style.position === 'absolute' && (parseFloat(style.left) < -999 || parseFloat(style.top) < -999)) hidden = true;
            else hidden = isHidden(element.parentElement);
        }
        hiddenCache.set(element, hidden);
        return hidden;
    };

    const alpha = (color) => {
        const m = /rgba?\(([^)]+)\)/.exec(color) ?? [];
        const parts = (m[1] ?? '').split(/[ ,/]+/).filter(Boolean);
        if (color.startsWith('color(') || color.startsWith('oklch') || color.startsWith('oklab')) {
            const slash = color.split('/');
            return slash.length > 1 ? parseFloat(slash[1]) : 1;
        }
        return parts.length === 4 ? parseFloat(parts[3]) : (parts.length === 3 ? 1 : 0);
    };

    // A decorative layer (a glow, a pattern, a backdrop image) spans the whole
    // section and would put the "first painted pixel" on the section's edge.
    const decorative = (element, rect) => {
        const style = getComputedStyle(element);
        const layered = style.position === 'absolute' || style.position === 'fixed';
        const big = rect.width >= rootRect.width * 0.8 && rect.height >= rootRect.height * 0.6;
        return layered && (big || element.getAttribute('aria-hidden') === 'true');
    };

    // What an ancestor with overflow other than visible cuts off is not
    // painted: a collapsed accordion panel, a chart's screen-reader table.
    const clipCache = new Map();
    const clipOf = (node) => {
        if (clipCache.has(node)) return clipCache.get(node);
        const style = getComputedStyle(node);
        const clips = style.overflowY !== 'visible' || style.overflowX !== 'visible' || style.clipPath !== 'none';
        const value = clips ? node.getBoundingClientRect() : null;
        clipCache.set(node, value);
        return value;
    };
    const clipped = (start, rect) => {
        let top = rect.top;
        let bottom = rect.bottom;
        let left = rect.left;
        let right = rect.right;
        for (let node = start; node && node !== document.body; node = node.parentElement) {
            const clip = clipOf(node);
            if (clip) {
                top = Math.max(top, clip.top);
                bottom = Math.min(bottom, clip.bottom);
                left = Math.max(left, clip.left);
                right = Math.min(right, clip.right);
            }
        }
        return { top, bottom, width: right - left, height: bottom - top };
    };

    const boxes = [];
    const push = (start, raw, label, node) => {
        const rect = clipped(start, raw);
        if (rect.width <= 0.5 || rect.height <= 0.5) return;
        if (rect.bottom <= rootRect.top || rect.top >= rootRect.bottom) return;
        boxes.push({ top: rect.top, bottom: rect.bottom, label, node });
    };

    // Text: the line boxes of every visible text node.
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    for (let node = walker.nextNode(); node; node = walker.nextNode()) {
        if ((node.textContent ?? '').trim() === '') continue;
        const parent = node.parentElement;
        if (!parent || isHidden(parent) || parent.closest('script,style,template,noscript')) continue;
        const range = document.createRange();
        range.selectNodeContents(node);
        for (const rect of range.getClientRects()) push(parent, rect, `text in ${describe(parent)}`, parent);
    }

    // Replaced content, controls and painted surfaces.
    const rootBg = rootStyle.backgroundColor;
    for (const element of root.querySelectorAll('*')) {
        if (isHidden(element)) continue;
        const tag = element.tagName.toLowerCase();
        const rect = element.getBoundingClientRect();
        if (rect.width <= 0.5 || rect.height <= 0.5) continue;
        if (element.closest('svg') && tag !== 'svg') continue;
        if (decorative(element, rect)) continue;
        if (['img', 'svg', 'video', 'canvas', 'iframe', 'picture', 'input', 'select', 'textarea', 'button', 'hr', 'progress', 'meter'].includes(tag)
            || element.getAttribute('role') === 'separator') {
            push(element.parentElement, rect, describe(element), element);
            continue;
        }
        const style = getComputedStyle(element);
        const bg = style.backgroundColor;
        const surface = (alpha(bg) > 0.02 && bg !== rootBg)
            || style.backgroundImage !== 'none'
            || style.boxShadow !== 'none'
            || ['Top', 'Bottom', 'Left', 'Right'].some((side) => parseFloat(style[`border${side}Width`]) > 0 && alpha(style[`border${side}Color`]) > 0.02);
        if (surface) push(element.parentElement, rect, describe(element), element);
    }

    // The two rhythm tokens, resolved to pixels at this viewport.
    const tokenPx = (token) => {
        const probe = document.createElement('div');
        probe.style.cssText = `position:absolute;visibility:hidden;padding-top:var(${token})`;
        document.body.appendChild(probe);
        const px = parseFloat(getComputedStyle(probe).paddingTop) || 0;
        probe.remove();
        return px;
    };

    const result = {
        sectionY: tokenPx('--d-section-y'),
        heroY: tokenPx('--d-hero-y'),
        root: describe(root),
        rootBg,
        padTop: parseFloat(rootStyle.paddingTop) || 0,
        padBottom: parseFloat(rootStyle.paddingBottom) || 0,
        marginTop: parseFloat(rootStyle.marginTop) || 0,
        marginBottom: parseFloat(rootStyle.marginBottom) || 0,
        height: rootRect.height,
    };
    if (boxes.length === 0) {
        return { ...result, insetTop: 0, insetBottom: 0, first: '(nothing painted)', last: '(nothing painted)' };
    }
    const first = boxes.reduce((a, b) => (b.top < a.top ? b : a));
    const last = boxes.reduce((a, b) => (b.bottom > a.bottom ? b : a));

    // What sits between the root's content edge and the first/last painted
    // box: every ancestor's padding and border, and every gap a margin or an
    // empty sibling leaves. Reported so an outlier names its cause.
    const explain = (box, edge) => {
        const parts = [];
        let node = box.node;
        for (; node && node !== root; node = node.parentElement) {
            const parent = node.parentElement;
            if (!parent) break;
            const style = getComputedStyle(node);
            const own = edge === 'top'
                ? parseFloat(style.marginTop) || 0
                : parseFloat(style.marginBottom) || 0;
            if (own > 0.5) parts.push(`${describe(node)} margin ${Math.round(own)}`);
            const parentStyle = getComputedStyle(parent);
            const pad = edge === 'top'
                ? (parseFloat(parentStyle.paddingTop) || 0) + (parseFloat(parentStyle.borderTopWidth) || 0)
                : (parseFloat(parentStyle.paddingBottom) || 0) + (parseFloat(parentStyle.borderBottomWidth) || 0);
            if (parent !== root && pad > 0.5) parts.push(`${describe(parent)} padding ${Math.round(pad)}`);
            // Siblings on the far side of the painted box that take up space
            // without painting anything (an empty spacer, a hidden live region).
            const rect = node.getBoundingClientRect();
            for (const sibling of parent.children) {
                if (sibling === node) continue;
                const r = sibling.getBoundingClientRect();
                if (r.height < 1) continue;
                const beyond = edge === 'top' ? r.bottom <= rect.top + 0.5 : r.top >= rect.bottom - 0.5;
                if (beyond) parts.push(`${describe(sibling)} ${Math.round(r.height)}px ${edge === 'top' ? 'above' : 'below'}`);
            }
        }
        return parts.slice(0, 6).join('; ');
    };
    return {
        ...result,
        insetTop: Math.max(0, first.top - rootRect.top),
        insetBottom: Math.max(0, rootRect.bottom - last.bottom),
        first: first.label,
        last: last.label,
        whyTop: explain(first, 'top'),
        whyBottom: explain(last, 'bottom'),
    };
};

export async function collectRhythm(page) {
    return page.evaluate(IN_PAGE);
}
