import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';

// Exercise the shipped message selector without booting the unrelated page widgets.
const source = readFileSync(new URL('../../Resources/Public/Js/desiderio.js', import.meta.url), 'utf8');
const constants = source.slice(source.indexOf('  const powermailErrorFallback ='), source.indexOf('  const getPowermailFieldsets ='));
const helpers = source.slice(source.indexOf('  const uniquePowermailMessages ='), source.indexOf('  const getPowermailFieldMessages ='));
const getMessages = runInNewContext(`${constants}\n${helpers}\ngetPowermailControlMessages;`);
const form = { dataset: { powermailMessageRequired: 'Please complete this field.' } };

const control = (overrides = {}) => ({
  value: '',
  required: false,
  dataset: {},
  validity: { valid: true },
  validationMessage: '',
  getAttribute: () => null,
  ...overrides,
});
const messages = (field, targetForm = form) => Array.from(getMessages(targetForm, field));

test('an unchecked required checkbox uses the page language, not the browser language', () => {
  assert.deepEqual(messages(control({
    type: 'checkbox', value: 'accepted', required: true, checked: false,
    validity: { valid: false, valueMissing: true },
    validationMessage: 'Klicke dieses Kästchen an, wenn du fortfahren möchtest.',
  })), ['Please complete this field.']);
});

test('missing radio-group selection is localized even on a member without required', () => {
  for (const required of [true, false]) {
    assert.deepEqual(messages(control({
      type: 'radio', name: 'choice', value: 'yes', required, checked: false,
      validity: { valid: false, valueMissing: true },
      validationMessage: 'Wähle eine dieser Optionen aus.',
    })), ['Please complete this field.']);
  }
});

test('a checked checkbox and a satisfied radio group have no required error', () => {
  for (const type of ['checkbox', 'radio']) {
    assert.deepEqual(messages(control({
      type, value: 'yes', required: true, checked: true,
      validity: { valid: true, valueMissing: false },
    })), []);
  }
  assert.deepEqual(messages(control({
    type: 'radio', value: 'no', required: true, checked: false,
    validity: { valid: true, valueMissing: false },
  })), []);
});

test('ordinary empty required fields retain the translated page message', () => {
  const field = control({ required: true, validity: { valid: false, valueMissing: true } });
  assert.deepEqual(messages(field), ['Please complete this field.']);
  assert.deepEqual(messages(field, { dataset: { powermailMessageRequired: 'Bitte füllen Sie dieses Feld aus.' } }), ['Bitte füllen Sie dieses Feld aus.']);
});

test('the required metadata fallback still works without native validity', () => {
  assert.deepEqual(messages(control({ dataset: { powermailRequired: 'true' }, validity: undefined })), ['Please complete this field.']);
});

test('an optional empty field has no error', () => {
  assert.deepEqual(messages(control()), []);
});

test('numeric bad input is not mislabeled as an empty required field', () => {
  assert.deepEqual(messages(control({ required: true, validity: { valid: false, badInput: true, valueMissing: false } })), ['Enter a number.']);
});

test('a custom validation error on an optional value is preserved', () => {
  assert.deepEqual(messages(control({
    value: 'unavailable', validity: { valid: false, customError: true, valueMissing: false },
    validationMessage: 'This code is unavailable.',
  })), ['This code is unavailable.']);
});
