/**
 * desiderio.js - Shared vanilla JS utilities for desiderio TYPO3 components.
 * Framework-free, auto-initialised on DOMContentLoaded.
 */
document.addEventListener('DOMContentLoaded', () => {
  /* ------------------------------------------------------------------ */
  /*  1. Accordion                                                       */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-accordion]').forEach(root => {
    const single = (root.dataset.type || 'single') === 'single';

    const setOpen = (item, open) => {
      const state = open ? 'open' : 'closed';
      item.dataset.state = state;
      const trigger = item.querySelector('[data-d-accordion-trigger]');
      const content = item.querySelector('[data-d-accordion-content]');
      if (trigger) {
        trigger.setAttribute('aria-expanded', String(open));
        trigger.dataset.state = state;
      }
      if (content) {
        content.dataset.state = state;
        content.hidden = !open;
      }
    };

    root.addEventListener('click', e => {
      const trigger = e.target.closest('[data-d-accordion-trigger]');
      const item = trigger?.closest('[data-d-accordion-item]');
      if (!item) return;

      const isOpen = trigger.getAttribute('aria-expanded') === 'true';
      if (single && !isOpen) {
        root.querySelectorAll('[data-d-accordion-item]').forEach(other => {
          if (other !== item) setOpen(other, false);
        });
      }
      setOpen(item, !isOpen);
    });
  });

  /* ------------------------------------------------------------------ */
  /*  2. Tabs                                                            */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-tabs]').forEach(root => {
    const activate = value => {
      root.querySelectorAll('[data-d-tabs-trigger]').forEach(t => {
        const active = t.dataset.value === value;
        t.setAttribute('aria-selected', String(active));
        t.dataset.state = active ? 'active' : 'inactive';
        t.toggleAttribute('data-active', active);
      });
      root.querySelectorAll('[data-d-tabs-content]').forEach(c => {
        const active = c.dataset.value === value;
        c.dataset.state = active ? 'active' : 'inactive';
        c.hidden = !active;
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
  /*  2b. Dismissible alerts/notifications                               */
  /* ------------------------------------------------------------------ */
  document.addEventListener('click', e => {
    const button = e.target.closest('[data-d-dismiss]');
    if (button) button.closest('[data-d-dismissible]')?.remove();
  });

  /* ------------------------------------------------------------------ */
  /*  3. Gallery browser                                                 */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-gallery]').forEach(root => {
    const main = root.querySelector('[data-d-gallery-main]');
    const title = root.querySelector('[data-d-gallery-title]');
    const description = root.querySelector('[data-d-gallery-description]');
    const credit = root.querySelector('[data-d-gallery-credit]');
    const link = root.querySelector('[data-d-gallery-link]');
    const thumbs = Array.from(root.querySelectorAll('[data-d-gallery-thumb]'));

    if (!main || thumbs.length === 0) return;

    const activate = thumb => {
      const src = thumb.dataset.largeSrc;
      if (!src) return;

      main.src = src;
      main.alt = thumb.dataset.thumbAlt || thumb.dataset.thumbTitle || '';

      // Keep an enclosing lightbox trigger pointing at the active image.
      // Note: "data-large-src-2x" does not camelize cleanly (digit after
      // hyphen), so read it via getAttribute.
      const lightboxWrap = main.closest('[data-d-lightbox]');
      if (lightboxWrap) {
        lightboxWrap.dataset.dLightboxSrc = thumb.getAttribute('data-large-src-2x') || src;
      }

      if (title) {
        title.textContent = thumb.dataset.thumbTitle || '';
        title.hidden = title.textContent.trim() === '';
      }
      if (description) {
        description.textContent = thumb.dataset.thumbDescription || '';
        description.hidden = description.textContent.trim() === '';
      }
      if (credit) {
        credit.textContent = thumb.dataset.thumbCredit || '';
        credit.hidden = credit.textContent.trim() === '';
      }
      if (link) {
        const href = thumb.dataset.thumbLink || '';
        link.hidden = href === '';
        if (href) link.setAttribute('href', href);
        else link.removeAttribute('href');
      }

      thumbs.forEach(item => item.setAttribute('aria-current', String(item === thumb)));
    };

    thumbs.forEach(thumb => {
      thumb.addEventListener('click', () => activate(thumb));
    });

    const selected = thumbs.find(thumb => thumb.getAttribute('aria-current') === 'true') || thumbs[0];
    activate(selected);
  });

  /* ------------------------------------------------------------------ */
  /*  4. Dark-mode toggle                                                */
  /* ------------------------------------------------------------------ */
  const html = document.documentElement;
  const body = document.body;
  const media = window.matchMedia('(prefers-color-scheme: dark)');
  const storedTheme = () => localStorage.getItem('d-theme');
  const siteTheme = () => body?.dataset.theme || 'system';
  const themeLabels = {
    light: 'Light',
    dark: 'Dark',
    system: 'System',
  };
  const resolveTheme = preference => (
    preference === 'dark' || (preference === 'system' && media.matches)
  );

  const applyTheme = preference => {
    const selected = preference || siteTheme();
    const isDark = resolveTheme(selected);

    html.classList.toggle('dark', isDark);
    html.dataset.theme = selected;
    if (body) body.dataset.themeCurrent = isDark ? 'dark' : 'light';

    document.querySelectorAll('[data-d-theme-toggle]').forEach(btn => {
      btn.setAttribute('aria-pressed', String(isDark));
      btn.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    });

    document.querySelectorAll('[data-d-theme-option]').forEach(btn => {
      const active = btn.dataset.dThemeOption === selected;
      btn.setAttribute('aria-pressed', String(active));
      btn.dataset.state = active ? 'active' : 'inactive';
    });

    document.querySelectorAll('[data-d-theme-switch]').forEach(root => {
      const labelKey = `dThemeLabel${selected.charAt(0).toUpperCase()}${selected.slice(1)}`;
      const label = root.dataset[labelKey] || themeLabels[selected] || themeLabels.system;
      const baseLabel = root.dataset.dThemeLabel || 'Colour scheme';
      root.dataset.themePreference = selected;

      root.querySelectorAll('[data-d-theme-current-icon]').forEach(icon => {
        const active = icon.dataset.dThemeCurrentIcon === selected;
        icon.hidden = !active;
        icon.dataset.state = active ? 'active' : 'inactive';
      });

      const labelNode = root.querySelector('[data-d-theme-current-label]');
      if (labelNode) labelNode.textContent = label;

      const summary = root.querySelector('[data-d-theme-summary]');
      if (summary) {
        summary.setAttribute('aria-label', `${baseLabel}: ${label}`);
        summary.setAttribute('title', `${baseLabel}: ${label}`);
      }
    });
  };

  applyTheme(storedTheme() || siteTheme());

  media.addEventListener?.('change', () => {
    if ((storedTheme() || siteTheme()) === 'system') {
      applyTheme('system');
    }
  });

  document.querySelectorAll('[data-d-theme-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
      const next = html.classList.contains('dark') ? 'light' : 'dark';
      localStorage.setItem('d-theme', next);
      applyTheme(next);
    });
  });

  document.querySelectorAll('[data-d-theme-option]').forEach(btn => {
    btn.addEventListener('click', () => {
      const next = btn.dataset.dThemeOption || 'system';
      localStorage.setItem('d-theme', next);
      applyTheme(next);
      const root = btn.closest('[data-d-theme-switch]');
      if (root && root.tagName.toLowerCase() === 'details') {
        root.open = false;
      }
    });
  });

  /* ------------------------------------------------------------------ */
  /*  5. Click-outside                                                   */
  /* ------------------------------------------------------------------ */
  document.addEventListener('click', e => {
    document.querySelectorAll('[data-d-click-outside]:not(.is-hidden)').forEach(el => {
      if (!el.contains(e.target)) el.classList.add('is-hidden');
    });

    document.querySelectorAll('details[data-d-close-on-outside][open]').forEach(el => {
      if (!el.contains(e.target)) el.open = false;
    });
  });

  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;

    document.querySelectorAll('details[data-d-close-on-outside][open]').forEach(el => {
      el.open = false;
      el.querySelector('summary')?.focus();
    });
  });

  /* ------------------------------------------------------------------ */
  /*  6. Mobile menu toggle                                              */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-menu-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetSel = btn.dataset.dMenuTarget;
      const target = targetSel ? document.querySelector(targetSel) : null;
      if (!target) return;

      const expanded = btn.getAttribute('aria-expanded') === 'true';
      const nextExpanded = !expanded;
      btn.setAttribute('aria-expanded', String(nextExpanded));
      target.classList.toggle('is-open', nextExpanded);
      target.classList.toggle('is-hidden', !nextExpanded);
    });
  });

  /* ------------------------------------------------------------------ */
  /*  6b. Primary nav submenu disclosure                                 */
  /* ------------------------------------------------------------------ */
  // Submenus open via their chevron button (APG disclosure navigation);
  // mouse users on the horizontal layout also get hover-open. Escape and
  // outside clicks dismiss (WCAG 1.4.13), focusout keeps only the submenu
  // the keyboard is actually in open.
  const navHoverMedia = window.matchMedia('(hover: hover) and (min-width: 1025px)');

  document.querySelectorAll('[data-d-primary-nav]').forEach(nav => {
    const items = [...nav.querySelectorAll('[data-d-nav-sub]')];
    if (items.length === 0) return;
    nav.dataset.dNavReady = 'true';

    const setOpen = (item, open) => {
      item.classList.toggle('is-open', open);
      item.querySelector('[data-d-subnav-toggle]')?.setAttribute('aria-expanded', String(open));
    };
    // Items can nest (Priority+ moves submenu items into the "More" panel),
    // so keeping one branch open means sparing the exception's ancestors and
    // descendants, not just the exception itself.
    const closeAll = except => items.forEach(item => {
      if (item === except) return;
      if (except && (item.contains(except) || except.contains(item))) return;
      setOpen(item, false);
    });

    items.forEach(item => {
      const toggle = item.querySelector('[data-d-subnav-toggle]');
      if (!toggle) return;

      toggle.addEventListener('click', () => {
        const open = !item.classList.contains('is-open');
        closeAll(item);
        setOpen(item, open);
      });

      // Hover-open stays off for items nested inside the "More" panel —
      // there the submenu expands inline and should only follow clicks.
      const nested = () => item.parentElement?.closest('[data-d-nav-sub]') !== null;

      let hoverTimer = null;
      item.addEventListener('pointerenter', event => {
        if (event.pointerType !== 'mouse' || !navHoverMedia.matches || nested()) return;
        window.clearTimeout(hoverTimer);
        hoverTimer = window.setTimeout(() => {
          closeAll(item);
          setOpen(item, true);
        }, 60);
      });
      item.addEventListener('pointerleave', event => {
        if (event.pointerType !== 'mouse' || !navHoverMedia.matches || nested()) return;
        window.clearTimeout(hoverTimer);
        hoverTimer = window.setTimeout(() => setOpen(item, false), 140);
      });

      item.addEventListener('focusout', () => {
        window.requestAnimationFrame(() => {
          if (!item.contains(document.activeElement)) setOpen(item, false);
        });
      });
    });

    document.addEventListener('click', event => {
      if (!nav.contains(event.target)) closeAll();
    });

    document.addEventListener('keydown', event => {
      if (event.key !== 'Escape') return;
      const openItems = items.filter(item => item.classList.contains('is-open'));
      if (openItems.length === 0) return;
      // Innermost open item holding focus first (items are in document
      // order, so for nested branches the deepest match comes last).
      const focused = openItems.filter(item => item.contains(document.activeElement)).pop();
      const open = focused || openItems[0];
      setOpen(open, false);
      if (focused) open.querySelector('[data-d-subnav-toggle]')?.focus();
    });
  });

  /* ------------------------------------------------------------------ */
  /*  6c. Priority+ overflow (one-row desktop nav)                       */
  /* ------------------------------------------------------------------ */
  // Keeps the horizontal nav on a single row: items that do not fit move
  // into the trailing "More" disclosure (already wired by section 6b).
  // Mobile/tablet (<= 1024px) restores everything into the burger panel.
  const navDesktopMedia = window.matchMedia('(min-width: 1025px)');

  document.querySelectorAll('[data-d-primary-nav]').forEach(nav => {
    const list = nav.querySelector('.desiderio-header__nav-list');
    const moreItem = nav.querySelector('[data-d-nav-more]');
    const moreList = nav.querySelector('[data-d-nav-more-list]');
    if (!list || !moreItem || !moreList) return;

    const navItems = [...list.children].filter(li => li !== moreItem);
    if (navItems.length === 0) return;

    const GAP = parseFloat(getComputedStyle(list).columnGap) || 4;
    let widths = [];
    let moreWidth = 0;
    let visibleCount = -1;

    const restoreAll = () => {
      navItems.forEach(li => list.insertBefore(li, moreItem));
      moreItem.hidden = true;
      visibleCount = navItems.length;
    };

    // Item widths only change with content/fonts, not viewport width, so
    // measure once per font state with everything laid out in the row.
    const measure = () => {
      restoreAll();
      delete nav.dataset.dNavPriority;
      widths = navItems.map(li => li.getBoundingClientRect().width);
      moreItem.hidden = false;
      moreWidth = moreItem.getBoundingClientRect().width;
      moreItem.hidden = true;
      visibleCount = -1;
    };

    const update = () => {
      if (!navDesktopMedia.matches) {
        if (visibleCount !== navItems.length) restoreAll();
        delete nav.dataset.dNavPriority;
        return;
      }

      const available = nav.clientWidth;
      const fitsAll = widths.reduce((sum, w) => sum + w, 0) + GAP * (navItems.length - 1) <= available;
      let next = navItems.length;
      if (!fitsAll) {
        let used = moreWidth;
        next = 0;
        while (next < navItems.length && used + widths[next] + GAP * (next + 1) <= available) {
          used += widths[next];
          next += 1;
        }
      }
      if (next === visibleCount) return;

      restoreAll();
      if (next < navItems.length) {
        navItems.slice(next).forEach(li => moreList.append(li));
        moreItem.hidden = false;
        nav.dataset.dNavPriority = 'true';
      } else {
        delete nav.dataset.dNavPriority;
      }
      visibleCount = next;
    };

    measure();
    update();

    let resizeFrame = null;
    let lastNavWidth = nav.clientWidth;
    const onResize = () => {
      if (nav.clientWidth === lastNavWidth) return;
      lastNavWidth = nav.clientWidth;
      window.cancelAnimationFrame(resizeFrame);
      resizeFrame = window.requestAnimationFrame(update);
    };
    new ResizeObserver(onResize).observe(nav);
    navDesktopMedia.addEventListener?.('change', () => { measure(); update(); });
    document.fonts?.ready?.then(() => { measure(); update(); });
  });

  /* ------------------------------------------------------------------ */
  /*  7. Header search expand/collapse                                   */
  /* ------------------------------------------------------------------ */
  const initHeaderSearch = (scope = document) => {
    scope.querySelectorAll('[data-d-header-search]').forEach(form => {
      if (form.dataset.dHeaderSearchInit === 'true') return;
      form.dataset.dHeaderSearchInit = 'true';

      const toggle = form.querySelector('[data-d-header-search-toggle]');
      const input = form.querySelector('.desiderio-header__search-input');
      if (!toggle || !input) return;

      const hasQuery = () => input.value.trim() !== '';

      const open = ({ focus = true } = {}) => {
        form.classList.remove('desiderio-header__search--collapsed');
        toggle.setAttribute('aria-expanded', 'true');
        input.removeAttribute('tabindex');
        if (focus) {
          window.requestAnimationFrame(() => input.focus());
        }
      };

      const close = ({ force = false } = {}) => {
        if (form.classList.contains('desiderio-header__search--collapsed')) return;
        if (!force && hasQuery()) return;

        form.classList.add('desiderio-header__search--collapsed');
        toggle.setAttribute('aria-expanded', 'false');
        input.setAttribute('tabindex', '-1');
        input.blur();
      };

      toggle.addEventListener('click', () => open());

      if (hasQuery()) {
        open({ focus: false });
      }

      document.addEventListener('click', event => {
        if (form.classList.contains('desiderio-header__search--collapsed')) return;
        if (form.contains(event.target)) return;
        close();
      });

      document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        if (form.classList.contains('desiderio-header__search--collapsed')) return;
        close({ force: true });
        toggle.focus();
      });
    });
  };

  initHeaderSearch();

  /* ------------------------------------------------------------------ */
  /*  7. Consent                                                        */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-consent]').forEach(banner => {
    const key = 'd-consent';
    const stored = localStorage.getItem(key);
    if (stored === 'accepted' || stored === 'declined') {
      body.dataset.consent = stored;
      return;
    }

    banner.hidden = false;

    const choose = value => {
      localStorage.setItem(key, value);
      body.dataset.consent = value;
      banner.hidden = true;
    };

    banner.querySelector('[data-d-consent-accept]')?.addEventListener('click', () => choose('accepted'));
    banner.querySelector('[data-d-consent-decline]')?.addEventListener('click', () => choose('declined'));
  });

  /* ------------------------------------------------------------------ */
  /*  8. Back to top                                                     */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-back-to-top]').forEach(btn => {
    const threshold = parseInt(btn.dataset.threshold, 10) || 300;

    const toggle = () => btn.classList.toggle('is-hidden', window.scrollY < threshold);
    toggle();
    window.addEventListener('scroll', toggle, { passive: true });

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reduce.matches ? 'auto' : 'smooth' }));
  });

  /* ------------------------------------------------------------------ */
  /*  9. Pricing slider                                                  */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-pricing-slider]').forEach(root => {
    const range = root.querySelector('[data-d-pricing-slider-range]');
    const tiers = Array.from(root.querySelectorAll('[data-d-pricing-slider-tier]'));

    if (!range || tiers.length === 0) return;

    const min = Number(range.min || 0);
    const max = Number(range.max || tiers.length - 1);
    const scale = max - min || 1;

    const getTierText = tier => {
      const volume = tier.querySelector('.pricing-slider__volume')?.textContent?.trim();
      const price = tier.querySelector('.pricing-slider__price')?.textContent?.trim();
      return [volume, price].filter(Boolean).join(', ');
    };

    const activate = () => {
      const rawValue = Number(range.value);
      const value = Number.isFinite(rawValue) ? rawValue : min;
      const position = Math.min(Math.max((value - min) / scale, 0), 1);
      const activeIndex = Math.round(position * (tiers.length - 1));

      tiers.forEach((tier, index) => {
        const active = index === activeIndex;
        tier.classList.toggle('pricing-slider__tier--active', active);
        tier.setAttribute('aria-current', String(active));
      });

      range.setAttribute('aria-valuetext', getTierText(tiers[activeIndex]));
    };

    activate();
    range.addEventListener('input', activate);
    range.addEventListener('change', activate);
  });

  /* ------------------------------------------------------------------ */
  /*  10. Solr suggest                                                   */
  /* ------------------------------------------------------------------ */
  const appendHighlightedText = (target, text, query) => {
    const source = String(text || '');
    const needle = String(query || '').trim();

    if (!needle) {
      target.textContent = source;
      return;
    }

    const lowerSource = source.toLocaleLowerCase();
    const lowerNeedle = needle.toLocaleLowerCase();
    let offset = 0;

    while (offset < source.length) {
      const match = lowerSource.indexOf(lowerNeedle, offset);

      if (match === -1) {
        target.append(document.createTextNode(source.slice(offset)));
        break;
      }

      if (match > offset) {
        target.append(document.createTextNode(source.slice(offset, match)));
      }

      const mark = document.createElement('mark');
      mark.className = 'd-solr-suggest__mark';
      mark.textContent = source.slice(match, match + needle.length);
      target.append(mark);
      offset = match + needle.length;
    }
  };

  const normalizeDocuments = documents => {
    if (Array.isArray(documents)) return documents;
    if (documents && typeof documents === 'object') return Object.values(documents);
    return [];
  };

  const createContentTypeLabels = input => ({
    pages: input.dataset.dSolrTypeLabelPages || 'Pages',
    tx_news_domain_model_news: input.dataset.dSolrTypeLabelNews || 'News',
    tt_address: input.dataset.dSolrTypeLabelAddresses || 'Addresses',
    tx_skillflow_skill: input.dataset.dSolrTypeLabelSkills || 'Skills',
  });

  class DesiderioSolrSuggest {
    constructor(form) {
      this.form = form;
      this.input = form.querySelector('[data-d-solr-suggest], .js-solr-q, input[name$="[q]"]');
      if (!this.input) return;

      this.disableLegacySuggest();

      this.minChars = parseInt(form.dataset.suggestMinChars, 10) || 3;
      this.debounceMs = parseInt(form.dataset.suggestDebounce, 10) || 220;
      this.maxSuggestions = parseInt(form.dataset.suggestMaxItems, 10) || 10;
      this.queryParam = form.dataset.suggestParam || 'tx_solr[queryString]';
      this.contentTypeLabels = createContentTypeLabels(this.input);
      this.timer = null;
      this.abortController = null;
      this.activeIndex = -1;
      this.items = [];

      const anchor = this.input.closest('.input-group') || this.input.parentElement || form;
      const listId = `d-solr-suggest-${Math.random().toString(36).slice(2)}`;

      anchor.classList.add('d-solr-suggest-anchor');
      this.list = document.createElement('ul');
      this.list.className = 'd-solr-suggest';
      this.list.id = listId;
      this.list.setAttribute('role', 'listbox');
      this.list.hidden = true;
      anchor.append(this.list);

      this.input.setAttribute('role', 'combobox');
      this.input.setAttribute('aria-autocomplete', 'list');
      this.input.setAttribute('aria-haspopup', 'listbox');
      this.input.setAttribute('aria-controls', listId);
      this.input.setAttribute('aria-expanded', 'false');
      this.input.setAttribute('autocomplete', 'off');

      this.input.addEventListener('input', () => this.onInput());
      this.input.addEventListener('keydown', event => this.onKeydown(event));
      this.input.addEventListener('focus', () => this.onInput());
      this.form.addEventListener('submit', event => this.onSubmit(event));
      document.addEventListener('click', event => {
        if (!this.form.contains(event.target) && !this.list.contains(event.target)) {
          this.close();
        }
      });
    }

    disableLegacySuggest() {
      this.input.classList.remove('tx-solr-suggest', 'tx-solr-suggest-focus');

      document
        .querySelectorAll('.autocomplete-suggestions.tx-solr-autosuggest, .tx-solr-autosuggest')
        .forEach(element => element.remove());
    }

    onSubmit(event) {
      if (this.input.value.trim() !== '') return;

      event.preventDefault();
      this.input.focus();
      this.close();
    }

    onInput() {
      window.clearTimeout(this.timer);

      const query = this.input.value.trim();
      if (query.length < this.minChars) {
        this.close();
        return;
      }

      this.timer = window.setTimeout(() => this.fetchSuggestions(query), this.debounceMs);
    }

    async fetchSuggestions(query) {
      this.abortController?.abort();
      this.abortController = new AbortController();

      try {
        const url = new URL(this.form.dataset.suggest, window.location.href);
        url.searchParams.set(this.queryParam, query);

        const response = await fetch(url.toString(), {
          headers: { Accept: 'application/json' },
          signal: this.abortController.signal,
        });

        if (!response.ok) {
          this.close();
          return;
        }

        const data = await response.json();

        if (query !== this.input.value.trim()) {
          return;
        }

        this.render(data, query);
      } catch (error) {
        if (error.name !== 'AbortError') {
          this.close();
        }
      }
    }

    render(data, query) {
      this.list.replaceChildren();
      this.items = [];
      this.activeIndex = -1;

      const suggestions = Object.entries(data?.suggestions || {})
        .slice(0, this.maxSuggestions)
        .map(([label, count]) => ({
          type: 'term',
          label,
          count,
        }));
      const documents = normalizeDocuments(data?.documents)
        .filter(document => document && document.title && document.link)
        .map(document => ({
          type: 'document',
          label: document.title,
          link: document.link,
          content: document.content || '',
          resultType: this.getContentTypeLabel(document.type),
        }));

      suggestions.forEach(item => this.appendOption(item, query));

      if (documents.length > 0) {
        const group = document.createElement('li');
        group.className = 'd-solr-suggest__group';
        group.setAttribute('role', 'presentation');
        group.textContent = this.form.dataset.suggestHeader || 'Top results';
        this.list.append(group);
        documents.forEach(item => this.appendOption(item, query));
      }

      if (this.items.length === 0) {
        this.close();
        return;
      }

      this.list.hidden = false;
      this.input.setAttribute('aria-expanded', 'true');
    }

    getContentTypeLabel(type) {
      const documentType = String(type || '');
      return this.contentTypeLabels[documentType] || documentType;
    }

    appendOption(item, query) {
      const option = document.createElement('li');
      const optionId = `${this.list.id}-option-${this.items.length}`;

      option.className = `d-solr-suggest__option d-solr-suggest__option--${item.type}`;
      option.id = optionId;
      option.setAttribute('role', 'option');
      option.setAttribute('aria-selected', 'false');
      option.addEventListener('pointerdown', event => {
        event.preventDefault();
        this.choose(item);
      });

      if (item.type === 'term') {
        const term = document.createElement('span');
        term.className = 'd-solr-suggest__term';
        appendHighlightedText(term, item.label, query);
        option.append(term);

        if (item.count !== undefined) {
          const count = document.createElement('span');
          count.className = 'd-solr-suggest__count';
          count.textContent = item.count;
          option.append(count);
        }
      } else {
        const body = document.createElement('span');
        body.className = 'd-solr-suggest__document';

        const title = document.createElement('span');
        title.className = 'd-solr-suggest__title';
        appendHighlightedText(title, item.label, query);
        body.append(title);

        if (item.content) {
          const content = document.createElement('span');
          content.className = 'd-solr-suggest__content';
          content.textContent = item.content;
          body.append(content);
        }

        option.append(body);

        if (item.resultType) {
          const type = document.createElement('span');
          type.className = 'd-solr-suggest__type';
          type.textContent = item.resultType;
          option.append(type);
        }
      }

      this.items.push({ item, element: option });
      this.list.append(option);
    }

    onKeydown(event) {
      if (this.list.hidden) return;

      if (event.key === 'ArrowDown') {
        event.preventDefault();
        this.moveActive(1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        this.moveActive(-1);
      } else if (event.key === 'Enter' && this.activeIndex >= 0) {
        event.preventDefault();
        this.choose(this.items[this.activeIndex].item);
      } else if (event.key === 'Escape') {
        this.close();
      }
    }

    moveActive(direction) {
      if (this.items.length === 0) return;

      this.activeIndex = (this.activeIndex + direction + this.items.length) % this.items.length;
      this.items.forEach(({ element }, index) => {
        const active = index === this.activeIndex;
        element.classList.toggle('is-active', active);
        element.setAttribute('aria-selected', String(active));
      });

      const activeElement = this.items[this.activeIndex].element;
      this.input.setAttribute('aria-activedescendant', activeElement.id);
      activeElement.scrollIntoView({ block: 'nearest' });
    }

    choose(item) {
      if (item.type === 'document' && item.link) {
        window.location.href = item.link;
        return;
      }

      this.input.value = item.label;
      this.close();

      if (typeof this.form.requestSubmit === 'function') {
        this.form.requestSubmit();
      } else {
        this.form.submit();
      }
    }

    close() {
      this.list.hidden = true;
      this.list.replaceChildren();
      this.items = [];
      this.activeIndex = -1;
      this.input.setAttribute('aria-expanded', 'false');
      this.input.removeAttribute('aria-activedescendant');
    }
  }

  const initSolrSuggest = (scope = document) => {
    scope.querySelectorAll('form[data-suggest]').forEach(form => {
      if (form.dataset.dSolrSuggestInit === 'true') return;
      if (!form.querySelector('[data-d-solr-suggest], .js-solr-q, input[name$="[q]"]')) return;
      form.dataset.dSolrSuggestInit = 'true';
      new DesiderioSolrSuggest(form);
    });
  };

  initSolrSuggest();
  document.body?.addEventListener('tx_solr_updated', () => initSolrSuggest());

  /* ------------------------------------------------------------------ */
  /*  11. Powermail multi-step state                                     */
  /* ------------------------------------------------------------------ */
  const powermailErrorSelector = [
    '.border-destructive',
    '.parsley-error',
    '.powermail_field_error',
    '[aria-invalid="true"]',
    '[aria-invalid="1"]',
    '[class*="powermail_field_error_container_"]:not(:empty)',
  ].join(',');
  const powermailControlSelector = [
    'input:not([type="hidden"]):not([type="submit"]):not([type="button"]):not([type="reset"])',
    'select',
    'textarea',
  ].join(',');
  const powermailAutocompleteByMarker = {
    name: 'name',
    email: 'email',
    phone: 'tel',
    company: 'organization',
  };
  const powermailErrorFallback = 'Please check this field.';

  const getPowermailFieldsets = form => Array.from(form.querySelectorAll('.powermail_fieldset'));

  const getVisiblePowermailIndex = fieldsets => {
    const visibleIndex = fieldsets.findIndex(fieldset => (
      fieldset.style.display !== 'none' && !fieldset.hidden
    ));

    return visibleIndex >= 0 ? visibleIndex : 0;
  };

  const showPowermailFieldset = (fieldsets, activeIndex) => {
    fieldsets.forEach((fieldset, index) => {
      fieldset.style.display = index === activeIndex ? '' : 'none';
    });
  };

  const setPowermailStepState = (button, active) => {
    button.classList.toggle('active', active);
    button.dataset.active = active ? 'true' : 'false';
    button.dataset.state = active ? 'active' : 'inactive';

    if (active) {
      button.setAttribute('aria-current', 'step');
    } else {
      button.removeAttribute('aria-current');
    }

    if (button.dataset.powermailStepLabel) {
      button.setAttribute(
        'aria-label',
        `${button.dataset.powermailStepLabel}${active ? (button.dataset.powermailStepCurrentSuffix || '') : ''}`,
      );
    }
  };

  const getPowermailFieldWrapper = control => control.closest('[class*="powermail_fieldwrap_"]');

  const getPowermailMarkerFromWrapper = wrapper => {
    if (!wrapper) return '';

    const markerClass = Array.from(wrapper.classList).find(className => (
      className.startsWith('powermail_fieldwrap_')
      && !className.startsWith('powermail_fieldwrap_type_')
    ));

    return markerClass ? markerClass.replace('powermail_fieldwrap_', '') : '';
  };

  const getPowermailMarkerFromControl = control => {
    const wrapperMarker = getPowermailMarkerFromWrapper(getPowermailFieldWrapper(control));
    if (wrapperMarker) return wrapperMarker;

    const idMatch = control.id?.match(/^powermail_field_(.+?)(?:_\d+)?$/);
    return idMatch ? idMatch[1] : '';
  };

  const getPowermailErrorContainer = (field, create = true) => {
    const { marker, wrapper } = field;
    if (!marker || !wrapper) return null;

    const byData = wrapper.querySelector(`[data-powermail-field-error="${marker}"]`);
    if (byData) return byData;

    const byId = wrapper.querySelector(`#powermail_field_error_${marker}`);
    if (byId) return byId;

    const byClass = wrapper.querySelector(`.powermail_field_error_container_${marker}`);
    if (byClass) return byClass;

    if (!create) return null;

    const container = document.createElement('div');
    container.id = `powermail_field_error_${marker}`;
    container.className = `powermail_field_error_container_${marker} text-xs text-destructive`;
    container.dataset.powermailFieldError = marker;
    container.setAttribute('aria-live', 'polite');
    wrapper.appendChild(container);

    return container;
  };

  const addDescribedBy = (control, id) => {
    if (!id) return;

    const describedBy = new Set((control.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
    describedBy.add(id);
    control.setAttribute('aria-describedby', Array.from(describedBy).join(' '));
  };

  const getPowermailFields = form => {
    const fieldsByMarker = new Map();

    form.querySelectorAll(powermailControlSelector).forEach(control => {
      if (control.disabled) return;

      const marker = getPowermailMarkerFromControl(control);
      const wrapper = getPowermailFieldWrapper(control);
      if (!marker || !wrapper) return;

      if (!fieldsByMarker.has(marker)) {
        fieldsByMarker.set(marker, {
          marker,
          wrapper,
          controls: [],
          fieldset: control.closest('.powermail_fieldset'),
        });
      }

      fieldsByMarker.get(marker).controls.push(control);
    });

    return Array.from(fieldsByMarker.values());
  };

  const applyPowermailAutocomplete = form => {
    getPowermailFields(form).forEach(field => {
      const autocomplete = powermailAutocompleteByMarker[field.marker];
      if (!autocomplete) return;

      field.controls.forEach(control => {
        if (control.matches('select, textarea, input')) {
          const currentValue = control.getAttribute('autocomplete');
          if (currentValue === null || currentValue === '') {
            control.setAttribute('autocomplete', autocomplete);
          }
        }
      });
    });
  };

  const getPowermailValidationScope = form => {
    if (form.dataset.powermailA11yValidateScope === 'all') return 'all';
    if (form.dataset.powermailA11yValidateScope) return form.dataset.powermailA11yValidateScope;

    return '';
  };

  const shouldValidatePowermailField = (form, field) => {
    const scope = getPowermailValidationScope(form);
    if (scope === 'all') return true;
    if (scope) return field.fieldset?.id === scope;
    if (!form.classList.contains('powermail_morestep')) return form.dataset.powermailA11yValidated === 'true';

    return false;
  };

  const getPowermailFieldMessage = field => {
    const errorContainer = getPowermailErrorContainer(field, false);
    const containerText = errorContainer?.textContent?.replace(/\s+/g, ' ').trim();
    if (containerText) return containerText;

    const control = field.controls.find(item => item.validationMessage) || field.controls[0];
    return control?.dataset?.powermailRequiredMessage
      || control?.dataset?.powermailErrorMessage
      || control?.validationMessage
      || powermailErrorFallback;
  };

  const getPowermailFieldLabel = field => {
    const label = field.wrapper.querySelector('[data-slot="field-label"], [data-slot="field-legend"], label, legend');
    const labelText = label?.textContent?.replace(/\*/g, '').replace(/\s+/g, ' ').trim();

    return labelText || field.controls[0]?.getAttribute('name') || field.marker;
  };

  const isPowermailFieldRequired = field => (
    field.controls.some(control => (
      control.required
      || control.dataset.powermailRequired === 'true'
      || control.getAttribute('aria-required') === 'true'
    ))
  );

  const isPowermailFieldEmpty = field => {
    const firstControl = field.controls[0];
    if (!firstControl) return false;

    if (firstControl.type === 'checkbox' || firstControl.type === 'radio') {
      return !field.controls.some(control => control.checked);
    }

    if (firstControl.type === 'file') {
      return !field.controls.some(control => control.files?.length > 0);
    }

    return !field.controls.some(control => String(control.value || '').trim() !== '');
  };

  const hasPowermailRenderedError = field => {
    const errorContainer = getPowermailErrorContainer(field, false);
    return field.wrapper.querySelector(powermailErrorSelector) !== null
      || Boolean(errorContainer?.textContent?.trim());
  };

  const isPowermailFieldInvalid = (form, field) => {
    if (hasPowermailRenderedError(field)) return true;
    if (!shouldValidatePowermailField(form, field)) return false;
    if (isPowermailFieldRequired(field) && isPowermailFieldEmpty(field)) return true;

    return field.controls.some(control => control.validity && !control.validity.valid);
  };

  const syncPowermailFieldAccessibility = form => {
    const invalidFields = [];

    getPowermailFields(form).forEach(field => {
      const errorContainer = getPowermailErrorContainer(field);
      const invalid = isPowermailFieldInvalid(form, field);

      if (errorContainer) {
        field.controls.forEach(control => addDescribedBy(control, errorContainer.id));
      }

      field.controls.forEach(control => {
        if (invalid) {
          control.setAttribute('aria-invalid', 'true');
        } else {
          control.removeAttribute('aria-invalid');
        }
      });

      if (invalid) {
        if (errorContainer && !errorContainer.textContent.trim()) {
          errorContainer.textContent = getPowermailFieldMessage(field);
        }

        invalidFields.push({
          field,
          label: getPowermailFieldLabel(field),
          message: getPowermailFieldMessage(field),
          control: field.controls.find(control => control.id) || field.controls[0],
        });
      } else if (errorContainer) {
        errorContainer.textContent = '';
      }
    });

    return invalidFields;
  };

  const syncPowermailDocumentTitle = (form, hasErrors) => {
    if (!document.title) return;
    if (!form.dataset.powermailOriginalTitle) {
      form.dataset.powermailOriginalTitle = document.title;
    }

    if (hasErrors) {
      if (!document.title.startsWith('Error: ')) {
        document.title = `Error: ${form.dataset.powermailOriginalTitle}`;
      }
      return;
    }

    if (document.title.startsWith('Error: ')) {
      document.title = form.dataset.powermailOriginalTitle;
    }
  };

  const renderPowermailErrorSummary = (form, invalidFields, focusSummary = false) => {
    const summary = form.querySelector('[data-powermail-error-summary]');
    const list = summary?.querySelector('[data-powermail-error-summary-list]');
    if (!summary || !list) {
      syncPowermailDocumentTitle(form, invalidFields.length > 0);
      return;
    }

    list.textContent = '';

    invalidFields.forEach(({ field, label, message, control }) => {
      const item = document.createElement('li');
      const link = document.createElement('a');
      const targetId = control.id || `powermail_field_${field.marker}`;

      link.href = `#${targetId}`;
      link.className = 'underline underline-offset-4';
      link.textContent = `${label}: ${message}`;
      link.addEventListener('click', event => {
        event.preventDefault();

        const fieldsets = getPowermailFieldsets(form);
        const index = fieldsets.indexOf(field.fieldset);
        if (index >= 0) {
          showPowermailFieldset(fieldsets, index);
          form.querySelectorAll('[data-powermail-morestep-current]').forEach(button => {
            setPowermailStepState(button, Number(button.dataset.powermailMorestepCurrent) === index);
          });
        }

        control.focus({ preventScroll: false });
      });

      item.appendChild(link);
      list.appendChild(item);
    });

    const hasErrors = invalidFields.length > 0;
    summary.hidden = !hasErrors;
    summary.classList.toggle('hidden', !hasErrors);
    syncPowermailDocumentTitle(form, hasErrors);

    if (hasErrors && focusSummary) {
      summary.focus({ preventScroll: true });
      summary.scrollIntoView({ block: 'nearest' });
    }
  };

  const syncPowermailA11y = (form, focusSummary = false) => {
    applyPowermailAutocomplete(form);
    const invalidFields = syncPowermailFieldAccessibility(form);
    renderPowermailErrorSummary(form, invalidFields, focusSummary);

    return invalidFields;
  };

  const getPowermailErrorIndex = fieldsets => fieldsets.findIndex(fieldset => (
    fieldset.querySelector(powermailErrorSelector) !== null
  ));

  const syncPowermailMultistep = form => {
    if (!form?.classList?.contains('powermail_morestep')) return;

    const fieldsets = getPowermailFieldsets(form);
    if (fieldsets.length === 0) return;

    const errorIndex = getPowermailErrorIndex(fieldsets);
    const activeIndex = errorIndex >= 0 ? errorIndex : getVisiblePowermailIndex(fieldsets);

    showPowermailFieldset(fieldsets, activeIndex);

    form.querySelectorAll('[data-powermail-morestep-current]').forEach(button => {
      setPowermailStepState(button, Number(button.dataset.powermailMorestepCurrent) === activeIndex);
    });
  };

  const initPowermailForms = (scope = document) => {
    scope.querySelectorAll('form').forEach(form => {
      if (!form.classList.contains('powermail_morestep') && !form.querySelector('.powermail_fieldset, [class*="powermail_fieldwrap_"]')) return;

      syncPowermailMultistep(form);
      syncPowermailA11y(form);
    });
  };

  initPowermailForms();
  window.setTimeout(() => initPowermailForms(), 0);
  window.setTimeout(() => initPowermailForms(), 100);
  window.addEventListener('load', () => initPowermailForms(), { once: true });

  document.addEventListener('click', event => {
    const trigger = event.target.closest('[data-powermail-morestep-show]');
    if (!trigger) return;

    const form = trigger.closest('form');
    if (form && trigger.hasAttribute('data-powermail-morestep-validate')) {
      const fieldsets = getPowermailFieldsets(form);
      const activeFieldset = fieldsets[getVisiblePowermailIndex(fieldsets)];
      form.dataset.powermailA11yValidated = 'true';
      form.dataset.powermailA11yValidateScope = activeFieldset?.id || '';
    }

    window.requestAnimationFrame(() => {
      if (!form?.classList?.contains('powermail_morestep')) return;

      // Powermail's own MoreStepForm handler has already validated the
      // step (data-powermail-morestep-validate) and switched the visible
      // fieldset by now. Re-validating here would hit the NEWLY shown
      // step's untouched fields and pin the form to it via the error
      // snap — only mirror the tab state for whatever step powermail
      // decided to show. The error snap stays load-time-only, for
      // server-rendered submits that come back with errors.
      const fieldsets = getPowermailFieldsets(form);
      const activeIndex = getVisiblePowermailIndex(fieldsets);

      form.querySelectorAll('[data-powermail-morestep-current]').forEach(button => {
        setPowermailStepState(button, Number(button.dataset.powermailMorestepCurrent) === activeIndex);
      });

      window.setTimeout(() => {
        syncPowermailA11y(form, trigger.hasAttribute('data-powermail-morestep-validate'));
      }, 0);
    });
  });

  document.addEventListener('invalid', event => {
    const form = event.target?.closest?.('form');
    if (!form) return;

    form.dataset.powermailA11yValidated = 'true';
    form.dataset.powermailA11yValidateScope = 'all';
    window.setTimeout(() => syncPowermailA11y(form, true), 0);
  }, true);

  document.addEventListener('submit', event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!form.querySelector('.powermail_fieldset, [class*="powermail_fieldwrap_"]')) return;

    form.dataset.powermailA11yValidated = 'true';
    form.dataset.powermailA11yValidateScope = 'all';
    window.setTimeout(() => syncPowermailA11y(form, true), 0);
  }, true);

  ['input', 'change'].forEach(eventName => {
    document.addEventListener(eventName, event => {
      const form = event.target?.closest?.('form');
      if (!form || !form.querySelector('.powermail_fieldset, [class*="powermail_fieldwrap_"]')) return;

      window.setTimeout(() => syncPowermailA11y(form), 0);
    }, true);
  });

  if (document.body) {
    const powermailObserver = new MutationObserver(records => {
      const addedPowermailMarkup = records.some(record => Array.from(record.addedNodes).some(node => (
        node.nodeType === Node.ELEMENT_NODE
        && (
          node.matches?.('form, .powermail_fieldset, [class*="powermail_fieldwrap_"]')
          || node.querySelector?.('form, .powermail_fieldset, [class*="powermail_fieldwrap_"]')
        )
      )));

      if (addedPowermailMarkup) {
        window.setTimeout(() => initPowermailForms(), 0);
      }
    });

    powermailObserver.observe(document.body, { childList: true, subtree: true });
  }

  /* ------------------------------------------------------------------ */
  /*  12. Feature carousel scroll indicator                              */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-feature-carousel]').forEach(root => {
    const track = root.querySelector('.feature-carousel__track');
    const thumb = root.querySelector('.feature-carousel__scroll-thumb');
    const scrollTrack = root.querySelector('.feature-carousel__scroll-track');
    if (!track || !thumb || !scrollTrack) return;

    let ticking = false;
    const sync = () => {
      ticking = false;
      const maxScroll = track.scrollWidth - track.clientWidth;
      const ratio = maxScroll > 0 ? track.scrollLeft / maxScroll : 0;
      const travel = scrollTrack.clientWidth - thumb.clientWidth;
      thumb.style.transform = `translateX(${ratio * travel}px)`;
      root.querySelector('.feature-carousel__scroll')?.classList.toggle(
        'feature-carousel__scroll--hidden',
        maxScroll <= 0,
      );
    };

    const requestSync = () => {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(sync);
    };

    sync();
    track.addEventListener('scroll', requestSync, { passive: true });
    window.addEventListener('resize', requestSync, { passive: true });
  });

  /* ------------------------------------------------------------------ */
  /*  13. Reading progress                                               */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-reading-progress]').forEach(root => {
    const scope = root.closest('.desiderio-editorial-template') || root.closest('article') || document;
    const selector = root.dataset.dReadingProgressTarget || '[itemprop="articleBody"]';
    const target = scope.querySelector(selector);
    const bar = root.querySelector('[role="progressbar"]');
    const indicator = bar?.querySelector('[data-slot="progress-indicator"]');
    if (!target || !bar || !indicator) return;

    // The generated Progress atom ships without an accessible name; give the
    // progressbar one so assistive tech announces what the value tracks.
    if (!bar.hasAttribute('aria-label')) {
      bar.setAttribute('aria-label', root.dataset.dReadingProgressLabel || 'Reading progress');
    }

    // Respect reduced-motion: drop the indicator's easing so the bar snaps to
    // the scroll position instead of animating toward it.
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const applyMotionPreference = () => {
      indicator.style.transitionDuration = reduceMotion.matches ? '0s' : '';
    };
    applyMotionPreference();
    reduceMotion.addEventListener?.('change', applyMotionPreference);

    let ticking = false;
    const measure = () => {
      ticking = false;
      const viewport = window.innerHeight || document.documentElement.clientHeight;
      const rect = target.getBoundingClientRect();
      const scrollable = rect.height - viewport;
      let ratio;
      if (scrollable <= 0) {
        // Body fits within the viewport: complete once its end is on screen.
        ratio = rect.bottom <= viewport ? 1 : 0;
      } else {
        ratio = -rect.top / scrollable;
      }
      const percent = Math.min(Math.max(ratio, 0), 1) * 100;
      indicator.style.transform = `translateX(-${100 - percent}%)`;
      bar.setAttribute('aria-valuenow', String(Math.round(percent)));
    };

    // Coalesce scroll/resize bursts into one layout read per frame.
    const requestMeasure = () => {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(measure);
    };

    measure();
    window.addEventListener('scroll', requestMeasure, { passive: true });
    window.addEventListener('resize', requestMeasure, { passive: true });
  });

  /* ------------------------------------------------------------------ */
  /*  14. Lightbox (one shared <dialog>, created on demand)              */
  /* ------------------------------------------------------------------ */
  let lightbox = null;
  let lightboxTrigger = null;

  const ensureLightbox = () => {
    if (lightbox) return lightbox;

    const dialog = document.createElement('dialog');
    dialog.className = 'd-lightbox-dialog';

    const img = document.createElement('img');
    img.className = 'd-lightbox-dialog__img';
    img.decoding = 'async';

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'd-lightbox-dialog__close';
    closeButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';

    const closeLabel = document.createElement('span');
    closeLabel.className = 'sr-only';
    closeButton.append(closeLabel);

    closeButton.addEventListener('click', () => dialog.close());
    // Backdrop click: the dialog element itself is only hit outside content.
    dialog.addEventListener('click', e => {
      if (e.target === dialog) dialog.close();
    });
    dialog.addEventListener('close', () => {
      img.removeAttribute('src');
      lightboxTrigger?.focus();
      lightboxTrigger = null;
    });

    dialog.append(img, closeButton);
    document.body.append(dialog);

    lightbox = { dialog, img, closeLabel };
    return lightbox;
  };

  document.addEventListener('click', e => {
    const trigger = e.target.closest('[data-d-lightbox]');
    if (!trigger) return;

    const innerImg = trigger.querySelector('img');
    const src = trigger.dataset.dLightboxSrc || innerImg?.currentSrc || innerImg?.src;
    if (!src) return;

    const { dialog, img, closeLabel } = ensureLightbox();
    img.src = src;
    img.alt = innerImg?.alt || '';
    closeLabel.textContent = trigger.dataset.dLightboxClose || 'Close';

    lightboxTrigger = trigger;
    dialog.showModal();
  });

  /* ------------------------------------------------------------------ */
  /*  15. Double-submit guard for POST forms                             */
  /* ------------------------------------------------------------------ */
  const submitButtonsOf = form =>
    form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]');

  document.addEventListener('submit', event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if ((form.method || '').toLowerCase() !== 'post') return;
    if (event.defaultPrevented) return;

    // Inside the TYPO3 visual editor canvas a native form POST is rejected
    // by its persistence middleware (it only accepts its own JSON saves)
    // and the exception page replaces the editing canvas — and a real
    // submission mid-edit would send mails and discard unsaved changes.
    if (document.querySelector('ve-editable-rich-text, ve-editable-text, img[data-veedit]')) {
      event.preventDefault();
      console.info('[desiderio] form submission blocked inside the visual editor');
      return;
    }

    if (form.dataset.dSubmitting === 'true') {
      event.preventDefault();
      return;
    }
    form.dataset.dSubmitting = 'true';

    // Disable after the browser has serialized the form data for this
    // submission — disabling synchronously would drop the trigger button's
    // name/value pair from the payload.
    window.setTimeout(() => {
      submitButtonsOf(form).forEach(button => {
        button.disabled = true;
        button.setAttribute('aria-disabled', 'true');
      });
    }, 0);
  });

  // bfcache restore (back button) revives the page with the guard still
  // armed — reset it so the form can be submitted again.
  window.addEventListener('pageshow', event => {
    if (!event.persisted) return;
    document.querySelectorAll('form[data-d-submitting="true"]').forEach(form => {
      delete form.dataset.dSubmitting;
      submitButtonsOf(form).forEach(button => {
        button.disabled = false;
        button.removeAttribute('aria-disabled');
      });
    });
  });
});
