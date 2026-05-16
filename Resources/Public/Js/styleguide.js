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
  var allOverviewCards = document.querySelectorAll('.docs__overview-card');

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
      allOverviewCards.forEach(function (card) {
        var name = (card.dataset.name || '').toLowerCase() + ' ' + (card.dataset.ctype || '').toLowerCase() + ' ' + card.textContent.toLowerCase();
        card.style.display = (q === '' || name.includes(q)) ? '' : 'none';
      });
      // Hide empty groups
      document.querySelectorAll('.docs__nav-group').forEach(function (group) {
        var visible = group.querySelectorAll('li:not([style*="display: none"])');
        group.style.display = visible.length > 0 || q === '' ? '' : 'none';
      });
      document.querySelectorAll('.docs__overview-group').forEach(function (group) {
        var visible = group.querySelectorAll('.docs__overview-card:not([style*="display: none"])');
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
    var name = navLink ? navLink.dataset.name : ctype.replace('desiderio_', '');

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
  window.requestAnimationFrame(onHashChange);

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
  var ICONS = {
    'alert-triangle': '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
    'book-open': '<path d="M12 7v14"/><path d="M3 18a2 2 0 0 1 2-2h7"/><path d="M3 6a2 2 0 0 1 2-2h7v17H5a2 2 0 0 0-2 2z"/><path d="M21 6a2 2 0 0 0-2-2h-7v17h7a2 2 0 0 1 2 2z"/>',
    briefcase: '<path d="M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1"/><path d="M3 7h18v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M3 13h18"/><path d="M10 13v1h4v-1"/>',
    building: '<path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/><path d="M16 8h2a2 2 0 0 1 2 2v11"/><path d="M9 7h2"/><path d="M9 11h2"/><path d="M9 15h2"/><path d="M4 21h16"/>',
    chart: '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
    'chart-no-axes-combined': '<path d="M12 16v5"/><path d="M16 14v7"/><path d="M20 10v11"/><path d="m22 3-8.646 8.646a.5.5 0 0 1-.708 0L9.354 8.354a.5.5 0 0 0-.708 0L2 15"/><path d="M4 18v3"/><path d="M8 14v7"/>',
    check: '<path d="m20 6-11 11-5-5"/>',
    'check-circle': '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
    clock: '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    cloud: '<path d="M17.5 19H7a5 5 0 1 1 1.6-9.73A6 6 0 0 1 20 12.5 3.5 3.5 0 0 1 17.5 19z"/>',
    database: '<ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v6c0 1.66 3.13 3 7 3s7-1.34 7-3V5"/><path d="M5 11v6c0 1.66 3.13 3 7 3s7-1.34 7-3v-6"/>',
    download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>',
    file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
    globe: '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
    handshake: '<path d="m11 17 2 2a2.8 2.8 0 0 0 4-4"/><path d="m14 14 2.5 2.5a2.8 2.8 0 0 0 4-4L15 7l-3 3a2.8 2.8 0 0 1-4-4l1-1"/><path d="m7 12-2 2a2.8 2.8 0 1 0 4 4l2-2"/><path d="M2 7l4-4 4 4"/><path d="m22 7-4-4-4 4"/>',
    heart: '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7z"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
    lightning: '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46L12 9h8a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46L12 15H4z"/>',
    lock: '<rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    mail: '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/>',
    'map-pin': '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
    phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13 1 .36 1.98.7 2.92a2 2 0 0 1-.45 2.11L8.09 10a16 16 0 0 0 6 6l1.25-1.27a2 2 0 0 1 2.11-.45c.94.34 1.92.57 2.92.7A2 2 0 0 1 22 16.92z"/>',
    rocket: '<path d="M4.5 16.5c-1.5 1.26-2 4-2 4s2.74-.5 4-2c.84-.99.78-2.49-.14-3.4-.91-.92-2.41-.98-3.4-.14z"/><path d="m12 15-3-3a22 22 0 0 1 2-5.5C13.5 1.5 19 2 22 2c0 3 .5 8.5-4.5 11a22 22 0 0 1-5.5 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/><circle cx="16" cy="8" r="2"/>',
    search: '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
    settings: '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.72l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
    shield: '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>',
    'shield-check': '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
    sparkles: '<path d="M9.94 14.7 8.5 18.5 7.06 14.7 3.5 13.25l3.56-1.45L8.5 8l1.44 3.8 3.56 1.45z"/><path d="M17.5 3.5 16.4 6.4 13.5 7.5l2.9 1.1 1.1 2.9 1.1-2.9 2.9-1.1-2.9-1.1z"/><path d="M18 15.5 17.3 17.3 15.5 18l1.8.7.7 1.8.7-1.8 1.8-.7-1.8-.7z"/>',
    star: '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
    users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'x-circle': '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
    zap: '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46L12 9h8a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46L12 15H4z"/>'
  };

  function renderIcon(name, size) {
    if (!name || name === 'none') return '';
    var aliases = { default: 'sparkles', destructive: 'x-circle', success: 'check-circle', warning: 'alert-triangle' };
    var key = String(name).toLowerCase();
    var paths = ICONS[aliases[key] || key] || ICONS.sparkles;
    return '<svg class="icon" width="' + (size || 24) + '" height="' + (size || 24) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' + paths + '</svg>';
  }

  function previewItems(d) {
    return d.items || d.feature_items || d.value_items || d.perks || d.counters || [];
  }

  /* ================================================================ */
  /*  Code snippet generator                                           */
  /* ================================================================ */
  function generateCodeSnippet(ctype, data) {
    var name = ctype.replace('desiderio_', '');
    var lines = [
      '<!-- Content Element: ' + name + ' -->',
      '<d:layout.section>',
      '  <d:layout.container>',
    ];

    if (data._type === 'hero') {
      lines.push('    <d:atom.badge variant="outline">{data.badge}</d:atom.badge>');
      lines.push('    <d:atom.typography tag="h1" variant="h1">');
      lines.push('      {data -> f:render.text(field: \'header\')}');
      lines.push('    </d:atom.typography>');
      lines.push('    <d:atom.typography variant="lead">{data.subheadline}</d:atom.typography>');
      lines.push('    <d:atom.button variant="default" size="lg">');
      lines.push('      <f:link.typolink parameter="{data.primary_button_link}">');
      lines.push('        {data.primary_button_text}');
      lines.push('      </f:link.typolink>');
      lines.push('    </d:atom.button>');
    } else if (data._type === 'grid') {
      lines.push('    <d:atom.typography tag="h2" variant="h2">{data -> f:render.text(field: \'header\')}</d:atom.typography>');
      lines.push('    <d:layout.grid cols="3">');
      lines.push('      <f:for each="{data.items}" as="item">');
      lines.push('        <d:molecule.card>');
      lines.push('          <div class="card__header">');
      lines.push('            <h3 class="card__title">{item -> f:render.text(field: \'title\')}</h3>');
      lines.push('            <p class="card__description">{item -> f:render.text(field: \'description\')}</p>');
      lines.push('          </div>');
      lines.push('        </d:molecule.card>');
      lines.push('      </f:for>');
      lines.push('    </d:layout.grid>');
    } else if (data._type === 'form') {
      lines.push('    <d:molecule.card>');
      lines.push('      <div class="card__header">');
      lines.push('        <h3 class="card__title">{data -> f:render.text(field: \'header\')}</h3>');
      lines.push('      </div>');
      lines.push('      <div class="card__content">');
      lines.push('        <d:atom.label>Email</d:atom.label>');
      lines.push('        <d:atom.input type="email" placeholder="you@example.com"/>');
      lines.push('      </div>');
      lines.push('      <div class="card__footer">');
      lines.push('        <d:atom.button variant="default">{data.button_text}</d:atom.button>');
      lines.push('      </div>');
      lines.push('    </d:molecule.card>');
    } else if (data._type === 'list') {
      lines.push('    <d:atom.typography tag="h2" variant="h2">{data -> f:render.text(field: \'header\')}</d:atom.typography>');
      lines.push('    <d:molecule.accordion>');
      lines.push('      <f:for each="{data.items}" as="item">');
      lines.push('        <d:molecule.accordionItem>');
      lines.push('          <div class="accordion__trigger">{item.title}</div>');
      lines.push('          <div class="accordion__body">{item.description}</div>');
      lines.push('        </d:molecule.accordionItem>');
      lines.push('      </f:for>');
      lines.push('    </d:molecule.accordion>');
    } else {
      lines.push('    <d:atom.typography tag="h2" variant="h2">');
      lines.push('      {data -> f:render.text(field: \'header\')}');
      lines.push('    </d:atom.typography>');
      lines.push('    <d:atom.typography variant="lead">{data.description}</d:atom.typography>');
    }

    lines.push('  </d:layout.container>');
    lines.push('</d:layout.section>');

    return lines.join('\n');
  }

  /* ================================================================ */
  /*  RENDER — shadcn-compatible Desiderio preview classes              */
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
    var items = previewItems(d);
    var cols = items.length <= 3 ? 'grid--cols-3' : 'grid--cols-4';
    var description = d.description || d.subheadline;
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--lg">' +
        '<div style="text-align:center;margin-bottom:2rem;">' +
          '<h2 class="typography typography--h2">' + esc(d.header) + '</h2>' +
          (description ? '<p class="typography typography--muted" style="margin-top:0.5rem;">' + esc(description) + '</p>' : '') +
        '</div>' +
        '<div class="grid ' + cols + '">' +
          items.map(function (item) {
            var itemIcon = renderIcon(item.icon || item.icon_name || item.tab_icon, 28);
            return '<div class="card"><div class="card__header">' +
              (itemIcon ? '<span class="icon" style="color:var(--primary);">' + itemIcon + '</span>' : '') +
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
    var cardIcon = renderIcon(d.icon || d.icon_style || 'sparkles', 48);
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--sm">' +
        '<div class="card">' +
          '<div class="aspect-ratio aspect-ratio--16-9" style="background:var(--muted);display:flex;align-items:center;justify-content:center;">' +
            '<span class="icon" style="color:var(--primary);position:static;">' + cardIcon + '</span>' +
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
    var items = d.stats || d.counters || [];
    return '<div class="section section--spacing-sm">' +
      '<div class="container container--default">' +
        (d.header ? '<h2 class="typography typography--h2" style="text-align:center;margin-bottom:2rem;">' + esc(d.header) + '</h2>' : '') +
        '<div class="grid grid--cols-' + Math.min(items.length, 4) + '" style="text-align:center;">' +
          items.map(function (s) {
            var statIcon = renderIcon(s.icon || s.icon_name, 28);
            return '<div class="stack stack--vertical stack--align-center">' +
              (statIcon ? '<span class="icon" style="color:var(--primary);">' + statIcon + '</span>' : '') +
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
