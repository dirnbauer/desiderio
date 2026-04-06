/**
 * Desiderio — TYPO3 v14 Theme JavaScript
 *
 * Styleguide: Interactive content element preview using real shadcn2fluid BEM classes.
 * Dark mode toggle and mobile menu are handled by s2f.js (from shadcn2fluid-templates).
 */
'use strict';

function desiderioInit() {

  /* ================================================================== */
  /*  Smooth scroll for styleguide nav links                            */
  /* ================================================================== */
  document.querySelectorAll('.desiderio-sg__nav-link').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var href = link.getAttribute('href');
      if (href && href.startsWith('#')) {
        e.preventDefault();
        var target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          history.replaceState(null, '', href);
        }
      }
    });
  });

  /* ================================================================== */
  /*  Active nav tracking via IntersectionObserver                      */
  /* ================================================================== */
  var navLinks = document.querySelectorAll('.desiderio-sg__nav-link');
  if (navLinks.length > 0 && 'IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          var id = entry.target.id;
          navLinks.forEach(function (link) {
            link.classList.toggle('desiderio-sg__nav-link--active', link.getAttribute('href') === '#' + id);
          });
        }
      });
    }, { rootMargin: '-20% 0px -60% 0px' });

    document.querySelectorAll('.desiderio-sg__group[id]').forEach(function (group) {
      observer.observe(group);
    });
  }

  /* ================================================================== */
  /*  Search / filter                                                   */
  /* ================================================================== */
  var searchInput = document.getElementById('sg-search');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.toLowerCase().trim();
      document.querySelectorAll('.desiderio-sg__element-card').forEach(function (card) {
        var name = (card.dataset.ctype || '') + ' ' + (card.querySelector('.desiderio-sg__card-name')?.textContent || '');
        card.style.display = name.toLowerCase().includes(q) || q === '' ? '' : 'none';
      });
      document.querySelectorAll('.desiderio-sg__group').forEach(function (group) {
        var visible = group.querySelectorAll('.desiderio-sg__element-card:not([style*="display: none"])');
        group.style.display = visible.length > 0 || q === '' ? '' : 'none';
      });
    });
  }

  /* ================================================================== */
  /*  Fixture preview — click to expand                                 */
  /* ================================================================== */
  var fixturesEl = document.getElementById('styleguide-fixtures');
  if (!fixturesEl) return;

  var fixtures;
  try { fixtures = JSON.parse(fixturesEl.textContent); } catch (_) { return; }

  /** Close open previews inside a container */
  function closePreviewsIn(container) {
    container.querySelectorAll('.desiderio-sg__element-card--active').forEach(function (c) {
      c.classList.remove('desiderio-sg__element-card--active');
      c.setAttribute('aria-expanded', 'false');
    });
    container.querySelectorAll('.desiderio-sg__preview').forEach(function (p) { p.remove(); });
  }

  /** Open preview for a card */
  function openPreview(card) {
    var ctype = card.dataset.ctype;
    var name = card.querySelector('.desiderio-sg__card-name')?.textContent || ctype;
    var data = fixtures[ctype];
    if (!data) return;

    card.classList.add('desiderio-sg__element-card--active');
    card.setAttribute('aria-expanded', 'true');

    var preview = document.createElement('div');
    preview.className = 'desiderio-sg__preview';
    preview.innerHTML =
      '<div class="desiderio-sg__preview-chrome">' +
        '<div class="stack stack--horizontal stack--align-center stack--gap-sm">' +
          '<span class="typography typography--small" style="font-weight:600;">' + esc(name) + '</span>' +
          '<span class="badge badge--outline" style="font-size:0.6875rem;font-family:var(--font-mono,monospace);">' + esc(ctype) + '</span>' +
        '</div>' +
        '<button type="button" class="btn btn--ghost btn--icon btn--sm desiderio-sg__preview-close" aria-label="Close">' +
          SVG.x +
        '</button>' +
      '</div>' +
      '<div class="desiderio-sg__preview-body">' +
        renderPreview(data) +
      '</div>';

    preview.querySelector('.desiderio-sg__preview-close').addEventListener('click', function (e) {
      e.stopPropagation();
      card.classList.remove('desiderio-sg__element-card--active');
      card.setAttribute('aria-expanded', 'false');
      preview.remove();
    });

    card.after(preview);
  }

  /* — Card click — */
  document.querySelectorAll('.desiderio-sg__element-card[data-ctype]').forEach(function (card) {
    card.addEventListener('click', function () {
      var list = card.closest('.desiderio-sg__element-list');
      var wasActive = card.classList.contains('desiderio-sg__element-card--active');
      closePreviewsIn(list);
      if (!wasActive) openPreview(card);
    });
    card.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
    });
  });

  /* — Show All / Collapse All — */
  document.querySelectorAll('.desiderio-sg__show-all').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var group = btn.closest('.desiderio-sg__group');
      if (!group) return;
      var list = group.querySelector('.desiderio-sg__element-list');
      var hasOpen = list.querySelector('.desiderio-sg__preview');
      if (hasOpen) {
        closePreviewsIn(list);
        btn.textContent = 'Show All';
      } else {
        btn.textContent = 'Collapse All';
        list.querySelectorAll('.desiderio-sg__element-card[data-ctype]').forEach(openPreview);
      }
    });
  });

  /* ================================================================== */
  /*  SVG Icons (Lucide subset — shadcn/ui default icon library)        */
  /* ================================================================== */
  var SVG = {
    x: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
    check: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
    star: '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    starEmpty: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    arrowRight: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>',
    search: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>',
    mail: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
    user: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    globe: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>',
    mapPin: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>'
  };

  /* ================================================================== */
  /*  RENDER — uses real shadcn2fluid BEM classes                       */
  /* ================================================================== */
  function renderPreview(d) {
    var fn = renderers[d._type] || renderSection;
    return fn(d);
  }

  var renderers = {
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
    section: renderSection
  };

  /* — Hero — */
  function renderHero(d) {
    return '<div class="section section--bg-muted section--spacing-lg">' +
      '<div class="container container--sm" style="text-align:center;">' +
        '<div class="stack stack--vertical stack--align-center stack--gap-default">' +
          (d.badge ? '<span class="badge badge--outline">' + esc(d.badge) + '</span>' : '') +
          '<h1 class="typography typography--h1">' + esc(d.header) + '</h1>' +
          (d.description ? '<p class="typography typography--lead">' + esc(d.description) + '</p>' : '') +
          '<div class="stack stack--horizontal stack--gap-sm" style="justify-content:center;flex-wrap:wrap;">' +
            (d.primaryButton ? '<a href="#" class="btn btn--default btn--lg">' + esc(d.primaryButton) + ' <span class="icon">' + SVG.arrowRight + '</span></a>' : '') +
            (d.secondaryButton ? '<a href="#" class="btn btn--outline btn--lg">' + esc(d.secondaryButton) + '</a>' : '') +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Grid — */
  function renderGrid(d) {
    var items = d.items || [];
    var cols = items.length <= 3 ? 'grid--cols-3' : 'grid--cols-4';
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--lg">' +
        '<div class="stack stack--vertical stack--gap-lg" style="text-align:center;margin-bottom:2rem;">' +
          '<h2 class="typography typography--h2">' + esc(d.header) + '</h2>' +
          (d.description ? '<p class="typography typography--muted">' + esc(d.description) + '</p>' : '') +
        '</div>' +
        '<div class="grid ' + cols + '">' +
          items.map(function (item) {
            return '<div class="card">' +
              '<div class="card__header">' +
                (item.icon ? '<span style="font-size:1.75rem;line-height:1;">' + item.icon + '</span>' : '') +
                '<h3 class="card__title">' + esc(item.title) + '</h3>' +
                (item.description ? '<p class="card__description">' + esc(item.description) + '</p>' : '') +
              '</div>' +
            '</div>';
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — List — */
  function renderList(d) {
    var items = d.items || [];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--default">' +
        '<div style="margin-bottom:1.5rem;">' +
          '<h2 class="typography typography--h2">' + esc(d.header) + '</h2>' +
          (d.description ? '<p class="typography typography--muted" style="margin-top:0.5rem;">' + esc(d.description) + '</p>' : '') +
        '</div>' +
        '<div class="accordion">' +
          items.map(function (item) {
            return '<div class="accordion__item">' +
              '<div class="accordion__trigger" style="cursor:default;">' +
                '<span>' + esc(item.title) + '</span>' +
              '</div>' +
              (item.description ? '<div class="accordion__body">' + esc(item.description) + '</div>' : '') +
            '</div>';
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Card — */
  function renderCard(d) {
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--sm">' +
        '<div class="card">' +
          '<div class="aspect-ratio aspect-ratio--16-9" style="background:var(--muted);display:flex;align-items:center;justify-content:center;">' +
            '<span style="font-size:3rem;position:static;">' + (d.icon || '') + '</span>' +
          '</div>' +
          '<div class="card__header">' +
            '<h3 class="card__title">' + esc(d.header) + '</h3>' +
            (d.description ? '<p class="card__description">' + esc(d.description) + '</p>' : '') +
          '</div>' +
          (d.primaryButton ? '<div class="card__footer"><a href="#" class="btn btn--default" style="width:100%;justify-content:center;">' + esc(d.primaryButton) + '</a></div>' : '') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Form — */
  function renderForm(d) {
    var fields = d.fields || [{ label: 'Email', type: 'email', placeholder: 'you@example.com' }];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--sm">' +
        '<div class="card">' +
          '<div class="card__header">' +
            '<h3 class="card__title">' + esc(d.header) + '</h3>' +
            (d.description ? '<p class="card__description">' + esc(d.description) + '</p>' : '') +
          '</div>' +
          '<div class="card__content">' +
            '<div class="stack stack--vertical stack--gap-default">' +
              fields.map(function (f) {
                return '<div class="stack stack--vertical stack--gap-sm">' +
                  '<label class="label">' + esc(f.label) + '</label>' +
                  '<input class="input" type="' + (f.type || 'text') + '" placeholder="' + esc(f.placeholder || '') + '" disabled />' +
                '</div>';
              }).join('') +
            '</div>' +
          '</div>' +
          '<div class="card__footer">' +
            '<a href="#" class="btn btn--default" style="width:100%;justify-content:center;">' + esc(d.primaryButton || 'Submit') + '</a>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Pricing — */
  function renderPricing(d) {
    var plans = d.plans || [];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--lg">' +
        '<div style="text-align:center;margin-bottom:2rem;">' +
          '<h2 class="typography typography--h2">' + esc(d.header) + '</h2>' +
          (d.description ? '<p class="typography typography--muted" style="margin-top:0.5rem;">' + esc(d.description) + '</p>' : '') +
        '</div>' +
        '<div class="grid grid--cols-3">' +
          plans.map(function (plan) {
            return '<div class="card' + (plan.featured ? '" style="border-color:var(--primary);box-shadow:0 0 0 1px var(--primary);"' : '"') + '>' +
              '<div class="card__header">' +
                '<p class="typography typography--small" style="font-weight:600;">' + esc(plan.name) + '</p>' +
                '<p class="typography typography--h1" style="margin:0;">' + esc(plan.price) + '</p>' +
                '<p class="typography typography--muted">' + esc(plan.period || 'per month') + '</p>' +
              '</div>' +
              '<div class="card__content">' +
                '<div class="stack stack--vertical stack--gap-sm">' +
                  (plan.features || []).map(function (f) {
                    return '<div class="stack stack--horizontal stack--align-center stack--gap-sm">' +
                      '<span class="icon" style="color:var(--primary);">' + SVG.check + '</span>' +
                      '<span class="typography typography--small">' + esc(f) + '</span>' +
                    '</div>';
                  }).join('') +
                '</div>' +
              '</div>' +
              '<div class="card__footer">' +
                '<a href="#" class="btn ' + (plan.featured ? 'btn--default' : 'btn--outline') + '" style="width:100%;justify-content:center;">' + esc(plan.button || 'Get Started') + '</a>' +
              '</div>' +
            '</div>';
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Testimonial — */
  function renderTestimonial(d) {
    var stars = '';
    if (d.rating) {
      for (var i = 0; i < 5; i++) stars += '<span class="icon">' + (i < d.rating ? SVG.star : SVG.starEmpty) + '</span>';
    }
    var initial = (d.author || 'A').charAt(0).toUpperCase();
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--sm" style="text-align:center;">' +
        '<div class="stack stack--vertical stack--align-center stack--gap-default">' +
          '<p class="typography typography--h1" style="opacity:0.15;line-height:1;margin:0;">\u201C</p>' +
          (stars ? '<div class="stack stack--horizontal stack--gap-sm" style="color:#f59e0b;">' + stars + '</div>' : '') +
          '<p class="typography typography--lead" style="font-style:italic;">' + esc(d.quote || d.description) + '</p>' +
          '<div class="stack stack--horizontal stack--align-center stack--gap-sm">' +
            '<div class="avatar"><span class="avatar__fallback">' + initial + '</span></div>' +
            '<div style="text-align:left;">' +
              '<p class="typography typography--small" style="font-weight:600;">' + esc(d.author || '') + '</p>' +
              '<p class="typography typography--muted">' + esc(d.role || '') + (d.company ? ' at ' + esc(d.company) : '') + '</p>' +
            '</div>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Stats — */
  function renderStats(d) {
    var items = d.stats || [];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--default">' +
        (d.header ? '<h2 class="typography typography--h2" style="text-align:center;margin-bottom:2rem;">' + esc(d.header) + '</h2>' : '') +
        '<div class="grid grid--cols-' + Math.min(items.length, 4) + '" style="text-align:center;">' +
          items.map(function (s) {
            return '<div class="stack stack--vertical stack--align-center">' +
              '<span class="typography typography--h1" style="color:var(--primary);">' + esc(s.value) + '</span>' +
              '<span class="typography typography--muted">' + esc(s.label) + '</span>' +
            '</div>';
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Nav — */
  function renderNav(d) {
    var links = d.links || [];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--lg">' +
        '<div class="card" style="padding:0.75rem 1.5rem;">' +
          '<div class="stack stack--horizontal stack--align-center" style="justify-content:space-between;flex-wrap:wrap;gap:1rem;">' +
            '<span class="typography typography--large">' + esc(d.logo || 'Brand') + '</span>' +
            '<div class="stack stack--horizontal stack--gap-sm">' +
              links.map(function (l, i) {
                return '<a href="#" class="btn ' + (i === 0 ? 'btn--ghost' : 'btn--link') + ' btn--sm" style="' + (i === 0 ? 'background:var(--accent);' : '') + '">' + esc(l) + '</a>';
              }).join('') +
            '</div>' +
            (d.primaryButton ? '<a href="#" class="btn btn--default btn--sm">' + esc(d.primaryButton) + '</a>' : '') +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Footer — */
  function renderFooter(d) {
    var columns = d.columns || [];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--lg">' +
        '<div style="border-top:1px solid var(--border);padding-top:2rem;">' +
          '<div class="grid grid--cols-4">' +
            columns.map(function (col) {
              return '<div class="stack stack--vertical stack--gap-sm">' +
                '<h4 class="typography typography--small" style="font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted-foreground);">' + esc(col.title) + '</h4>' +
                (col.links || []).map(function (l) {
                  return '<a href="#" class="link link--muted" style="font-size:0.875rem;">' + esc(l) + '</a>';
                }).join('') +
              '</div>';
            }).join('') +
          '</div>' +
          '<div class="separator separator--horizontal" style="margin:2rem 0 1.5rem;"></div>' +
          '<p class="typography typography--muted" style="font-size:0.75rem;">' + esc(d.copyright || '\u00A9 2025 Company. All rights reserved.') + '</p>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Team — */
  function renderTeam(d) {
    var members = d.members || [];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--lg">' +
        (d.header ? '<div style="text-align:center;margin-bottom:2rem;">' +
          '<h2 class="typography typography--h2">' + esc(d.header) + '</h2>' +
          (d.description ? '<p class="typography typography--muted" style="margin-top:0.5rem;">' + esc(d.description) + '</p>' : '') +
        '</div>' : '') +
        '<div class="grid grid--cols-4">' +
          members.map(function (m) {
            return '<div class="card" style="text-align:center;">' +
              '<div class="card__header" style="align-items:center;">' +
                '<div class="avatar avatar--lg"><span class="avatar__fallback">' + (m.name || 'A').charAt(0) + '</span></div>' +
                '<h3 class="card__title" style="font-size:0.9375rem;">' + esc(m.name) + '</h3>' +
                '<p class="card__description">' + esc(m.role || '') + '</p>' +
              '</div>' +
            '</div>';
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Table — */
  function renderTable(d) {
    var rows = d.rows || [];
    var headers = d.headers || (rows.length > 0 ? Object.keys(rows[0]) : []);
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--default">' +
        (d.header ? '<h3 class="typography typography--h3" style="margin-bottom:1rem;">' + esc(d.header) + '</h3>' : '') +
        '<div class="table-wrapper">' +
          '<table class="table">' +
            '<thead class="table__header"><tr class="table__row">' +
              headers.map(function (h) { return '<th class="table__head">' + esc(h) + '</th>'; }).join('') +
            '</tr></thead>' +
            '<tbody>' +
              rows.map(function (row) {
                return '<tr class="table__row">' +
                  headers.map(function (h) { return '<td class="table__cell">' + esc(row[h] || '') + '</td>'; }).join('') +
                '</tr>';
              }).join('') +
            '</tbody>' +
          '</table>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — Section (generic fallback) — */
  function renderSection(d) {
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--default">' +
        '<div class="stack stack--vertical stack--gap-default">' +
          '<h2 class="typography typography--h2">' + esc(d.header) + '</h2>' +
          (d.description ? '<p class="typography typography--lead">' + esc(d.description) + '</p>' : '') +
          (d.content ? '<div class="card"><div class="card__content" style="padding-top:1.5rem;"><p class="typography typography--p">' + esc(d.content) + '</p></div></div>' : '') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* — HTML escape — */
  function esc(s) {
    if (!s) return '';
    var el = document.createElement('div');
    el.appendChild(document.createTextNode(String(s)));
    return el.innerHTML;
  }
}

/* Handle async script loading */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', desiderioInit);
} else {
  desiderioInit();
}
