/**
 * Applies the visitor's stored theme before the first paint, so someone who
 * chose dark never sees a light flash. Loaded in the head (priority asset);
 * desiderio.js takes over theme switching once the page is ready.
 */
(function () {
  'use strict';

  try {
    var choice = window.localStorage.getItem('d-theme');
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (choice === 'dark' || (choice === 'system' && prefersDark)) {
      document.documentElement.classList.add('dark');
    }
  } catch (e) {
    // Storage refused: the site's default theme applies a moment later.
  }
})();
