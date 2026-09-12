/**
 * Checkbox filters for the Solr result page.
 *
 * Each facet checkbox carries the URL its state change leads to
 * (data-d-facet-url, computed server-side: the remove URL when the option is
 * active, the add URL when it is not). Checking or unchecking navigates there.
 * Without this file the markup still works — every checkbox has a plain link
 * fallback inside <noscript> — so this only removes the extra click.
 *
 * No AJAX: the result list is rendered by TYPO3, and a full navigation keeps
 * the URL, the back button and the screen-reader page announcement correct.
 */
(function () {
    'use strict';

    function navigate(checkbox) {
        var raw = checkbox.getAttribute('data-d-facet-url');
        if (!raw) {
            return;
        }

        var target;
        try {
            target = new URL(raw, window.location.href);
        } catch (error) {
            return;
        }
        // Never follow a URL that left this site, whatever produced the markup.
        if (target.origin !== window.location.origin) {
            return;
        }

        // A second click during the navigation would queue a stale filter
        // state, so freeze the whole group while the page is on its way.
        var group = checkbox.closest('#tx-solr-faceting') || document;
        group.setAttribute('aria-busy', 'true');
        Array.prototype.forEach.call(
            group.querySelectorAll('.d-facet-option__checkbox'),
            function (control) {
                control.disabled = true;
            }
        );

        window.location.assign(target.toString());
    }

    document.addEventListener('change', function (event) {
        var checkbox = event.target.closest
            ? event.target.closest('.d-facet-option__checkbox[data-d-facet-url]')
            : null;
        if (checkbox) {
            navigate(checkbox);
        }
    });
})();
