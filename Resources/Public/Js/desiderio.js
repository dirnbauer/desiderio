/**
 * desiderio.js - Shared vanilla JS utilities for desiderio TYPO3 components.
 * Framework-free, auto-initialised on DOMContentLoaded.
 */
document.addEventListener('DOMContentLoaded', () => {
  /* ------------------------------------------------------------------ */
  /*  1. Accordion                                                       */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-d-accordion]').forEach(root => {
    if (root.hasAttribute('x-data')) return;

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
    if (root.hasAttribute('x-data')) return;

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
  const body = document.body;
  const media = window.matchMedia('(prefers-color-scheme: dark)');
  const storedTheme = () => localStorage.getItem('d-theme');
  const siteTheme = () => body?.dataset.theme || 'system';
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
  };

  applyTheme(storedTheme() || siteTheme());

  media.addEventListener?.('change', () => {
    if (!storedTheme() && siteTheme() === 'system') {
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
      const nextExpanded = !expanded;
      btn.setAttribute('aria-expanded', String(nextExpanded));
      target.classList.toggle('is-open', nextExpanded);
      target.classList.toggle('is-hidden', !nextExpanded);
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

      if (window.jQuery?.fn?.devbridgeAutocomplete && window.jQuery(this.input).data('autocomplete')) {
        window.jQuery(this.input).devbridgeAutocomplete('dispose');
      }

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
});
