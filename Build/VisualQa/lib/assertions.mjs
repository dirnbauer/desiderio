/**
 * In-page checks run against one rendered content element.
 *
 * Everything here is decided in the page rather than from screenshots, because
 * a pixel diff can tell you something moved but not that a heading is clipped
 * or that a caption sits at 3.1:1. Each check returns a list of findings with
 * the offending selector, so the report can say what to fix rather than "this
 * element looks wrong".
 */

const IN_PAGE = () => {
    const ROOT = 'section[data-d-section], .desiderio-section, body';

    const describe = (element) => {
        if (!element || element === document.body) return 'body';
        const id = element.id ? `#${element.id}` : '';
        const cls = typeof element.className === 'string' && element.className.trim() !== ''
            ? '.' + element.className.trim().split(/\s+/).slice(0, 3).join('.')
            : '';
        return `${element.tagName.toLowerCase()}${id}${cls}`;
    };

    const root = document.querySelector(ROOT) ?? document.body;
    const findings = [];
    const add = (check, selector, detail) => findings.push({ check, selector, detail });

    /**
     * Screen-reader-only text is deliberately clipped to a 1x1 box, and a
     * carousel's inactive slides are deliberately hidden or zero-sized. Both
     * look exactly like the defects below, so they have to be excluded or the
     * report fills with noise and stops being read.
     */
    const isVisuallyHidden = (element) => {
        for (let node = element; node && node !== document.body; node = node.parentElement) {
            if (node.classList?.contains('sr-only') || node.classList?.contains('visually-hidden')) return true;
            if (node.hasAttribute?.('hidden') || node.getAttribute?.('aria-hidden') === 'true') return true;
            const style = getComputedStyle(node);
            if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') return true;
            // The clip/clip-path 1px trick used by every sr-only implementation.
            if (style.clipPath !== 'none' && node.getBoundingClientRect().width <= 2) return true;
        }
        return false;
    };

    // --- 1. Horizontal overflow -------------------------------------------
    // A page-level scrollbar is the symptom; the useful output is which
    // descendant actually sticks out. A carousel inside its own scroll
    // container is legitimate, so those are excluded.
    const viewport = document.documentElement.clientWidth;
    if (document.documentElement.scrollWidth > viewport + 1) {
        const scrollable = (element) => {
            for (let node = element.parentElement; node && node !== document.body; node = node.parentElement) {
                const overflowX = getComputedStyle(node).overflowX;
                if (overflowX === 'auto' || overflowX === 'scroll') return true;
            }
            return false;
        };
        for (const element of root.querySelectorAll('*')) {
            const rect = element.getBoundingClientRect();
            if (rect.width > 0 && rect.right > viewport + 1 && !scrollable(element)) {
                add('overflow-x', describe(element), `extends ${Math.round(rect.right - viewport)}px past the ${viewport}px viewport`);
                break;
            }
        }
        if (!findings.some((f) => f.check === 'overflow-x')) {
            add('overflow-x', 'document', `scrollWidth ${document.documentElement.scrollWidth} > ${viewport}`);
        }
    }

    // --- 2. Clipped text ---------------------------------------------------
    // overflow:hidden without an ellipsis or a line-clamp means the text is
    // simply gone, which no screenshot review reliably catches.
    for (const element of root.querySelectorAll('h1,h2,h3,h4,h5,h6,p,li,a,span,dd,dt,figcaption,blockquote,button')) {
        if (element.children.length > 0 || (element.textContent ?? '').trim() === '') continue;
        if (isVisuallyHidden(element)) continue;
        const style = getComputedStyle(element);
        if (style.overflow !== 'hidden' && style.overflowY !== 'hidden' && style.overflowX !== 'hidden') continue;
        if (style.textOverflow === 'ellipsis' || style.webkitLineClamp !== 'none') continue;
        if (element.scrollHeight > element.clientHeight + 1 || element.scrollWidth > element.clientWidth + 1) {
            add('text-clipped', describe(element), `${element.scrollWidth}x${element.scrollHeight} inside ${element.clientWidth}x${element.clientHeight}`);
        }
    }

    // --- 3. Nothing rendered ----------------------------------------------
    const rootRect = root.getBoundingClientRect();
    if (rootRect.height < 8) {
        add('empty-render', describe(root), `height ${Math.round(rootRect.height)}px`);
    } else if ((root.innerText ?? '').trim() === '' && root.querySelectorAll('img,svg,video,canvas').length === 0) {
        add('empty-render', describe(root), 'no text and no media');
    }

    // --- 4. Overlapping text ----------------------------------------------
    // Restricted to static, text-bearing leaves that share a parent: anything
    // else (overlays, badges pinned on cards, decorative layers) overlaps by
    // design and would drown the signal.
    const leaves = [...root.querySelectorAll('h1,h2,h3,h4,h5,h6,p,li,span,a')].filter((element) => {
        const style = getComputedStyle(element);
        return element.children.length === 0
            && (element.textContent ?? '').trim() !== ''
            && style.position === 'static'
            && !isVisuallyHidden(element);
    });
    for (let i = 0; i < leaves.length; i++) {
        for (let j = i + 1; j < leaves.length; j++) {
            if (leaves[i].parentElement !== leaves[j].parentElement) continue;
            const a = leaves[i].getBoundingClientRect();
            const b = leaves[j].getBoundingClientRect();
            const overlap = Math.max(0, Math.min(a.right, b.right) - Math.max(a.left, b.left))
                * Math.max(0, Math.min(a.bottom, b.bottom) - Math.max(a.top, b.top));
            const smaller = Math.min(a.width * a.height, b.width * b.height);
            if (smaller > 0 && overlap / smaller > 0.25) {
                add('text-overlap', `${describe(leaves[i])} / ${describe(leaves[j])}`, `${Math.round((overlap / smaller) * 100)}% overlap`);
            }
        }
    }

    // --- 5. Images ---------------------------------------------------------
    for (const image of root.querySelectorAll('img')) {
        if (isVisuallyHidden(image)) continue;
        const rect = image.getBoundingClientRect();
        if (image.complete && image.naturalWidth === 0) {
            add('image-broken', describe(image), image.currentSrc || image.src || '(no src)');
        } else if (rect.width < 1 || rect.height < 1) {
            add('image-collapsed', describe(image), `${Math.round(rect.width)}x${Math.round(rect.height)}`);
        }
        if (!image.hasAttribute('alt')) {
            add('image-no-alt', describe(image), image.currentSrc || image.src || '(no src)');
        }
    }

    // A <video> whose source is not a video is the exact defect the role-based
    // asset provider exists to prevent, so it is worth an explicit check.
    for (const video of root.querySelectorAll('video')) {
        const sources = [video.getAttribute('src'), ...[...video.querySelectorAll('source')].map((s) => s.getAttribute('src'))]
            .filter(Boolean);
        for (const src of sources) {
            if (/\.(jpe?g|png|gif|webp|avif)(\?|$)/i.test(src)) {
                add('video-not-a-video', describe(video), src);
            }
        }
    }

    return findings;
};

export async function collectFindings(page) {
    return page.evaluate(IN_PAGE);
}

/** Console errors and failed requests, wired up before navigation. */
export function watchPage(page) {
    const errors = [];
    page.on('pageerror', (error) => errors.push({ check: 'js-error', selector: 'page', detail: String(error.message ?? error) }));
    page.on('console', (message) => {
        if (message.type() === 'error') {
            errors.push({ check: 'console-error', selector: 'page', detail: message.text().slice(0, 200) });
        }
    });
    page.on('response', (response) => {
        if (response.status() >= 400) {
            errors.push({ check: 'request-failed', selector: 'network', detail: `${response.status()} ${response.url().slice(0, 160)}` });
        }
    });
    return errors;
}
