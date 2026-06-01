/**
 * Desiderio Blog - progressive enhancement for the shadcn-styled t3g/blog overrides.
 *
 * Two opt-in behaviours, both keyed off data-attributes so the markup stays valid
 * and fully usable without JavaScript:
 *
 *   1. Reading time  -> [data-blog-reading-time] holds an element whose text is
 *      replaced with an ICU-rendered "{n} min read" template (data-template),
 *      computed from the word count of the article body it points at
 *      (data-target, default: the nearest [data-blog-body]).
 *
 *   2. Reading progress -> [data-blog-progress] is a thin bar whose inner
 *      [data-blog-progress-bar] width tracks how far the article body has been
 *      scrolled. Hidden from assistive tech (decorative) and disabled entirely
 *      under prefers-reduced-motion, where it simply stays at 0 / removes itself.
 *
 * No template ships inline <script>; this single file is registered through the
 * DesiderioBlog set and only loads when t3g/blog is installed.
 */
(function () {
  'use strict';

  if (window.__desiderioBlog) {
    return;
  }
  window.__desiderioBlog = true;

  var WORDS_PER_MINUTE = 220;

  function reducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function countWords(element) {
    if (!element) {
      return 0;
    }
    var text = element.textContent || '';
    var matches = text.trim().match(/\S+/g);
    return matches ? matches.length : 0;
  }

  function renderTemplate(template, minutes) {
    // The server hands us a localised template carrying a literal "{n}" token
    // (e.g. "{n} min read" / "{n} Min. Lesezeit"); swap it for the measured
    // count. "#" is accepted as a fallback token for safety.
    return String(template || '')
      .replace(/\{n\}/g, String(minutes))
      .replace(/#/g, String(minutes));
  }

  function initReadingTime(node) {
    var targetSelector = node.getAttribute('data-target');
    var body = targetSelector
      ? document.querySelector(targetSelector)
      : (node.closest('[data-blog-article]') || document).querySelector('[data-blog-body]');
    if (!body) {
      return;
    }

    var words = countWords(body);
    if (words === 0) {
      return;
    }

    var minutes = Math.max(1, Math.round(words / WORDS_PER_MINUTE));
    var label = node.querySelector('[data-blog-reading-time-label]') || node;
    var template = label.getAttribute('data-template') || label.textContent;
    label.textContent = renderTemplate(template, minutes);

    var ariaTemplate = node.getAttribute('data-aria-template');
    if (ariaTemplate) {
      node.setAttribute('aria-label', renderTemplate(ariaTemplate, minutes));
    }
    node.removeAttribute('hidden');
  }

  function initProgress(node) {
    var bar = node.querySelector('[data-blog-progress-bar]');
    if (!bar) {
      return;
    }

    if (reducedMotion()) {
      // Respect the user's preference: no animated scroll affordance.
      node.setAttribute('hidden', 'hidden');
      return;
    }

    var targetSelector = node.getAttribute('data-target');
    var body = targetSelector
      ? document.querySelector(targetSelector)
      : (node.closest('[data-blog-article]') || document).querySelector('[data-blog-body]');
    if (!body) {
      return;
    }

    node.removeAttribute('hidden');

    var ticking = false;

    function update() {
      ticking = false;
      var rect = body.getBoundingClientRect();
      var viewport = window.innerHeight || document.documentElement.clientHeight;
      var total = rect.height - viewport;
      var scrolled = total > 0 ? (viewport - rect.top) / total : 1;
      var ratio = Math.min(1, Math.max(0, scrolled));
      bar.style.transform = 'scaleX(' + ratio.toFixed(4) + ')';
      node.setAttribute('aria-valuenow', String(Math.round(ratio * 100)));
    }

    function onScroll() {
      if (!ticking) {
        ticking = true;
        window.requestAnimationFrame(update);
      }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
  }

  function init() {
    var times = document.querySelectorAll('[data-blog-reading-time]');
    Array.prototype.forEach.call(times, initReadingTime);

    var bars = document.querySelectorAll('[data-blog-progress]');
    Array.prototype.forEach.call(bars, initProgress);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
