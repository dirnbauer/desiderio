/**
 * Desiderio — TYPO3 v14 Theme JavaScript
 *
 * Dark mode toggle and mobile menu are handled by s2f.js (from shadcn2fluid-templates).
 * This file adds desiderio-specific enhancements.
 */
'use strict';

function desiderioInit() {
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

  /* ------------------------------------------------------------------ */
  /*  Styleguide — Fixture preview on card click                        */
  /* ------------------------------------------------------------------ */
  const fixturesEl = document.getElementById('styleguide-fixtures');
  if (!fixturesEl) return;

  let fixtures;
  try {
    fixtures = JSON.parse(fixturesEl.textContent);
  } catch (_) {
    return;
  }

  /**
   * Close any open preview inside a container and deactivate cards.
   * @param {Element} container
   */
  function closePreviewsIn(container) {
    container.querySelectorAll('.desiderio-styleguide__element-card--active').forEach(c => {
      c.classList.remove('desiderio-styleguide__element-card--active');
      c.setAttribute('aria-expanded', 'false');
    });
    container.querySelectorAll('.desiderio-styleguide__element-preview').forEach(p => p.remove());
  }

  /**
   * Open preview for a single card.
   * @param {Element} card
   */
  function openPreview(card) {
    const ctype = card.dataset.ctype;
    const name = card.querySelector('.desiderio-styleguide__element-name')?.textContent || ctype;
    const data = fixtures[ctype];
    if (!data) return;

    card.classList.add('desiderio-styleguide__element-card--active');
    card.setAttribute('aria-expanded', 'true');

    const preview = document.createElement('div');
    preview.className = 'desiderio-styleguide__element-preview';
    preview.innerHTML =
      '<div class="desiderio-styleguide__preview-chrome">' +
        '<span>' +
          '<span class="desiderio-styleguide__preview-label">' + esc(name) + '</span>' +
          '<span class="desiderio-styleguide__preview-ctype">' + esc(ctype) + '</span>' +
        '</span>' +
        '<button type="button" class="desiderio-styleguide__preview-close" aria-label="Close preview">&times;</button>' +
      '</div>' +
      '<div class="desiderio-styleguide__preview-body">' +
        renderPreview(data) +
      '</div>';

    preview.querySelector('.desiderio-styleguide__preview-close').addEventListener('click', (e) => {
      e.stopPropagation();
      card.classList.remove('desiderio-styleguide__element-card--active');
      card.setAttribute('aria-expanded', 'false');
      preview.remove();
    });

    card.after(preview);
  }

  /* — Card click ---------------------------------------------------- */
  document.querySelectorAll('.desiderio-styleguide__element-card[data-ctype]').forEach(card => {
    card.addEventListener('click', () => {
      const list = card.closest('.desiderio-styleguide__element-list');
      const wasActive = card.classList.contains('desiderio-styleguide__element-card--active');

      closePreviewsIn(list);
      if (!wasActive) openPreview(card);
    });

    card.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        card.click();
      }
    });
  });

  /* — Show All / Collapse All per category -------------------------- */
  document.querySelectorAll('.desiderio-styleguide__show-all').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('.desiderio-styleguide__group');
      if (!group) return;

      const list = group.querySelector('.desiderio-styleguide__element-list');
      const hasOpen = list.querySelector('.desiderio-styleguide__element-preview');

      if (hasOpen) {
        closePreviewsIn(list);
        btn.textContent = 'Show All';
        return;
      }

      btn.textContent = 'Collapse All';
      list.querySelectorAll('.desiderio-styleguide__element-card[data-ctype]').forEach(card => {
        openPreview(card);
      });
    });
  });

  /* ================================================================== */
  /*  RENDER FUNCTIONS                                                   */
  /* ================================================================== */

  function renderPreview(d) {
    const fn = renderers[d._type] || renderGeneric;
    return fn(d);
  }

  const renderers = {
    hero: renderHero,
    grid: renderGrid,
    list: renderList,
    card: renderCard,
    form: renderForm,
    pricing: renderPricing,
    testimonial: renderTestimonial,
    stats: renderStats,
    nav: renderNav,
    footer: renderFooter,
    team: renderTeam,
    table: renderTable,
    section: renderGeneric
  };

  /* — Hero ---------------------------------------------------------- */
  function renderHero(d) {
    return '<div class="sg-hero">' +
      (d.badge ? '<span class="sg-hero__badge">' + esc(d.badge) + '</span>' : '') +
      '<h2 class="sg-hero__title">' + esc(d.header) + '</h2>' +
      (d.description ? '<p class="sg-hero__desc">' + esc(d.description) + '</p>' : '') +
      '<div class="sg-hero__actions">' +
        (d.primaryButton ? '<span class="sg-btn sg-btn--primary">' + esc(d.primaryButton) + '</span>' : '') +
        (d.secondaryButton ? '<span class="sg-btn sg-btn--outline">' + esc(d.secondaryButton) + '</span>' : '') +
      '</div>' +
    '</div>';
  }

  /* — Grid ---------------------------------------------------------- */
  function renderGrid(d) {
    var items = d.items || [];
    return '<div class="sg-grid">' +
      '<div class="sg-grid__header">' +
        '<h3 class="sg-grid__title">' + esc(d.header) + '</h3>' +
        (d.description ? '<p class="sg-grid__desc">' + esc(d.description) + '</p>' : '') +
      '</div>' +
      '<div class="sg-grid__items">' +
        items.map(function(item) {
          return '<div class="sg-grid__item">' +
            (item.icon ? '<div class="sg-grid__item-icon">' + item.icon + '</div>' : '') +
            '<h4 class="sg-grid__item-title">' + esc(item.title) + '</h4>' +
            (item.description ? '<p class="sg-grid__item-desc">' + esc(item.description) + '</p>' : '') +
          '</div>';
        }).join('') +
      '</div>' +
    '</div>';
  }

  /* — List ---------------------------------------------------------- */
  function renderList(d) {
    var items = d.items || [];
    return '<div class="sg-list">' +
      '<div class="sg-list__header">' +
        '<h3 class="sg-list__title">' + esc(d.header) + '</h3>' +
        (d.description ? '<p class="sg-list__desc">' + esc(d.description) + '</p>' : '') +
      '</div>' +
      '<div class="sg-list__items">' +
        items.map(function(item) {
          return '<div class="sg-list__item">' +
            '<h4 class="sg-list__item-title">' + esc(item.title) + '</h4>' +
            (item.description ? '<p class="sg-list__item-desc">' + esc(item.description) + '</p>' : '') +
          '</div>';
        }).join('') +
      '</div>' +
    '</div>';
  }

  /* — Card ---------------------------------------------------------- */
  function renderCard(d) {
    return '<div class="sg-card">' +
      '<div class="sg-card__inner">' +
        '<div class="sg-card__image">' + (d.icon || '') + '</div>' +
        '<div class="sg-card__body">' +
          '<h3 class="sg-card__title">' + esc(d.header) + '</h3>' +
          (d.description ? '<p class="sg-card__desc">' + esc(d.description) + '</p>' : '') +
          (d.primaryButton ? '<span class="sg-btn sg-btn--primary" style="width:100%;text-align:center;">' + esc(d.primaryButton) + '</span>' : '') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Form ---------------------------------------------------------- */
  function renderForm(d) {
    var fields = d.fields || [{ label: 'Email', type: 'email', placeholder: 'you@example.com' }];
    return '<div class="sg-form">' +
      '<div class="sg-form__inner">' +
        '<h3 class="sg-form__title">' + esc(d.header) + '</h3>' +
        (d.description ? '<p class="sg-form__desc">' + esc(d.description) + '</p>' : '') +
        fields.map(function(f) {
          return '<div class="sg-form__field">' +
            '<label class="sg-form__label">' + esc(f.label) + '</label>' +
            '<input class="sg-form__input" type="' + (f.type || 'text') + '" placeholder="' + esc(f.placeholder || '') + '" disabled />' +
          '</div>';
        }).join('') +
        '<span class="sg-btn sg-btn--primary" style="display:block;width:100%;text-align:center;margin-top:0.5rem;">' +
          esc(d.primaryButton || 'Submit') +
        '</span>' +
      '</div>' +
    '</div>';
  }

  /* — Pricing ------------------------------------------------------- */
  function renderPricing(d) {
    var plans = d.plans || [];
    return '<div class="sg-pricing">' +
      '<div class="sg-pricing__header">' +
        '<h3 class="sg-pricing__title">' + esc(d.header) + '</h3>' +
        (d.description ? '<p class="sg-pricing__desc">' + esc(d.description) + '</p>' : '') +
      '</div>' +
      '<div class="sg-pricing__plans">' +
        plans.map(function(plan) {
          return '<div class="sg-pricing__plan' + (plan.featured ? ' sg-pricing__plan--featured' : '') + '">' +
            '<p class="sg-pricing__plan-name">' + esc(plan.name) + '</p>' +
            '<p class="sg-pricing__plan-price">' + esc(plan.price) + '</p>' +
            '<p class="sg-pricing__plan-period">' + esc(plan.period || 'per month') + '</p>' +
            '<ul class="sg-pricing__plan-features">' +
              (plan.features || []).map(function(f) {
                return '<li class="sg-pricing__plan-feature">' + esc(f) + '</li>';
              }).join('') +
            '</ul>' +
            '<span class="sg-btn ' + (plan.featured ? 'sg-btn--primary' : 'sg-btn--outline') + '" style="display:block;width:100%;text-align:center;">' +
              esc(plan.button || 'Get Started') +
            '</span>' +
          '</div>';
        }).join('') +
      '</div>' +
    '</div>';
  }

  /* — Testimonial --------------------------------------------------- */
  function renderTestimonial(d) {
    var stars = '';
    if (d.rating) {
      for (var i = 0; i < 5; i++) stars += (i < d.rating ? '\u2605' : '\u2606');
    }
    var initial = (d.author || 'A').charAt(0).toUpperCase();
    return '<div class="sg-testimonial">' +
      '<div class="sg-testimonial__inner">' +
        '<div class="sg-testimonial__quote-mark">\u201C</div>' +
        (stars ? '<div class="sg-testimonial__stars">' + stars + '</div>' : '') +
        '<p class="sg-testimonial__text">' + esc(d.quote || d.description) + '</p>' +
        '<div class="sg-testimonial__author">' +
          '<div class="sg-testimonial__avatar">' + initial + '</div>' +
          '<div class="sg-testimonial__info">' +
            '<p class="sg-testimonial__name">' + esc(d.author || '') + '</p>' +
            '<p class="sg-testimonial__role">' + esc(d.role || '') + (d.company ? ' at ' + esc(d.company) : '') + '</p>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Stats --------------------------------------------------------- */
  function renderStats(d) {
    var items = d.stats || [];
    return '<div class="sg-stats">' +
      (d.header ? '<div class="sg-stats__header"><h3 class="sg-stats__title">' + esc(d.header) + '</h3></div>' : '') +
      '<div class="sg-stats__items">' +
        items.map(function(s) {
          return '<div>' +
            '<div class="sg-stats__value">' + esc(s.value) + '</div>' +
            '<div class="sg-stats__label">' + esc(s.label) + '</div>' +
          '</div>';
        }).join('') +
      '</div>' +
    '</div>';
  }

  /* — Nav ----------------------------------------------------------- */
  function renderNav(d) {
    var links = d.links || [];
    return '<div class="sg-nav">' +
      '<span class="sg-nav__logo">' + esc(d.logo || 'Brand') + '</span>' +
      '<ul class="sg-nav__links">' +
        links.map(function(l, i) {
          return '<li><span class="sg-nav__link' + (i === 0 ? ' sg-nav__link--active' : '') + '">' + esc(l) + '</span></li>';
        }).join('') +
      '</ul>' +
      (d.primaryButton ? '<span class="sg-btn sg-btn--primary">' + esc(d.primaryButton) + '</span>' : '') +
    '</div>';
  }

  /* — Footer -------------------------------------------------------- */
  function renderFooter(d) {
    var columns = d.columns || [];
    return '<div class="sg-footer">' +
      '<div class="sg-footer__cols">' +
        columns.map(function(col) {
          return '<div>' +
            '<h4 class="sg-footer__col-title">' + esc(col.title) + '</h4>' +
            '<ul class="sg-footer__links">' +
              (col.links || []).map(function(l) {
                return '<li><span class="sg-footer__link">' + esc(l) + '</span></li>';
              }).join('') +
            '</ul>' +
          '</div>';
        }).join('') +
      '</div>' +
      '<div class="sg-footer__bottom">' + esc(d.copyright || '\u00A9 2025 Company. All rights reserved.') + '</div>' +
    '</div>';
  }

  /* — Team ---------------------------------------------------------- */
  function renderTeam(d) {
    var members = d.members || [];
    return '<div class="sg-team">' +
      (d.header ? '<div class="sg-team__header"><h3 class="sg-team__title">' + esc(d.header) + '</h3>' +
        (d.description ? '<p class="sg-team__desc">' + esc(d.description) + '</p>' : '') +
      '</div>' : '') +
      '<div class="sg-team__members">' +
        members.map(function(m) {
          return '<div class="sg-team__member">' +
            '<div class="sg-team__member-avatar">' + (m.name || 'A').charAt(0) + '</div>' +
            '<p class="sg-team__member-name">' + esc(m.name) + '</p>' +
            '<p class="sg-team__member-role">' + esc(m.role || '') + '</p>' +
          '</div>';
        }).join('') +
      '</div>' +
    '</div>';
  }

  /* — Table --------------------------------------------------------- */
  function renderTable(d) {
    var rows = d.rows || [];
    var headers = d.headers || (rows.length > 0 ? Object.keys(rows[0]) : []);
    return '<div class="sg-table">' +
      (d.header ? '<h3 class="sg-table__title">' + esc(d.header) + '</h3>' : '') +
      '<table>' +
        '<thead><tr>' + headers.map(function(h) { return '<th>' + esc(h) + '</th>'; }).join('') + '</tr></thead>' +
        '<tbody>' +
          rows.map(function(row) {
            return '<tr>' + headers.map(function(h) { return '<td>' + esc(row[h] || '') + '</td>'; }).join('') + '</tr>';
          }).join('') +
        '</tbody>' +
      '</table>' +
    '</div>';
  }

  /* — Generic / Section --------------------------------------------- */
  function renderGeneric(d) {
    return '<div class="sg-section">' +
      '<h3 class="sg-section__title">' + esc(d.header) + '</h3>' +
      (d.description ? '<p class="sg-section__desc">' + esc(d.description) + '</p>' : '') +
      (d.content ? '<div class="sg-section__content">' + esc(d.content) + '</div>' : '') +
    '</div>';
  }

  /* — Escape HTML --------------------------------------------------- */
  function esc(s) {
    if (!s) return '';
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(String(s)));
    return d.innerHTML;
  }
}

/* Handle async script loading — DOMContentLoaded may have already fired */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', desiderioInit);
} else {
  desiderioInit();
}
