/**
 * Desiderio — Component Browser (Storybook-like)
 *
 * Hash-based routing, sidebar navigation, live preview with fixture data,
 * responsive viewport switching, and code tab.
 */
'use strict';

function desiderioInit() {
  var app = document.getElementById('docs-app');
  if (!app) return;

  /* ================================================================ */
  /*  Load fixture data                                                */
  /* ================================================================ */
  var fixturesEl = document.getElementById('styleguide-fixtures');
  var fixtures = {};
  try { fixtures = JSON.parse(fixturesEl.textContent); } catch (_) {}

  /* ================================================================ */
  /*  DOM refs                                                         */
  /* ================================================================ */
  var welcomeEl = document.getElementById('docs-welcome');
  var componentEl = document.getElementById('docs-component');
  var nameEl = document.getElementById('docs-component-name');
  var ctypeEl = document.getElementById('docs-component-ctype');
  var previewFrame = document.getElementById('docs-preview-frame');
  var previewPanel = document.getElementById('docs-preview-panel');
  var codePanel = document.getElementById('docs-code-panel');
  var codeContent = document.getElementById('docs-code-content');
  var searchInput = document.getElementById('docs-search');
  var allNavLinks = document.querySelectorAll('.docs__nav-link');

  /* ================================================================ */
  /*  Sidebar — group collapse/expand                                  */
  /* ================================================================ */
  document.querySelectorAll('.docs__nav-group-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var groupId = btn.dataset.toggleGroup;
      var list = document.getElementById('nav-' + groupId);
      if (!list) return;
      var isCollapsed = list.classList.contains('docs__nav-list--collapsed');
      list.classList.toggle('docs__nav-list--collapsed');
      btn.setAttribute('aria-expanded', isCollapsed ? 'true' : 'false');
    });
  });

  /* ================================================================ */
  /*  Sidebar — search/filter                                          */
  /* ================================================================ */
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.toLowerCase().trim();
      allNavLinks.forEach(function (link) {
        var name = (link.dataset.name || '').toLowerCase() + ' ' + (link.dataset.ctype || '').toLowerCase();
        link.closest('li').style.display = (q === '' || name.includes(q)) ? '' : 'none';
      });
      // Hide empty groups
      document.querySelectorAll('.docs__nav-group').forEach(function (group) {
        var visible = group.querySelectorAll('li:not([style*="display: none"])');
        group.style.display = visible.length > 0 || q === '' ? '' : 'none';
      });
    });
  }

  /* ================================================================ */
  /*  Tabs — Preview / Code                                            */
  /* ================================================================ */
  document.querySelectorAll('.docs__tabs .tabs__trigger').forEach(function (tab) {
    tab.addEventListener('click', function () {
      document.querySelectorAll('.docs__tabs .tabs__trigger').forEach(function (t) {
        t.classList.remove('tabs__trigger--active');
      });
      tab.classList.add('tabs__trigger--active');

      var target = tab.dataset.tab;
      previewPanel.style.display = target === 'preview' ? '' : 'none';
      codePanel.style.display = target === 'code' ? '' : 'none';
    });
  });

  /* ================================================================ */
  /*  Viewport buttons                                                 */
  /* ================================================================ */
  document.querySelectorAll('.docs__viewport-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.docs__viewport-btn').forEach(function (b) {
        b.classList.remove('docs__viewport-btn--active');
      });
      btn.classList.add('docs__viewport-btn--active');
      previewFrame.dataset.viewport = btn.dataset.viewport;
    });
  });

  /* ================================================================ */
  /*  Router — hash-based navigation                                   */
  /* ================================================================ */
  function showComponent(ctype) {
    var data = fixtures[ctype];
    if (!data) {
      welcomeEl.style.display = '';
      componentEl.style.display = 'none';
      return;
    }

    // Find the name from nav
    var navLink = document.querySelector('.docs__nav-link[data-ctype="' + ctype + '"]');
    var name = navLink ? navLink.dataset.name : ctype.replace('shadcn2fluid_', '');

    // Update header
    nameEl.textContent = name;
    ctypeEl.textContent = ctype;

    // Show component, hide welcome
    welcomeEl.style.display = 'none';
    componentEl.style.display = '';

    // Reset to preview tab
    document.querySelectorAll('.docs__tabs .tabs__trigger').forEach(function (t) {
      t.classList.toggle('tabs__trigger--active', t.dataset.tab === 'preview');
    });
    previewPanel.style.display = '';
    codePanel.style.display = 'none';

    // Render preview
    previewFrame.innerHTML = renderPreview(data);
    previewFrame.dataset.viewport = 'desktop';

    // Reset viewport buttons
    document.querySelectorAll('.docs__viewport-btn').forEach(function (b) {
      b.classList.toggle('docs__viewport-btn--active', b.dataset.viewport === 'desktop');
    });

    // Generate code snippet
    codeContent.textContent = generateCodeSnippet(ctype, data);

    // Update sidebar active state
    allNavLinks.forEach(function (link) {
      link.classList.toggle('docs__nav-link--active', link.dataset.ctype === ctype);
    });

    // Ensure the active link is visible (expand parent group if collapsed)
    if (navLink) {
      var list = navLink.closest('.docs__nav-list');
      if (list && list.classList.contains('docs__nav-list--collapsed')) {
        list.classList.remove('docs__nav-list--collapsed');
      }
      navLink.scrollIntoView({ block: 'nearest' });
    }

    // Scroll main to top
    document.querySelector('.docs__main')?.scrollTo(0, 0);
  }

  function onHashChange() {
    var hash = window.location.hash;
    if (hash && hash.startsWith('#component-')) {
      var ctype = hash.replace('#component-', '');
      showComponent(ctype);
    } else {
      welcomeEl.style.display = '';
      componentEl.style.display = 'none';
      allNavLinks.forEach(function (l) { l.classList.remove('docs__nav-link--active'); });
    }
  }

  window.addEventListener('hashchange', onHashChange);
  onHashChange();

  // Sidebar link clicks
  allNavLinks.forEach(function (link) {
    link.addEventListener('click', function (e) {
      // Let the hashchange handler deal with it
    });
  });

  /* ================================================================ */
  /*  SVG Icons (Lucide subset)                                        */
  /* ================================================================ */
  var SVG = {
    x: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
    check: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
    star: '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    starEmpty: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    arrowRight: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>'
  };

  /* ================================================================ */
  /*  Code snippet generator                                           */
  /* ================================================================ */
  function generateCodeSnippet(ctype, data) {
    var name = ctype.replace('shadcn2fluid_', '');
    var lines = [
      '<-- Content Element: ' + name + ' -->',
      '<s2f:layout.section>',
      '  <s2f:layout.container>',
    ];

    if (data._type === 'hero') {
      lines.push('    <s2f:atom.badge variant="outline">{data.badge}</s2f:atom.badge>');
      lines.push('    <s2f:atom.typography tag="h1" variant="h1">');
      lines.push('      {data -> f:render.text(field: \'header\')}');
      lines.push('    </s2f:atom.typography>');
      lines.push('    <s2f:atom.typography variant="lead">{data.subheadline}</s2f:atom.typography>');
      lines.push('    <s2f:atom.button variant="default" size="lg">');
      lines.push('      <f:link.typolink parameter="{data.primary_button_link}">');
      lines.push('        {data.primary_button_text}');
      lines.push('      </f:link.typolink>');
      lines.push('    </s2f:atom.button>');
    } else if (data._type === 'grid') {
      lines.push('    <s2f:atom.typography tag="h2" variant="h2">{data -> f:render.text(field: \'header\')}</s2f:atom.typography>');
      lines.push('    <s2f:layout.grid cols="3">');
      lines.push('      <f:for each="{data.items}" as="item">');
      lines.push('        <s2f:molecule.card>');
      lines.push('          <div class="card__header">');
      lines.push('            <h3 class="card__title">{item -> f:render.text(field: \'title\')}</h3>');
      lines.push('            <p class="card__description">{item -> f:render.text(field: \'description\')}</p>');
      lines.push('          </div>');
      lines.push('        </s2f:molecule.card>');
      lines.push('      </f:for>');
      lines.push('    </s2f:layout.grid>');
    } else if (data._type === 'form') {
      lines.push('    <s2f:molecule.card>');
      lines.push('      <div class="card__header">');
      lines.push('        <h3 class="card__title">{data -> f:render.text(field: \'header\')}</h3>');
      lines.push('      </div>');
      lines.push('      <div class="card__content">');
      lines.push('        <s2f:atom.label>Email</s2f:atom.label>');
      lines.push('        <s2f:atom.input type="email" placeholder="you@example.com"/>');
      lines.push('      </div>');
      lines.push('      <div class="card__footer">');
      lines.push('        <s2f:atom.button variant="default">{data.button_text}</s2f:atom.button>');
      lines.push('      </div>');
      lines.push('    </s2f:molecule.card>');
    } else if (data._type === 'list') {
      lines.push('    <s2f:atom.typography tag="h2" variant="h2">{data -> f:render.text(field: \'header\')}</s2f:atom.typography>');
      lines.push('    <s2f:molecule.accordion>');
      lines.push('      <f:for each="{data.items}" as="item">');
      lines.push('        <s2f:molecule.accordionItem>');
      lines.push('          <div class="accordion__trigger">{item.title}</div>');
      lines.push('          <div class="accordion__body">{item.description}</div>');
      lines.push('        </s2f:molecule.accordionItem>');
      lines.push('      </f:for>');
      lines.push('    </s2f:molecule.accordion>');
    } else {
      lines.push('    <s2f:atom.typography tag="h2" variant="h2">');
      lines.push('      {data -> f:render.text(field: \'header\')}');
      lines.push('    </s2f:atom.typography>');
      lines.push('    <s2f:atom.typography variant="lead">{data.description}</s2f:atom.typography>');
    }

    lines.push('  </s2f:layout.container>');
    lines.push('</s2f:layout.section>');

    return lines.join('\n');
  }

  /* ================================================================ */
  /*  RENDER — real shadcn2fluid BEM classes                           */
  /* ================================================================ */
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

  function renderGrid(d) {
    var items = d.items || [];
    var cols = items.length <= 3 ? 'grid--cols-3' : 'grid--cols-4';
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--lg">' +
        '<div style="text-align:center;margin-bottom:2rem;">' +
          '<h2 class="typography typography--h2">' + esc(d.header) + '</h2>' +
          (d.description ? '<p class="typography typography--muted" style="margin-top:0.5rem;">' + esc(d.description) + '</p>' : '') +
        '</div>' +
        '<div class="grid ' + cols + '">' +
          items.map(function (item) {
            return '<div class="card"><div class="card__header">' +
              (item.icon ? '<span style="font-size:1.75rem;line-height:1;">' + item.icon + '</span>' : '') +
              '<h3 class="card__title">' + esc(item.title) + '</h3>' +
              (item.description ? '<p class="card__description">' + esc(item.description) + '</p>' : '') +
            '</div></div>';
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }

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
              '<div class="accordion__trigger" style="cursor:default;"><span>' + esc(item.title) + '</span></div>' +
              (item.description ? '<div class="accordion__body">' + esc(item.description) + '</div>' : '') +
            '</div>';
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }

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

  function renderForm(d) {
    var fields = d.fields || [{ label: 'Email', type: 'email', placeholder: 'you@example.com' }];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--sm">' +
        '<div class="card">' +
          '<div class="card__header"><h3 class="card__title">' + esc(d.header) + '</h3>' +
            (d.description ? '<p class="card__description">' + esc(d.description) + '</p>' : '') +
          '</div>' +
          '<div class="card__content"><div class="stack stack--vertical stack--gap-default">' +
            fields.map(function (f) {
              return '<div class="stack stack--vertical stack--gap-sm">' +
                '<label class="label">' + esc(f.label) + '</label>' +
                '<input class="input" type="' + (f.type || 'text') + '" placeholder="' + esc(f.placeholder || '') + '" disabled />' +
              '</div>';
            }).join('') +
          '</div></div>' +
          '<div class="card__footer"><a href="#" class="btn btn--default" style="width:100%;justify-content:center;">' + esc(d.primaryButton || 'Submit') + '</a></div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

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
              '<div class="card__content"><div class="stack stack--vertical stack--gap-sm">' +
                (plan.features || []).map(function (f) {
                  return '<div class="stack stack--horizontal stack--align-center stack--gap-sm">' +
                    '<span class="icon" style="color:var(--primary);">' + SVG.check + '</span>' +
                    '<span class="typography typography--small">' + esc(f) + '</span></div>';
                }).join('') +
              '</div></div>' +
              '<div class="card__footer"><a href="#" class="btn ' + (plan.featured ? 'btn--default' : 'btn--outline') + '" style="width:100%;justify-content:center;">' + esc(plan.button || 'Get Started') + '</a></div>' +
            '</div>';
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  function renderTestimonial(d) {
    var stars = '';
    if (d.rating) { for (var i = 0; i < 5; i++) stars += '<span class="icon">' + (i < d.rating ? SVG.star : SVG.starEmpty) + '</span>'; }
    var initial = (d.author || 'A').charAt(0).toUpperCase();
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--sm" style="text-align:center;">' +
        '<div class="stack stack--vertical stack--align-center stack--gap-default">' +
          '<p class="typography typography--h1" style="opacity:0.15;line-height:1;">\u201C</p>' +
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

  function renderStats(d) {
    var items = d.stats || [];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--default">' +
        (d.header ? '<h2 class="typography typography--h2" style="text-align:center;margin-bottom:2rem;">' + esc(d.header) + '</h2>' : '') +
        '<div class="grid grid--cols-' + Math.min(items.length, 4) + '" style="text-align:center;">' +
          items.map(function (s) {
            return '<div class="stack stack--vertical stack--align-center">' +
              '<span class="typography typography--h1" style="color:var(--primary);">' + esc(s.value) + '</span>' +
              '<span class="typography typography--muted">' + esc(s.label) + '</span></div>';
          }).join('') +
        '</div>' +
      '</div>' +
    '</div>';
  }

  function renderNav(d) {
    var links = d.links || [];
    return '<div class="section section--spacing-sm"><div class="container container--lg">' +
      '<div class="card" style="padding:0.75rem 1.5rem;">' +
        '<div class="stack stack--horizontal stack--align-center" style="justify-content:space-between;flex-wrap:wrap;gap:1rem;">' +
          '<span class="typography typography--large">' + esc(d.logo || 'Brand') + '</span>' +
          '<div class="stack stack--horizontal stack--gap-sm">' +
            links.map(function (l, i) { return '<a href="#" class="btn ' + (i === 0 ? 'btn--ghost' : 'btn--link') + ' btn--sm">' + esc(l) + '</a>'; }).join('') +
          '</div>' +
          (d.primaryButton ? '<a href="#" class="btn btn--default btn--sm">' + esc(d.primaryButton) + '</a>' : '') +
        '</div>' +
      '</div>' +
    '</div></div>';
  }

  function renderFooter(d) {
    var columns = d.columns || [];
    return '<div class="section section--spacing-sm"><div class="container container--lg">' +
      '<div style="border-top:1px solid var(--border);padding-top:2rem;">' +
        '<div class="grid grid--cols-4">' +
          columns.map(function (col) {
            return '<div class="stack stack--vertical stack--gap-sm">' +
              '<h4 class="typography typography--small" style="font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted-foreground);">' + esc(col.title) + '</h4>' +
              (col.links || []).map(function (l) { return '<a href="#" class="link link--muted" style="font-size:0.875rem;">' + esc(l) + '</a>'; }).join('') +
            '</div>';
          }).join('') +
        '</div>' +
        '<div class="separator separator--horizontal" style="margin:2rem 0 1.5rem;"></div>' +
        '<p class="typography typography--muted" style="font-size:0.75rem;">' + esc(d.copyright || '\u00A9 2025 Company.') + '</p>' +
      '</div>' +
    '</div></div>';
  }

  function renderTeam(d) {
    var members = d.members || [];
    return '<div class="section section--spacing-sm"><div class="container container--lg">' +
      (d.header ? '<div style="text-align:center;margin-bottom:2rem;">' +
        '<h2 class="typography typography--h2">' + esc(d.header) + '</h2>' +
        (d.description ? '<p class="typography typography--muted" style="margin-top:0.5rem;">' + esc(d.description) + '</p>' : '') +
      '</div>' : '') +
      '<div class="grid grid--cols-4">' +
        members.map(function (m) {
          return '<div class="card" style="text-align:center;"><div class="card__header" style="align-items:center;">' +
            '<div class="avatar avatar--lg"><span class="avatar__fallback">' + (m.name || 'A').charAt(0) + '</span></div>' +
            '<h3 class="card__title" style="font-size:0.9375rem;">' + esc(m.name) + '</h3>' +
            '<p class="card__description">' + esc(m.role || '') + '</p>' +
          '</div></div>';
        }).join('') +
      '</div>' +
    '</div></div>';
  }

  function renderTable(d) {
    var rows = d.rows || [];
    var headers = d.headers || (rows.length > 0 ? Object.keys(rows[0]) : []);
    return '<div class="section section--spacing-sm"><div class="container container--default">' +
      (d.header ? '<h3 class="typography typography--h3" style="margin-bottom:1rem;">' + esc(d.header) + '</h3>' : '') +
      '<div class="table-wrapper"><table class="table">' +
        '<thead class="table__header"><tr class="table__row">' +
          headers.map(function (h) { return '<th class="table__head">' + esc(h) + '</th>'; }).join('') +
        '</tr></thead><tbody>' +
          rows.map(function (row) {
            return '<tr class="table__row">' + headers.map(function (h) { return '<td class="table__cell">' + esc(row[h] || '') + '</td>'; }).join('') + '</tr>';
          }).join('') +
        '</tbody></table></div>' +
    '</div></div>';
  }

  function renderSection(d) {
    return '<div class="section section--spacing-sm"><div class="container container--default">' +
      '<div class="stack stack--vertical stack--gap-default">' +
        '<h2 class="typography typography--h2">' + esc(d.header) + '</h2>' +
        (d.description ? '<p class="typography typography--lead">' + esc(d.description) + '</p>' : '') +
        (d.content ? '<div class="card"><div class="card__content" style="padding-top:1.5rem;"><p class="typography typography--p">' + esc(d.content) + '</p></div></div>' : '') +
      '</div>' +
    '</div></div>';
  }

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
