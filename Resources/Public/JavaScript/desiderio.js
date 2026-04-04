/**
 * Desiderio — TYPO3 v14 Theme JavaScript
 *
 * Dark mode toggle and mobile menu are handled by s2f.js (from shadcn2fluid-templates).
 * This file adds desiderio-specific enhancements.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  /* ------------------------------------------------------------------ */
  /*  Smooth scroll for styleguide nav links                            */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('.desiderio-styleguide__nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
      const href = link.getAttribute('href');
      if (href && href.startsWith('#')) {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          history.replaceState(null, '', href);
        }
      }
    });
  });

  /* ------------------------------------------------------------------ */
  /*  Active nav tracking for styleguide                                */
  /* ------------------------------------------------------------------ */
  const navLinks = document.querySelectorAll('.desiderio-styleguide__nav-link');
  if (navLinks.length > 0 && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const id = entry.target.id;
            navLinks.forEach(link => {
              link.classList.toggle(
                'desiderio-styleguide__nav-link--active',
                link.getAttribute('href') === '#' + id
              );
            });
          }
        });
      },
      { rootMargin: '-20% 0px -60% 0px' }
    );

    document.querySelectorAll('.desiderio-styleguide__group[id]').forEach(group => {
      observer.observe(group);
    });
  }
});
