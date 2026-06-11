/**
 * CKEditor 5 abbreviation plugin for the Desiderio RTE preset.
 *
 * Adds an <abbr title="…"> text attribute with a balloon form (abbreviation
 * text + full title), following the official CKEditor 5 abbreviation plugin
 * tutorial. Persisting works because TYPO3's RTE processing already allows
 * the abbr tag and the title attribute (EXT:rte_ckeditor Processing.yaml).
 */
import { Plugin } from '@ckeditor/ckeditor5-core';
import {
  ButtonView,
  ContextualBalloon,
  LabeledFieldView,
  View,
  clickOutsideHandler,
  createLabeledInputText,
  submitHandler,
} from '@ckeditor/ckeditor5-ui';
import { findAttributeRange } from '@ckeditor/ckeditor5-typing';
import { getRangeText } from './abbreviation-utils.js';

const ABBR_ICON = '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">'
  + '<text x="1" y="12" font-family="sans-serif" font-size="10" font-weight="bold" fill="currentColor">ab</text>'
  + '<path d="M1 16h12" stroke="currentColor" stroke-width="1.2" stroke-dasharray="2 1.6" fill="none"/>'
  + '</svg>';

class AbbreviationEditing extends Plugin {
  static get pluginName() {
    return 'AbbreviationEditing';
  }

  init() {
    const editor = this.editor;
    editor.model.schema.extend('$text', { allowAttributes: ['abbreviation'] });

    editor.conversion.for('downcast').attributeToElement({
      model: 'abbreviation',
      view: (modelAttributeValue, conversionApi) => {
        const { writer } = conversionApi;
        return writer.createAttributeElement('abbr', { title: modelAttributeValue });
      },
    });

    editor.conversion.for('upcast').elementToAttribute({
      view: { name: 'abbr', attributes: ['title'] },
      model: {
        key: 'abbreviation',
        value: (viewElement) => viewElement.getAttribute('title'),
      },
    });
  }
}

class AbbreviationFormView extends View {
  constructor(locale, labels) {
    super(locale);
    const t = locale.t;
    const bind = this.bindTemplate;

    this.set('headerText', labels.insertHeaderLabel);

    this.headerView = new View(locale);
    this.headerView.setTemplate({
      tag: 'h2',
      attributes: {
        class: ['ck', 'ck-abbr-form__header'],
        style: 'margin: 0; font-size: var(--ck-font-size-base); font-weight: 700;',
      },
      children: [{ text: bind.to('headerText') }],
    });

    this.abbrInputView = this._createInput(labels.abbreviationFieldLabel);
    this.titleInputView = this._createInput(labels.titleFieldLabel);

    this.saveButtonView = this._createButton(t('Save'), 'ck-button-save');
    this.saveButtonView.type = 'submit';
    this.cancelButtonView = this._createButton(t('Cancel'), 'ck-button-cancel');
    this.cancelButtonView.delegate('execute').to(this, 'cancel');

    this.childViews = this.createCollection([
      this.headerView,
      this.abbrInputView,
      this.titleInputView,
      this.saveButtonView,
      this.cancelButtonView,
    ]);

    this.setTemplate({
      tag: 'form',
      attributes: {
        class: ['ck', 'ck-abbr-form'],
        tabindex: '-1',
        style: 'padding: var(--ck-spacing-large); display: grid; gap: var(--ck-spacing-standard); min-width: 240px;',
      },
      children: this.childViews,
    });
  }

  render() {
    super.render();
    submitHandler({ view: this });
  }

  focus() {
    this.abbrInputView.focus();
  }

  _createInput(label) {
    const labeledInput = new LabeledFieldView(this.locale, createLabeledInputText);
    labeledInput.label = label;
    return labeledInput;
  }

  _createButton(label, className) {
    const button = new ButtonView(this.locale);
    button.set({ label, withText: true, tooltip: true, class: className });
    return button;
  }
}

class AbbreviationUI extends Plugin {
  static get pluginName() {
    return 'AbbreviationUI';
  }

  static get requires() {
    return [ContextualBalloon];
  }

