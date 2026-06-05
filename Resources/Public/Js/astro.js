/**
 * Desiderio Astro - progressive enhancement runtime.
 *
 * Use this for interaction that pure HTML, CSS, and shadcn-style classes cannot
 * cover well: counters, scroll reveals, copy buttons, tilt, and marquees.
 */
(function () {
  'use strict';

  var readyAttr = 'astroReady';
  var reduceMotionQuery = '(prefers-reduced-motion: reduce)';
  var coarsePointerQuery = '(pointer: coarse)';

  function supports(selector) {
    return typeof selector === 'string' && selector.trim() !== '';
  }

  function reducedMotion() {
    return window.matchMedia && window.matchMedia(reduceMotionQuery).matches;
  }

  function parseNumberParts(input) {
    var source = String(input || '').trim();
    var match = source.match(/(-?\d[\d.,]*)/);

    if (!match) {
      return null;
    }

    var numberText = match[1];
    var lastComma = numberText.lastIndexOf(',');
    var lastDot = numberText.lastIndexOf('.');
    var lastSeparator = Math.max(lastComma, lastDot);
    var decimalDigits = 0;
    var normalized = numberText;

    if (lastSeparator !== -1) {
      var fractionLength = numberText.length - lastSeparator - 1;
      var hasComma = numberText.includes(',');
      var hasDot = numberText.includes('.');
      var isDecimalSeparator = fractionLength > 0 && (fractionLength !== 3 || (hasComma && hasDot));

      if (isDecimalSeparator) {
        decimalDigits = fractionLength;
        normalized = numberText
          .slice(0, lastSeparator)
          .replace(/[.,]/g, '') + '.' + numberText.slice(lastSeparator + 1);
      } else {
        normalized = numberText.replace(/[.,]/g, '');
      }
    }

    return {
      target: Number(normalized),
      decimals: decimalDigits,
      prefix: source.slice(0, match.index),
      suffix: source.slice(match.index + numberText.length),
      source: source
    };
  }

  function formatNumber(value, decimals, locale) {
    return new Intl.NumberFormat(locale || document.documentElement.lang || undefined, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    }).format(value);
  }

  function easeOutCubic(value) {
    return 1 - Math.pow(1 - value, 3);
  }

  function setCounterText(element, parts, value) {
    element.textContent = parts.prefix + formatNumber(value, parts.decimals) + parts.suffix;
  }

  function closestCopyRoot(button) {
    return button.closest('[data-astro-copy-root], pre, figure, .code-block, .docs__code-panel');
  }

  function copyTextFor(button) {
    var selector = button.dataset.astroCopyTarget;
    var target = supports(selector) ? document.querySelector(selector) : null;

    if (!target) {
      target = closestCopyRoot(button)?.querySelector('code, pre, [data-astro-copy-source]');
    }

    return target?.innerText || target?.textContent || '';
  }

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (char) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[char];
    });
  }

  function normalizeLanguage(language, source) {
    var normalized = String(language || '').toLowerCase().replace(/[^a-z0-9+#-]/g, '');

    if (normalized.includes('php') || source.includes('<?php')) {
      return 'php';
    }

    if (normalized.includes('typescript') || normalized === 'ts') {
      return 'typescript';
    }

    if (normalized.includes('javascript') || normalized === 'js') {
      return 'javascript';
    }

    if (normalized.includes('html') || normalized.includes('xml') || source.trim().startsWith('<')) {
      return 'markup';
    }

    if (normalized.includes('css') || normalized.includes('scss')) {
      return 'css';
    }

    return 'plain';
  }

  function highlightCode(source, language) {
    if (window.Prism && window.Prism.highlight && window.Prism.languages) {
      var prismLanguage = language === 'markup' ? 'html' : language;
      var grammar = window.Prism.languages[prismLanguage] || window.Prism.languages[language];

      if (grammar) {
        return window.Prism.highlight(source, grammar, prismLanguage);
      }
    }

    return escapeHtml(source);
  }

  function AstroRuntime() {
    this.revealObserver = null;
    this.counterObserver = null;
    this.bodyEventsReady = false;
  }

  AstroRuntime.prototype.init = function (scope) {
    var root = scope || document;

    this.initReveal(root);
    this.initCounters(root);
    this.initHighlight(root);
    this.initCopy(root);
    this.initTilt(root);
    this.initMarquee(root);
    this.initCarousel(root);
    this.initContentCarousel(root);
    this.initCountdown(root);
    this.initBodyEvents();
  };

  AstroRuntime.prototype.initBodyEvents = function () {
    if (this.bodyEventsReady || !document.body) {
      return;
    }

    this.bodyEventsReady = true;
    document.body.addEventListener('tx_solr_updated', function () {
      window.DesiderioAstro.init(document);
    });
  };

  AstroRuntime.prototype.initReveal = function (scope) {
    var elements = scope.querySelectorAll('[data-astro-reveal], [data-d-animate]');

    if (!elements.length) {
      return;
    }

    if (reducedMotion() || !('IntersectionObserver' in window)) {
      elements.forEach(function (element) {
        element.dataset.astroVisible = 'true';
        element.classList.add('is-visible');
      });
      return;
    }

    if (!this.revealObserver) {
      this.revealObserver = new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          entry.target.dataset.astroVisible = 'true';
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        });
      }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.12
      });
    }

    elements.forEach(function (element) {
      if (element.dataset[readyAttr]?.includes('reveal')) {
        return;
      }

      element.dataset[readyAttr] = [element.dataset[readyAttr], 'reveal'].filter(Boolean).join(' ');
      this.revealObserver.observe(element);
    }, this);
  };

  AstroRuntime.prototype.initCounters = function (scope) {
    var elements = scope.querySelectorAll('[data-astro-counter], [data-d-counter]');

    if (!elements.length) {
      return;
    }

    if (!this.counterObserver && 'IntersectionObserver' in window) {
      this.counterObserver = new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          observer.unobserve(entry.target);
          window.DesiderioAstro.runCounter(entry.target);
        });
      }, {
        rootMargin: '0px 0px -6% 0px',
        threshold: 0.32
      });
    }

    elements.forEach(function (element) {
      if (element.dataset[readyAttr]?.includes('counter')) {
        return;
      }

      var explicitTarget = element.dataset.astroTarget || element.dataset.target;
      var parts = parseNumberParts(explicitTarget || element.textContent);

      if (!parts || !Number.isFinite(parts.target)) {
        return;
      }

      element.dataset.astroOriginalText = element.textContent.trim();
      element.dataset.astroParsedTarget = String(parts.target);
      element.dataset.astroParsedDecimals = String(parts.decimals);
      element.dataset.astroParsedPrefix = parts.prefix;
      element.dataset.astroParsedSuffix = parts.suffix;
      element.dataset[readyAttr] = [element.dataset[readyAttr], 'counter'].filter(Boolean).join(' ');

      if (reducedMotion() || !this.counterObserver) {
        setCounterText(element, parts, parts.target);
        return;
      }

      setCounterText(element, parts, 0);
      this.counterObserver.observe(element);
    }, this);
  };

  AstroRuntime.prototype.runCounter = function (element) {
    var parts = {
      target: Number(element.dataset.astroParsedTarget || 0),
      decimals: Number(element.dataset.astroParsedDecimals || 0),
      prefix: element.dataset.astroParsedPrefix || '',
      suffix: element.dataset.astroParsedSuffix || ''
    };
    var duration = Number(element.dataset.astroDuration || element.dataset.duration || 1400);
    var start = performance.now();

    if (!Number.isFinite(parts.target)) {
      return;
    }

    var step = function (now) {
      var progress = Math.min((now - start) / duration, 1);
      var value = parts.target * easeOutCubic(progress);

      setCounterText(element, parts, progress < 1 ? value : parts.target);

      if (progress < 1) {
        window.requestAnimationFrame(step);
      }
    };

    window.requestAnimationFrame(step);
  };

  AstroRuntime.prototype.initHighlight = function (scope) {
    scope.querySelectorAll('[data-astro-highlight]').forEach(function (element) {
      if (element.dataset[readyAttr]?.includes('highlight')) {
        return;
      }

      var source = element.dataset.astroSource || element.textContent || '';
      var language = normalizeLanguage(element.dataset.astroLanguage || element.className, source);

      element.dataset.astroSource = source;
      element.dataset.astroLanguageNormalized = language;
      element.innerHTML = highlightCode(source, language);
      element.dataset[readyAttr] = [element.dataset[readyAttr], 'highlight'].filter(Boolean).join(' ');
    });
  };

  AstroRuntime.prototype.initCopy = function (scope) {
    scope.querySelectorAll('[data-astro-copy]').forEach(function (button) {
      if (button.dataset[readyAttr]?.includes('copy')) {
        return;
      }

      button.dataset[readyAttr] = [button.dataset[readyAttr], 'copy'].filter(Boolean).join(' ');
      button.addEventListener('click', function () {
        var text = copyTextFor(button);

        if (!text.trim() || !navigator.clipboard?.writeText) {
          return;
        }

        navigator.clipboard.writeText(text).then(function () {
          button.dataset.astroCopied = 'true';
          window.setTimeout(function () {
            delete button.dataset.astroCopied;
          }, 1800);
        });
      });
    });
  };

  AstroRuntime.prototype.initTilt = function (scope) {
    if (reducedMotion() || (window.matchMedia && window.matchMedia(coarsePointerQuery).matches)) {
      return;
    }

    scope.querySelectorAll('[data-astro-tilt]').forEach(function (element) {
      if (element.dataset[readyAttr]?.includes('tilt')) {
        return;
      }

      element.dataset[readyAttr] = [element.dataset[readyAttr], 'tilt'].filter(Boolean).join(' ');
      element.addEventListener('pointermove', function (event) {
        var rect = element.getBoundingClientRect();
        var x = ((event.clientX - rect.left) / rect.width - 0.5) * 2;
        var y = ((event.clientY - rect.top) / rect.height - 0.5) * -2;
        var limit = Number(element.dataset.astroTilt || 3);

        element.style.setProperty('--astro-tilt-x', (y * limit).toFixed(2) + 'deg');
        element.style.setProperty('--astro-tilt-y', (x * limit).toFixed(2) + 'deg');
      });
      element.addEventListener('pointerleave', function () {
        element.style.removeProperty('--astro-tilt-x');
        element.style.removeProperty('--astro-tilt-y');
      });
    });
  };

  AstroRuntime.prototype.initMarquee = function (scope) {
    scope.querySelectorAll('[data-astro-marquee]').forEach(function (element) {
      if (element.dataset[readyAttr]?.includes('marquee')) {
        return;
      }

      var track = element.querySelector('[data-astro-marquee-track]') || element.firstElementChild;

      if (!track) {
        return;
      }

      element.dataset[readyAttr] = [element.dataset[readyAttr], 'marquee'].filter(Boolean).join(' ');

      if (reducedMotion()) {
        element.dataset.astroPaused = 'true';
        return;
      }

      var clone = track.cloneNode(true);
      clone.setAttribute('aria-hidden', 'true');
      element.appendChild(clone);
    });
  };

  AstroRuntime.prototype.initCarousel = function (scope) {
    scope.querySelectorAll('[data-astro-carousel]').forEach(function (root) {
      if (root.dataset[readyAttr]?.includes('carousel')) {
        return;
      }

      var slides = Array.prototype.slice.call(
        root.querySelectorAll('[data-astro-carousel-slide]')
      );

      if (slides.length < 2) {
        return;
      }

      root.dataset[readyAttr] = [root.dataset[readyAttr], 'carousel'].filter(Boolean).join(' ');
      root.dataset.astroCarouselReady = 'true';

      var live = root.querySelector('[data-astro-carousel-status]');
      var index = 0;

      var labels = root.dataset.astroCarouselLabels
        ? root.dataset.astroCarouselLabels.split('|')
        : { prev: 'Previous slide', next: 'Next slide', dot: 'Go to slide' };
      if (Array.isArray(labels)) {
        labels = { prev: labels[0] || 'Previous slide', next: labels[1] || 'Next slide', dot: labels[2] || 'Go to slide' };
      }

      function makeButton(cls, text) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = cls;
        b.setAttribute('aria-label', text);
        return b;
      }

      var controls = document.createElement('div');
      controls.className = 'hero-carousel__controls';

      var prev = makeButton('hero-carousel__nav hero-carousel__nav--prev', labels.prev);
      var next = makeButton('hero-carousel__nav hero-carousel__nav--next', labels.next);
      prev.innerHTML = '<span aria-hidden="true">‹</span>';
      next.innerHTML = '<span aria-hidden="true">›</span>';

      var dots = document.createElement('div');
      dots.className = 'hero-carousel__dots';
      dots.setAttribute('role', 'tablist');

      var dotButtons = slides.map(function (slide, i) {
        var dot = makeButton('hero-carousel__dot', labels.dot + ' ' + (i + 1));
        dot.setAttribute('role', 'tab');
        dot.addEventListener('click', function () {
          show(i);
        });
        dots.appendChild(dot);
        return dot;
      });

      function show(i) {
        index = (i + slides.length) % slides.length;
        slides.forEach(function (slide, n) {
          var active = n === index;
          slide.classList.toggle('hero-carousel__slide--active', active);
          slide.setAttribute('aria-hidden', active ? 'false' : 'true');
          slide.toggleAttribute('inert', !active);
        });
        dotButtons.forEach(function (dot, n) {
          var active = n === index;
          dot.setAttribute('aria-selected', active ? 'true' : 'false');
          dot.tabIndex = active ? 0 : -1;
        });
        if (live) {
          live.textContent = 'Slide ' + (index + 1) + ' of ' + slides.length;
        }
      }

      prev.addEventListener('click', function () {
        show(index - 1);
      });
      next.addEventListener('click', function () {
        show(index + 1);
      });

      root.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
          show(index - 1);
        } else if (event.key === 'ArrowRight') {
          show(index + 1);
        }
      });

      controls.appendChild(prev);
      controls.appendChild(dots);
      controls.appendChild(next);
      root.appendChild(controls);

      show(0);
    });
  };

  AstroRuntime.prototype.initContentCarousel = function (scope) {
    scope.querySelectorAll('[data-astro-content-carousel]').forEach(function (root) {
      if (root.dataset[readyAttr]?.includes('contentCarousel')) {
        return;
      }

      var track = root.querySelector('[data-astro-content-carousel-track]');
      var slides = track
        ? Array.prototype.slice.call(track.querySelectorAll('.content-carousel__slide'))
        : [];

      if (!track || slides.length < 2) {
        return;
      }

      root.dataset[readyAttr] = [root.dataset[readyAttr], 'contentCarousel'].filter(Boolean).join(' ');

      var prev = root.querySelector('[data-astro-content-carousel-prev]');
      var next = root.querySelector('[data-astro-content-carousel-next]');
      var dots = Array.prototype.slice.call(root.querySelectorAll('[data-astro-content-carousel-dot]'));
      var thumbs = Array.prototype.slice.call(root.querySelectorAll('[data-astro-content-carousel-thumb]'));
      var status = root.querySelector('[data-astro-content-carousel-status]');
      var total = slides.length;
      var index = 0;

      function scrollToIndex(i) {
        var target = slides[Math.max(0, Math.min(total - 1, i))];
        if (!target) {
          return;
        }
        var behavior = reducedMotion() ? 'auto' : 'smooth';
        track.scrollTo({ left: target.offsetLeft - track.offsetLeft, behavior: behavior });
      }

      function syncActive(active) {
        index = active;
        slides.forEach(function (slide, n) {
          slide.classList.toggle('content-carousel__slide--active', n === active);
        });
        [dots, thumbs].forEach(function (group) {
          group.forEach(function (control, n) {
            var on = n === active;
            control.classList.toggle('content-carousel__dot--active', on && control.classList.contains('content-carousel__dot'));
            control.classList.toggle('content-carousel__thumb--active', on && control.classList.contains('content-carousel__thumb'));
            control.setAttribute('aria-current', on ? 'true' : 'false');
          });
        });
        if (status) {
          status.textContent = 'Slide ' + (active + 1) + ' of ' + total;
        }
      }

      function closestSlide() {
        var center = track.scrollLeft + track.clientWidth / 2;
        var best = 0;
        var bestDistance = Infinity;
        slides.forEach(function (slide, n) {
          var slideCenter = slide.offsetLeft - track.offsetLeft + slide.offsetWidth / 2;
          var distance = Math.abs(slideCenter - center);
          if (distance < bestDistance) {
            bestDistance = distance;
            best = n;
          }
        });
        return best;
      }

      if (prev) {
        prev.addEventListener('click', function () {
          scrollToIndex(index - 1);
        });
      }
      if (next) {
        next.addEventListener('click', function () {
          scrollToIndex(index + 1);
        });
      }

      function bindControl(control, i) {
        control.addEventListener('click', function () {
          scrollToIndex(i);
        });
      }
      dots.forEach(bindControl);
      thumbs.forEach(bindControl);

      var scrollFrame = null;
      track.addEventListener('scroll', function () {
        if (scrollFrame) {
          return;
        }
        scrollFrame = window.requestAnimationFrame(function () {
          scrollFrame = null;
          var current = closestSlide();
          if (current !== index) {
            syncActive(current);
          }
        });
      });

      var autoplay = root.dataset.astroContentCarouselAutoplay === 'true' && !reducedMotion();
      if (autoplay) {
        var interval = parseInt(root.dataset.astroContentCarouselInterval, 10);
        if (isNaN(interval) || interval < 1000) {
          interval = 5000;
        }
        var timer = null;
        var advance = function () {
          scrollToIndex(index + 1 >= total ? 0 : index + 1);
        };
        var play = function () {
          if (!timer) {
            timer = window.setInterval(advance, interval);
          }
        };
        var pause = function () {
          if (timer) {
            window.clearInterval(timer);
            timer = null;
          }
        };
        root.addEventListener('mouseenter', pause);
        root.addEventListener('mouseleave', play);
        root.addEventListener('focusin', pause);
        root.addEventListener('focusout', play);
        document.addEventListener('visibilitychange', function () {
          if (document.hidden) {
            pause();
          } else {
            play();
          }
        });
        play();
      }

      syncActive(0);
    });
  };

  AstroRuntime.prototype.initCountdown = function (scope) {
    scope.querySelectorAll('[data-astro-countdown]').forEach(function (root) {
      if (root.dataset[readyAttr]?.includes('countdown')) {
        return;
      }

      var target = Date.parse(root.dataset.astroCountdown || '');

      if (isNaN(target)) {
        return;
      }

      root.dataset[readyAttr] = [root.dataset[readyAttr], 'countdown'].filter(Boolean).join(' ');

      var units = {
        days: root.querySelector('[data-astro-countdown-days]'),
        hours: root.querySelector('[data-astro-countdown-hours]'),
        minutes: root.querySelector('[data-astro-countdown-minutes]'),
        seconds: root.querySelector('[data-astro-countdown-seconds]')
      };

      function pad(n) {
        return String(n).padStart(2, '0');
      }

      function tick() {
        var diff = Math.max(0, target - Date.now());
        var totalSeconds = Math.floor(diff / 1000);
        var days = Math.floor(totalSeconds / 86400);
        var hours = Math.floor((totalSeconds % 86400) / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;

        if (units.days) {
          units.days.textContent = pad(days);
        }
        if (units.hours) {
          units.hours.textContent = pad(hours);
        }
        if (units.minutes) {
          units.minutes.textContent = pad(minutes);
        }
        if (units.seconds) {
          units.seconds.textContent = pad(seconds);
        }

        if (diff <= 0 && root.dataset.astroCountdownTimer) {
          window.clearInterval(Number(root.dataset.astroCountdownTimer));
          root.dataset.astroExpired = 'true';
        }
      }

      tick();
      root.dataset.astroCountdownTimer = String(window.setInterval(tick, 1000));
    });
  };

  window.DesiderioAstro = window.DesiderioAstro || new AstroRuntime();

  function init() {
    window.DesiderioAstro.init(document);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }

  document.addEventListener('desiderio:astro:init', function (event) {
    window.DesiderioAstro.init(event.detail?.scope || document);
  });
})();
