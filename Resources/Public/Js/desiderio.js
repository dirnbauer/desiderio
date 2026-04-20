/**
 * desiderio.js - Shared vanilla JS utilities for desiderio TYPO3 components.
 * Framework-free, auto-initialised on DOMContentLoaded.
 */
document.addEventListener('DOMContentLoaded', () => {
  /* ------------------------------------------------------------------ */
  /*  1. Accordion                                                       */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-accordion]').forEach(root => {
    const type = root.dataset.type || 'single';

    root.addEventListener('click', e => {
      const trigger = e.target.closest('[data-d-accordion-trigger]');
      if (!trigger) return;

      const item = trigger.closest('[data-d-accordion-item]');
      const content = item?.querySelector('[data-d-accordion-content]');
      if (!item || !content) return;

      const isOpen = trigger.getAttribute('aria-expanded') === 'true';

      // Single mode: close every other item first.
      if (type === 'single' && !isOpen) {
        root.querySelectorAll('[data-d-accordion-item]').forEach(other => {
          if (other === item) return;
          other.querySelector('[data-d-accordion-trigger]')?.setAttribute('aria-expanded', 'false');
          other.querySelector('[data-d-accordion-trigger]')?.classList.remove('accordion__trigger--open');
          other.querySelector('[data-d-accordion-content]')?.classList.add('accordion__content--hidden');
        });
      }

      // Toggle the clicked item.
      trigger.setAttribute('aria-expanded', String(!isOpen));
      trigger.classList.toggle('accordion__trigger--open', !isOpen);
      content.classList.toggle('accordion__content--hidden', isOpen);
    });
  });

  /* ------------------------------------------------------------------ */
  /*  2. Tabs                                                            */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-tabs]').forEach(root => {
    const activate = value => {
      root.querySelectorAll('[data-d-tabs-trigger]').forEach(t => {
        const active = t.dataset.value === value;
        t.classList.toggle('tabs__trigger--active', active);
        t.setAttribute('aria-selected', String(active));
      });
      root.querySelectorAll('[data-d-tabs-content]').forEach(c => {
        c.classList.toggle('tabs__content--hidden', c.dataset.value !== value);
      });
    };

    // Set initial tab.
    const defaultVal = root.dataset.default
      || root.querySelector('[data-d-tabs-trigger]')?.dataset.value;
    if (defaultVal) activate(defaultVal);

    root.addEventListener('click', e => {
      const trigger = e.target.closest('[data-d-tabs-trigger]');
      if (trigger) activate(trigger.dataset.value);
    });
  });

  /* ------------------------------------------------------------------ */
  /*  3. Dark-mode toggle                                                */
  /* ------------------------------------------------------------------ */
  const html = document.documentElement;
  if (localStorage.getItem('d-theme') === 'dark') html.classList.add('dark');

  document.querySelectorAll('[data-d-theme-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
      html.classList.toggle('dark');
      localStorage.setItem('d-theme', html.classList.contains('dark') ? 'dark' : 'light');
    });
  });

  /* ------------------------------------------------------------------ */
  /*  4. Scroll animations (IntersectionObserver)                        */
  /* ------------------------------------------------------------------ */
  if ('IntersectionObserver' in window) {
    const animObs = new IntersectionObserver(
      entries => entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('is-visible');
          animObs.unobserve(e.target);
        }
      }),
      { threshold: 0.1 }
    );
    document.querySelectorAll('[data-d-animate]').forEach(el => animObs.observe(el));
  }

  /* ------------------------------------------------------------------ */
  /*  5. Counter                                                         */
  /* ------------------------------------------------------------------ */
  if ('IntersectionObserver' in window) {
    const counterObs = new IntersectionObserver(
      entries => entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        counterObs.unobserve(el);

        const target = parseFloat(el.dataset.target) || 0;
        const duration = parseInt(el.dataset.duration, 10) || 2000;
        const isFloat = String(el.dataset.target).includes('.');
        const start = performance.now();

        const step = now => {
          const progress = Math.min((now - start) / duration, 1);
          el.textContent = isFloat
            ? (target * progress).toFixed(1)
            : Math.floor(target * progress);
          if (progress < 1) requestAnimationFrame(step);
          else el.textContent = el.dataset.target;
        };
        requestAnimationFrame(step);
      }),
      { threshold: 0.3 }
    );
    document.querySelectorAll('[data-d-counter]').forEach(el => counterObs.observe(el));
  }

  /* ------------------------------------------------------------------ */
  /*  6. Click-outside                                                   */
  /* ------------------------------------------------------------------ */
  document.addEventListener('click', e => {
    document.querySelectorAll('[data-d-click-outside]:not(.is-hidden)').forEach(el => {
      if (!el.contains(e.target)) el.classList.add('is-hidden');
    });
  });

  /* ------------------------------------------------------------------ */
  /*  7. Mobile menu toggle                                              */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-menu-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetSel = btn.dataset.dMenuTarget;
      const target = targetSel ? document.querySelector(targetSel) : null;
      if (!target) return;

      const expanded = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', String(!expanded));
      target.classList.toggle('is-hidden', !expanded);
    });
  });

  /* ------------------------------------------------------------------ */
  /*  8. Back to top                                                     */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-back-to-top]').forEach(btn => {
    const threshold = parseInt(btn.dataset.threshold, 10) || 300;

    const toggle = () => btn.classList.toggle('is-hidden', window.scrollY < threshold);
    toggle();
    window.addEventListener('scroll', toggle, { passive: true });

    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  });
});