  init() {
    const editor = this.editor;
    const t = editor.locale.t;

    this._labels = {
      buttonLabel: t('Abbreviation'),
      abbreviationFieldLabel: t('Abbreviation'),
      titleFieldLabel: t('Full term (title attribute)'),
      insertHeaderLabel: t('Insert abbreviation'),
      editHeaderLabel: t('Edit abbreviation'),
      ...(editor.config.get('abbreviation') || {}),
    };

    this._balloon = editor.plugins.get(ContextualBalloon);
    this._formView = this._createFormView();

    editor.ui.componentFactory.add('abbreviation', () => {
      const button = new ButtonView(editor.locale);
      button.set({
        label: this._labels.buttonLabel,
        icon: ABBR_ICON,
        tooltip: true,
      });
      this.listenTo(button, 'execute', () => this._showUI());
      return button;
    });
  }

  _createFormView() {
    const editor = this.editor;
    const formView = new AbbreviationFormView(editor.locale, this._labels);

    this.listenTo(formView, 'submit', () => {
      const abbrText = formView.abbrInputView.fieldView.element.value;
      const title = formView.titleInputView.fieldView.element.value;
      editor.model.change((writer) => {
        const selection = editor.model.document.selection;
        if (selection.isCollapsed) {
          if (abbrText !== '') {
            editor.model.insertContent(
              writer.createText(abbrText, { abbreviation: title }),
              selection.getFirstPosition(),
            );
          }
        } else {
          const ranges = editor.model.schema.getValidRanges(selection.getRanges(), 'abbreviation');
          for (const range of ranges) {
            if (title !== '') {
              writer.setAttribute('abbreviation', title, range);
            } else {
              writer.removeAttribute('abbreviation', range);
            }
          }
        }
      });
      this._hideUI();
    });

    this.listenTo(formView, 'cancel', () => this._hideUI());

    clickOutsideHandler({
      emitter: formView,
      activator: () => this._balloon.visibleView === formView,
      contextElements: [this._balloon.view.element],
      callback: () => this._hideUI(),
    });

    formView.keystrokes?.set?.('Esc', (data, cancel) => {
      this._hideUI();
      cancel();
    });

    return formView;
  }

  _showUI() {
    const editor = this.editor;
    const selection = editor.model.document.selection;

    if (this._balloon.hasView(this._formView)) {
      this._balloon.remove(this._formView);
    }
    this._balloon.add({ view: this._formView, position: this._getBalloonPositionData() });

    const abbrAttr = selection.getAttribute('abbreviation');
    if (selection.isCollapsed && abbrAttr) {
      // Caret inside an existing abbr: prefill both fields for editing.
      const position = selection.getFirstPosition();
      const abbrRange = findAttributeRange(position, 'abbreviation', abbrAttr, editor.model);
      this._formView.headerText = this._labels.editHeaderLabel;
      this._formView.abbrInputView.fieldView.value = getRangeText(abbrRange);
      this._formView.titleInputView.fieldView.value = abbrAttr;
      this._setAbbrInputEnabled(false);
    } else if (!selection.isCollapsed) {
      // Text selected: the abbreviation text is the selection itself.
      this._formView.headerText = abbrAttr ? this._labels.editHeaderLabel : this._labels.insertHeaderLabel;
      this._formView.abbrInputView.fieldView.value = getRangeText(selection.getFirstRange());
      this._formView.titleInputView.fieldView.value = abbrAttr ?? '';
      this._setAbbrInputEnabled(false);
    } else {
      this._formView.headerText = this._labels.insertHeaderLabel;
      this._formView.abbrInputView.fieldView.value = '';
      this._formView.titleInputView.fieldView.value = '';
      this._setAbbrInputEnabled(true);
    }

    this._formView.focus();
  }

  _setAbbrInputEnabled(enabled) {
    this._formView.abbrInputView.isEnabled = enabled;
    this._formView.abbrInputView.fieldView.isEnabled = enabled;
    if (this._formView.abbrInputView.fieldView.element) {
      this._formView.abbrInputView.fieldView.element.disabled = !enabled;
    }
  }

  _hideUI() {
    if (this._balloon.hasView(this._formView)) {
      this._balloon.remove(this._formView);
    }
    this._formView.element?.reset?.();
    this.editor.editing.view.focus();
  }

  _getBalloonPositionData() {
    const view = this.editor.editing.view;
    const viewDocument = view.document;
    return {
      target: () => view.domConverter.viewRangeToDom(viewDocument.selection.getFirstRange()),
    };
  }
}

export class Abbreviation extends Plugin {
  static get pluginName() {
    return 'Abbreviation';
  }

  static get requires() {
    return [AbbreviationEditing, AbbreviationUI];
  }
}
