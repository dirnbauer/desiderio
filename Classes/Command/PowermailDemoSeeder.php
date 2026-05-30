<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Seeds optional powermail demo records for the existing Desiderio styleguide command.
 *
 * This class intentionally avoids hard references to powermail PHP classes so
 * Desiderio remains installable without in2code/powermail.
 *
 * @phpstan-type DemoOption array{0: string, 1: string, 2: string}
 * @phpstan-type DemoField array{type: string, marker: string, titleEn: string, titleDe: string, mandatory: bool, validation: int, sender_email: bool, sender_name: bool, placeholderEn: string, placeholderDe: string, prefill: string, options: list<DemoOption>, textEn: string, textDe: string}
 * @phpstan-type DemoPage array{titleEn: string, titleDe: string, fields: list<DemoField>}
 * @phpstan-type DemoForm array{slug: string, titleEn: string, titleDe: string, pageTitleEn: string, pageTitleDe: string, introEn: string, introDe: string, thankTitleEn: string, thankTitleDe: string, thankBodyEn: string, thankBodyDe: string, moresteps: bool, pages: list<DemoPage>}
 */
final class PowermailDemoSeeder
{
    private const REQUIRED_TABLES = [
        'pages',
        'tt_content',
        'tx_powermail_domain_model_form',
        'tx_powermail_domain_model_page',
        'tx_powermail_domain_model_field',
    ];

    /** @var array<string, array<string, true>> */
    private array $tableColumnsCache = [];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function canSeed(): bool
    {
        foreach (self::REQUIRED_TABLES as $table) {
            if (!$this->tableExists($table)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{pages: int, forms: int, skipped: bool}
     */
    public function seed(
        int $parentPid,
        int $storagePid,
        int $germanLanguageUid,
        int $now,
        SymfonyStyle $io,
    ): array {
        if (!$this->canSeed()) {
            $io->note('Skipping Desiderio powermail demo pages because powermail tables are not available.');
            return ['pages' => 0, 'forms' => 0, 'skipped' => true];
        }

        $forms = $this->getDemoForms();
        $pageColumns = $this->getColumnNames('pages');
        $contentColumns = $this->getColumnNames('tt_content');

        $rootUid = $this->upsertPage(
            $parentPid,
            'Desiderio Powermail Lab',
            '/desiderio-powermail-lab',
            8192,
            $now,
            $pageColumns
        );
        $rootTranslationUid = $this->upsertPage(
            $parentPid,
            'Desiderio Powermail Labor',
            '/desiderio-powermail-labor',
            8193,
            $now,
            $pageColumns,
            $germanLanguageUid,
            $rootUid
        );
        $storagePid = $storagePid > 0 ? $storagePid : $rootUid;

        $this->softDeleteOwnedPowermailRecords($now);
        $ownedPageUids = $this->findOwnedChildPageUids($rootUid, $pageColumns);
        if ($ownedPageUids !== []) {
            $this->softDeleteContentOnPages($ownedPageUids, $now);
        }
        $this->softDeleteContentOnPages([$rootUid, $rootTranslationUid], $now);

        $this->insertTextContent(
            $rootUid,
            'Powermail form patterns',
            'Five seeded powermail pages demonstrate a progression from a compact newsletter signup to a complex multi-step project intake. Each form has its own redirect-based thank-you page and German translations.',
            256,
            $now,
            $contentColumns
        );
        $this->insertTextContent(
            $rootTranslationUid,
            'Powermail Formularmuster',
            'Fuenf automatisch angelegte Powermail-Seiten zeigen den Weg vom kompakten Newsletter-Formular bis zur komplexen mehrstufigen Projektanfrage. Jedes Formular hat eine eigene Danke-Seite und deutsche Uebersetzungen.',
            256,
            $now,
            $contentColumns,
            $germanLanguageUid
        );

        $createdPages = 2;
        $createdForms = 0;
        foreach ($forms as $index => $form) {
            $sorting = ($index + 1) * 512;
            $thankUid = $this->upsertPage(
                $rootUid,
                $form['thankTitleEn'],
                '/desiderio-powermail/' . $form['slug'] . '-thank-you',
                $sorting + 128,
                $now,
                $pageColumns
            );
            $thankTranslationUid = $this->upsertPage(
                $rootUid,
                $form['thankTitleDe'],
                '/desiderio-powermail/' . $form['slug'] . '-danke',
                $sorting + 129,
                $now,
                $pageColumns,
                $germanLanguageUid,
                $thankUid
            );
            $createdPages += 2;

            $formUids = $this->insertPowermailForm($storagePid, $form, $germanLanguageUid, $now);
            $createdForms++;

            $formPageUid = $this->upsertPage(
                $rootUid,
                $form['pageTitleEn'],
                '/desiderio-powermail/' . $form['slug'],
                $sorting,
                $now,
                $pageColumns
            );
            $formPageTranslationUid = $this->upsertPage(
                $rootUid,
                $form['pageTitleDe'],
                '/desiderio-powermail/' . $form['slug'] . '-de',
                $sorting + 1,
                $now,
                $pageColumns,
                $germanLanguageUid,
                $formPageUid
            );
            $createdPages += 2;

            $introUid = $this->insertTextContent(
                $formPageUid,
                $form['pageTitleEn'],
                $form['introEn'],
                256,
                $now,
                $contentColumns
            );
            $this->insertTextContent(
                $formPageTranslationUid,
                $form['pageTitleDe'],
                $form['introDe'],
                256,
                $now,
                $contentColumns,
                $germanLanguageUid,
                $introUid
            );

            $pluginUid = $this->insertPowermailPluginContent(
                $formPageUid,
                $form['pageTitleEn'],
                $formUids['default'],
                $storagePid,
                $thankUid,
                (bool)$form['moresteps'],
                512,
                $now,
                $contentColumns
            );
            $this->insertPowermailPluginContent(
                $formPageTranslationUid,
                $form['pageTitleDe'],
                $formUids['german'],
                $storagePid,
                $thankTranslationUid,
                (bool)$form['moresteps'],
                512,
                $now,
                $contentColumns,
                $germanLanguageUid,
                $pluginUid
            );

            $thankContentUid = $this->insertTextContent(
                $thankUid,
                $form['thankTitleEn'],
                $form['thankBodyEn'],
                256,
                $now,
                $contentColumns
            );
            $this->insertTextContent(
                $thankTranslationUid,
                $form['thankTitleDe'],
                $form['thankBodyDe'],
                256,
                $now,
                $contentColumns,
                $germanLanguageUid,
                $thankContentUid
            );
        }

        return ['pages' => $createdPages, 'forms' => $createdForms, 'skipped' => false];
    }

    /**
     * @return list<DemoForm>
     */
    public function getDemoForms(): array
    {
        return [
            [
                'slug' => 'newsletter',
                'titleEn' => 'Newsletter Signup',
                'titleDe' => 'Newsletter Anmeldung',
                'pageTitleEn' => 'Powermail 01: Newsletter signup',
                'pageTitleDe' => 'Powermail 01: Newsletter Anmeldung',
                'introEn' => 'A minimal single-step form with email validation and consent.',
                'introDe' => 'Ein minimales einstufiges Formular mit E-Mail-Validierung und Zustimmung.',
                'thankTitleEn' => 'Newsletter signup received',
                'thankTitleDe' => 'Newsletter Anmeldung erhalten',
                'thankBodyEn' => 'Thank you for signing up. The demo form redirected to this dedicated thank-you page.',
                'thankBodyDe' => 'Danke fuer die Anmeldung. Das Demoformular hat auf diese eigene Danke-Seite weitergeleitet.',
                'moresteps' => false,
                'pages' => [
                    [
                        'titleEn' => 'Signup',
                        'titleDe' => 'Anmeldung',
                        'fields' => [
                            $this->field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true, 'placeholderEn' => 'you@example.com', 'placeholderDe' => 'sie@example.com']),
                            $this->field('check', 'consent', 'Consent', 'Zustimmung', ['mandatory' => true, 'options' => [['I agree to receive the newsletter.', 'Ich moechte den Newsletter erhalten.', 'yes']]]),
                            $this->field('submit', 'submit', 'Subscribe', 'Abonnieren'),
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'contact',
                'titleEn' => 'Contact Inquiry',
                'titleDe' => 'Kontaktanfrage',
                'pageTitleEn' => 'Powermail 02: Contact inquiry',
                'pageTitleDe' => 'Powermail 02: Kontaktanfrage',
                'introEn' => 'A standard contact form with sender name, email validation, subject choice, and message.',
                'introDe' => 'Ein klassisches Kontaktformular mit Absendername, E-Mail-Validierung, Themenauswahl und Nachricht.',
                'thankTitleEn' => 'Contact inquiry received',
                'thankTitleDe' => 'Kontaktanfrage erhalten',
                'thankBodyEn' => 'Thanks. The contact inquiry demo has been submitted.',
                'thankBodyDe' => 'Danke. Die Demo-Kontaktanfrage wurde uebermittelt.',
                'moresteps' => false,
                'pages' => [
                    [
                        'titleEn' => 'Contact',
                        'titleDe' => 'Kontakt',
                        'fields' => [
                            $this->field('input', 'name', 'Name', 'Name', ['mandatory' => true, 'sender_name' => true]),
                            $this->field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true]),
                            $this->field('select', 'topic', 'Topic', 'Thema', ['mandatory' => true, 'options' => [['General question', 'Allgemeine Frage', 'general'], ['Support', 'Support', 'support'], ['Partnership', 'Partnerschaft', 'partnership']]]),
                            $this->field('textarea', 'message', 'Message', 'Nachricht', ['mandatory' => true, 'placeholderEn' => 'How can we help?', 'placeholderDe' => 'Wie koennen wir helfen?']),
                            $this->field('submit', 'submit', 'Send inquiry', 'Anfrage senden'),
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'consultation',
                'titleEn' => 'Consultation Request',
                'titleDe' => 'Beratungsanfrage',
                'pageTitleEn' => 'Powermail 03: Two-step consultation',
                'pageTitleDe' => 'Powermail 03: Zweistufige Beratung',
                'introEn' => 'A two-step form that separates contact data from appointment preferences.',
                'introDe' => 'Ein zweistufiges Formular, das Kontaktdaten und Terminwuensche trennt.',
                'thankTitleEn' => 'Consultation request received',
                'thankTitleDe' => 'Beratungsanfrage erhalten',
                'thankBodyEn' => 'Your consultation request has arrived in the demo inbox.',
                'thankBodyDe' => 'Ihre Beratungsanfrage ist im Demo-Postfach angekommen.',
                'moresteps' => true,
                'pages' => [
                    [
                        'titleEn' => 'Contact details',
                        'titleDe' => 'Kontaktdaten',
                        'fields' => [
                            $this->field('input', 'firstname', 'First name', 'Vorname', ['mandatory' => true, 'sender_name' => true]),
                            $this->field('input', 'lastname', 'Last name', 'Nachname', ['mandatory' => true]),
                            $this->field('input', 'email', 'Work email', 'Geschaeftliche E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true]),
                            $this->field('input', 'company', 'Company', 'Unternehmen'),
                        ],
                    ],
                    [
                        'titleEn' => 'Appointment',
                        'titleDe' => 'Termin',
                        'fields' => [
                            $this->field('date', 'preferred_date', 'Preferred date', 'Wunschtermin', ['mandatory' => true]),
                            $this->field('radio', 'format', 'Format', 'Format', ['mandatory' => true, 'options' => [['Video call', 'Videocall', 'video'], ['Phone call', 'Telefonat', 'phone'], ['On site', 'Vor Ort', 'onsite']]]),
                            $this->field('textarea', 'briefing', 'Briefing', 'Briefing', ['placeholderEn' => 'What should we prepare?', 'placeholderDe' => 'Was sollen wir vorbereiten?']),
                            $this->field('submit', 'submit', 'Request consultation', 'Beratung anfragen'),
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'support-upload',
                'titleEn' => 'Support Upload',
                'titleDe' => 'Support Upload',
                'pageTitleEn' => 'Powermail 04: Support upload',
                'pageTitleDe' => 'Powermail 04: Support Upload',
                'introEn' => 'A support request with priority, affected area, description, and optional file upload.',
                'introDe' => 'Eine Supportanfrage mit Prioritaet, betroffenem Bereich, Beschreibung und optionalem Upload.',
                'thankTitleEn' => 'Support request received',
                'thankTitleDe' => 'Supportanfrage erhalten',
                'thankBodyEn' => 'The support upload demo has saved the request and redirected here.',
                'thankBodyDe' => 'Die Support-Upload-Demo hat die Anfrage gespeichert und hierher weitergeleitet.',
                'moresteps' => true,
                'pages' => [
                    [
                        'titleEn' => 'Issue',
                        'titleDe' => 'Problem',
                        'fields' => [
                            $this->field('input', 'name', 'Name', 'Name', ['mandatory' => true, 'sender_name' => true]),
                            $this->field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true]),
                            $this->field('select', 'priority', 'Priority', 'Prioritaet', ['mandatory' => true, 'options' => [['Low', 'Niedrig', 'low'], ['Normal', 'Normal', 'normal'], ['Urgent', 'Dringend', 'urgent']]]),
                            $this->field('check', 'affected_area', 'Affected area', 'Betroffener Bereich', ['options' => [['Frontend', 'Frontend', 'frontend'], ['Backend', 'Backend', 'backend'], ['Email delivery', 'E-Mail Versand', 'mail']]]),
                        ],
                    ],
                    [
                        'titleEn' => 'Details',
                        'titleDe' => 'Details',
                        'fields' => [
                            $this->field('textarea', 'description', 'Description', 'Beschreibung', ['mandatory' => true]),
                            $this->field('file', 'attachment', 'Attachment', 'Anhang'),
                            $this->field('submit', 'submit', 'Send support request', 'Supportanfrage senden'),
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'project-intake',
                'titleEn' => 'Project Intake',
                'titleDe' => 'Projektanfrage',
                'pageTitleEn' => 'Powermail 05: Complex project intake',
                'pageTitleDe' => 'Powermail 05: Komplexe Projektanfrage',
                'introEn' => 'A multi-step project intake form using several field types and a review-oriented structure.',
                'introDe' => 'Ein mehrstufiges Projektanfrageformular mit mehreren Feldtypen und pruefbarer Struktur.',
                'thankTitleEn' => 'Project intake received',
                'thankTitleDe' => 'Projektanfrage erhalten',
                'thankBodyEn' => 'The complex intake demo has been submitted. This page can hold conversion tracking, next steps, and editorial thank-you content.',
                'thankBodyDe' => 'Die komplexe Intake-Demo wurde uebermittelt. Diese Seite kann Conversion-Tracking, naechste Schritte und redaktionelle Danke-Inhalte aufnehmen.',
                'moresteps' => true,
                'pages' => [
                    [
                        'titleEn' => 'Company',
                        'titleDe' => 'Unternehmen',
                        'fields' => [
                            $this->field('input', 'company', 'Company', 'Unternehmen', ['mandatory' => true]),
                            $this->field('input', 'website', 'Website', 'Website', ['validation' => 2]),
                            $this->field('select', 'company_size', 'Company size', 'Unternehmensgroesse', ['options' => [['1-10', '1-10', '1-10'], ['11-50', '11-50', '11-50'], ['51-250', '51-250', '51-250'], ['250+', '250+', '250+']]]),
                            $this->field('input', 'contact', 'Contact person', 'Kontaktperson', ['mandatory' => true, 'sender_name' => true]),
                            $this->field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true]),
                        ],
                    ],
                    [
                        'titleEn' => 'Scope',
                        'titleDe' => 'Umfang',
                        'fields' => [
                            $this->field('check', 'services', 'Services', 'Leistungen', ['mandatory' => true, 'options' => [['TYPO3 relaunch', 'TYPO3 Relaunch', 'typo3'], ['Design system', 'Designsystem', 'design-system'], ['Search integration', 'Suchintegration', 'search'], ['Forms and automation', 'Formulare und Automatisierung', 'forms']]]),
                            $this->field('radio', 'timeline', 'Timeline', 'Zeitplan', ['mandatory' => true, 'options' => [['ASAP', 'So bald wie moeglich', 'asap'], ['This quarter', 'Dieses Quartal', 'quarter'], ['Planning ahead', 'Vorausschauende Planung', 'later']]]),
                            $this->field('select', 'budget', 'Budget range', 'Budgetrahmen', ['options' => [['Under 10k', 'Unter 10k', 'under-10k'], ['10k-25k', '10k-25k', '10-25k'], ['25k-75k', '25k-75k', '25-75k'], ['75k+', '75k+', '75k-plus']]]),
                        ],
                    ],
                    [
                        'titleEn' => 'Briefing',
                        'titleDe' => 'Briefing',
                        'fields' => [
                            $this->field('html', 'briefing_note', 'Briefing note', 'Briefing Hinweis', ['textEn' => '<p>Use this final step for context, files, and consent before submitting the request.</p>', 'textDe' => '<p>Nutzen Sie diesen letzten Schritt fuer Kontext, Dateien und Zustimmung vor dem Absenden.</p>']),
                            $this->field('textarea', 'goals', 'Goals and constraints', 'Ziele und Rahmenbedingungen', ['mandatory' => true]),
                            $this->field('file', 'briefing_file', 'Briefing file', 'Briefing-Datei'),
                            $this->field('check', 'privacy', 'Privacy', 'Datenschutz', ['mandatory' => true, 'options' => [['I agree that this demo request may be processed.', 'Ich stimme der Verarbeitung dieser Demoanfrage zu.', 'accepted']]]),
                            $this->field('hidden', 'source', 'Source', 'Quelle', ['prefill' => 'desiderio-powermail-demo']),
                            $this->field('submit', 'submit', 'Submit project intake', 'Projektanfrage senden'),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array{mandatory?: bool, validation?: int, sender_email?: bool, sender_name?: bool, placeholderEn?: string, placeholderDe?: string, prefill?: string, options?: list<DemoOption>, textEn?: string, textDe?: string} $options
     * @return DemoField
     */
    private function field(string $type, string $marker, string $titleEn, string $titleDe, array $options = []): array
    {
        return $options + [
            'type' => $type,
            'marker' => $marker,
            'titleEn' => $titleEn,
            'titleDe' => $titleDe,
            'mandatory' => false,
            'validation' => 0,
            'sender_email' => false,
            'sender_name' => false,
            'placeholderEn' => '',
            'placeholderDe' => '',
            'prefill' => '',
            'options' => [],
            'textEn' => '',
            'textDe' => '',
        ];
    }

    /**
     * @param DemoForm $form
     * @return array{default: int, german: int}
     */
    private function insertPowermailForm(int $storagePid, array $form, int $germanLanguageUid, int $now): array
    {
        $formColumns = $this->getColumnNames('tx_powermail_domain_model_form');
        $pageColumns = $this->getColumnNames('tx_powermail_domain_model_page');
        $fieldColumns = $this->getColumnNames('tx_powermail_domain_model_field');

        $formUid = $this->insertRow('tx_powermail_domain_model_form', [
            'pid' => $storagePid,
            'title' => $form['titleEn'],
            'css' => 'desiderio-powermail-demo desiderio-powermail-' . $form['slug'],
            'pages' => count($form['pages']),
            'autocomplete_token' => 'on',
            'sys_language_uid' => 0,
            'crdate' => $now,
            'tstamp' => $now,
        ], $formColumns);

        $formTranslationUid = $this->insertRow('tx_powermail_domain_model_form', [
            'pid' => $storagePid,
            'title' => $form['titleDe'],
            'css' => 'desiderio-powermail-demo desiderio-powermail-' . $form['slug'],
            'pages' => count($form['pages']),
            'autocomplete_token' => 'on',
            'sys_language_uid' => $germanLanguageUid,
            'l10n_parent' => $formUid,
            'l10n_source' => $formUid,
            'crdate' => $now,
            'tstamp' => $now,
        ], $formColumns);

        foreach ($form['pages'] as $pageIndex => $page) {
            $pageUid = $this->insertPowermailPage($storagePid, $formUid, $page, false, $pageIndex, $now, $pageColumns);
            $translatedPageUid = $this->insertPowermailPage($storagePid, $formTranslationUid, $page, true, $pageIndex, $now, $pageColumns, $germanLanguageUid, $pageUid);
            foreach ($page['fields'] as $fieldIndex => $field) {
                $fieldUid = $this->insertPowermailField($storagePid, $pageUid, $field, false, $fieldIndex, $now, $fieldColumns);
                $this->insertPowermailField($storagePid, $translatedPageUid, $field, true, $fieldIndex, $now, $fieldColumns, $germanLanguageUid, $fieldUid);
            }
        }

        return ['default' => $formUid, 'german' => $formTranslationUid];
    }

    /**
     * @param DemoPage $page
     * @param array<string, true> $columns
     */
    private function insertPowermailPage(
        int $storagePid,
        int $formUid,
        array $page,
        bool $translated,
        int $index,
        int $now,
        array $columns,
        int $languageUid = 0,
        int $l10nParent = 0,
    ): int {
        return $this->insertRow('tx_powermail_domain_model_page', [
            'pid' => $storagePid,
            'form' => $formUid,
            'title' => $translated ? $page['titleDe'] : $page['titleEn'],
            'css' => '',
            'fields' => count($page['fields']),
            'sorting' => ($index + 1) * 256,
            'sys_language_uid' => $languageUid,
            'l10n_parent' => $l10nParent,
            'l10n_source' => $l10nParent,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns);
    }

    /**
     * @param DemoField $field
     * @param array<string, true> $columns
     */
    private function insertPowermailField(
        int $storagePid,
        int $pageUid,
        array $field,
        bool $translated,
        int $index,
        int $now,
        array $columns,
        int $languageUid = 0,
        int $l10nParent = 0,
    ): int {
        return $this->insertRow('tx_powermail_domain_model_field', [
            'pid' => $storagePid,
            'page' => $pageUid,
            'title' => $translated ? $field['titleDe'] : $field['titleEn'],
            'type' => $field['type'],
            'marker' => $field['marker'],
            'own_marker_select' => 1,
            'mandatory' => (int)(bool)$field['mandatory'],
            'validation' => $field['validation'],
            'sender_email' => (int)(bool)$field['sender_email'],
            'sender_name' => (int)(bool)$field['sender_name'],
            'placeholder' => $translated ? $field['placeholderDe'] : $field['placeholderEn'],
            'prefill_value' => $field['prefill'],
            'settings' => $this->buildOptionSettings($field['options'], $translated),
            'text' => $translated ? $field['textDe'] : $field['textEn'],
            'css' => '',
            'sorting' => ($index + 1) * 256,
            'sys_language_uid' => $languageUid,
            'l10n_parent' => $l10nParent,
            'l10n_source' => $l10nParent,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns);
    }

    /**
     * @param list<array{0: string, 1: string, 2: string}> $options
     */
    private function buildOptionSettings(array $options, bool $translated): string
    {
        $lines = [];
        foreach ($options as $option) {
            $label = $translated ? $option[1] : $option[0];
            $lines[] = $label . '|' . $option[2];
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, true> $columns
     */
    private function insertTextContent(
        int $pid,
        string $header,
        string $body,
        int $sorting,
        int $now,
        array $columns,
        int $languageUid = 0,
        int $translationParentUid = 0,
    ): int {
        return $this->insertRow('tt_content', [
            'pid' => $pid,
            'CType' => 'text',
            'header' => $header,
            'bodytext' => $body,
            'colPos' => 0,
            'sorting' => $sorting,
            'hidden' => 0,
            'sys_language_uid' => $languageUid,
            'l18n_parent' => $translationParentUid,
            'l10n_parent' => $translationParentUid,
            'l10n_source' => $translationParentUid,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns);
    }

    /**
     * @param array<string, true> $columns
     */
    private function insertPowermailPluginContent(
        int $pid,
        string $header,
        int $formUid,
        int $storagePid,
        int $thankPid,
        bool $moresteps,
        int $sorting,
        int $now,
        array $columns,
        int $languageUid = 0,
        int $translationParentUid = 0,
    ): int {
        return $this->insertRow('tt_content', [
            'pid' => $pid,
            'CType' => 'powermail_pi1',
            'header' => $header,
            'pi_flexform' => $this->buildPowermailFlexform($formUid, $storagePid, $thankPid, $moresteps),
            'colPos' => 0,
            'sorting' => $sorting,
            'hidden' => 0,
            'sys_language_uid' => $languageUid,
            'l18n_parent' => $translationParentUid,
            'l10n_parent' => $translationParentUid,
            'l10n_source' => $translationParentUid,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns);
    }

    private function buildPowermailFlexform(int $formUid, int $storagePid, int $thankPid, bool $moresteps): string
    {
        $values = [
            'main' => [
                'settings.flexform.main.form' => (string)$formUid,
                'settings.flexform.main.confirmation' => '0',
                'settings.flexform.main.optin' => '0',
                'settings.flexform.main.moresteps' => $moresteps ? '1' : '0',
                'settings.flexform.main.pid' => (string)$storagePid,
            ],
            'receiver' => [
                'settings.flexform.receiver.name' => 'Desiderio Demo',
                'settings.flexform.receiver.email' => 'hello@example.com',
                'settings.flexform.receiver.subject' => 'Powermail demo submission',
                'settings.flexform.receiver.body' => '{powermail_all}',
            ],
            'sender' => [
                'settings.flexform.sender.name' => 'Desiderio Demo',
                'settings.flexform.sender.email' => 'no-reply@example.com',
                'settings.flexform.sender.subject' => 'Thank you for your request',
                'settings.flexform.sender.body' => 'Thank you. We received your demo submission.',
            ],
            'thx' => [
                'settings.flexform.thx.body' => 'Thank you for your submission.',
                'settings.flexform.thx.redirect' => (string)$thankPid,
            ],
        ];

        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\n<T3FlexForms>\n    <data>\n";
        foreach ($values as $sheet => $fields) {
            $xml .= '        <sheet index="' . $sheet . "\">\n            <language index=\"lDEF\">\n";
            foreach ($fields as $field => $value) {
                $xml .= '                <field index="' . htmlspecialchars($field, ENT_XML1) . '"><value index="vDEF">' . htmlspecialchars($value, ENT_XML1) . "</value></field>\n";
            }
            $xml .= "            </language>\n        </sheet>\n";
        }
        $xml .= "    </data>\n</T3FlexForms>";

        return $xml;
    }

    /**
     * @param array<string, true> $columns
     */
    private function upsertPage(
        int $pid,
        string $title,
        string $slug,
        int $sorting,
        int $now,
        array $columns,
        int $languageUid = 0,
        int $l10nParent = 0,
    ): int {
        $existingUid = $this->findExistingPageUid($pid, $slug, $languageUid, $l10nParent, $columns);
        $row = $this->filterRow([
            'pid' => $pid,
            'title' => $title,
            'doktype' => 1,
            'slug' => $slug,
            'hidden' => 0,
            'sorting' => $sorting,
            'sys_language_uid' => $languageUid,
            'l10n_parent' => $l10nParent,
            'l10n_source' => $l10nParent,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns);

        $connection = $this->connectionPool->getConnectionForTable('pages');
        if ($existingUid !== null) {
            unset($row['pid'], $row['crdate']);
            $connection->update('pages', $row, ['uid' => $existingUid]);
            return $existingUid;
        }

        $connection->insert('pages', $row);
        return $this->normalizeInteger($connection->lastInsertId());
    }

    /**
     * @param array<string, true> $columns
     */
    private function findExistingPageUid(int $pid, string $slug, int $languageUid, int $l10nParent, array $columns): ?int
    {
        $where = ['pid = :pid', 'deleted = 0', 'slug = :slug'];
        $parameters = ['pid' => $pid, 'slug' => $slug];
        $types = ['pid' => ParameterType::INTEGER, 'slug' => ParameterType::STRING];

        if (isset($columns['sys_language_uid'])) {
            $where[] = 'sys_language_uid = :languageUid';
            $parameters['languageUid'] = $languageUid;
            $types['languageUid'] = ParameterType::INTEGER;
        }
        if ($l10nParent > 0 && isset($columns['l10n_parent'])) {
            $where[] = 'l10n_parent = :l10nParent';
            $parameters['l10nParent'] = $l10nParent;
            $types['l10nParent'] = ParameterType::INTEGER;
        }

        $uid = $this->connectionPool
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT uid FROM pages WHERE ' . implode(' AND ', $where) . ' ORDER BY uid DESC LIMIT 1', $parameters, $types)
            ->fetchOne();

        return $uid === false ? null : $this->normalizeInteger($uid);
    }

    /**
     * @param array<string, true> $columns
     * @return list<int>
     */
    private function findOwnedChildPageUids(int $rootUid, array $columns): array
    {
        $where = ['pid = :rootUid', 'deleted = 0', 'slug LIKE :slug'];
        $parameters = ['rootUid' => $rootUid, 'slug' => '/desiderio-powermail/%'];
        $types = ['rootUid' => ParameterType::INTEGER, 'slug' => ParameterType::STRING];
        if (isset($columns['sys_language_uid'])) {
            $where[] = 'sys_language_uid IN (0, 1)';
        }

        $uids = $this->connectionPool
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT uid FROM pages WHERE ' . implode(' AND ', $where), $parameters, $types)
            ->fetchFirstColumn();

        return $this->normalizeIntegerList($uids);
    }

    private function softDeleteOwnedPowermailRecords(int $now): void
    {
        $formUids = $this->connectionPool
            ->getConnectionForTable('tx_powermail_domain_model_form')
            ->executeQuery(
                'SELECT uid FROM tx_powermail_domain_model_form WHERE deleted = 0 AND css LIKE :css',
                ['css' => 'desiderio-powermail-demo%'],
                ['css' => ParameterType::STRING]
            )
            ->fetchFirstColumn();
        $formUids = $this->normalizeIntegerList($formUids);
        if ($formUids === []) {
            return;
        }

        $pageUids = $this->connectionPool
            ->getConnectionForTable('tx_powermail_domain_model_page')
            ->executeQuery(
                'SELECT uid FROM tx_powermail_domain_model_page WHERE form IN (:forms)',
                ['forms' => $formUids],
                ['forms' => ArrayParameterType::INTEGER]
            )
            ->fetchFirstColumn();
        $pageUids = $this->normalizeIntegerList($pageUids);

        if ($pageUids !== []) {
            $this->softDeleteRows('tx_powermail_domain_model_field', 'page', $pageUids, $now);
            $this->softDeleteRows('tx_powermail_domain_model_page', 'uid', $pageUids, $now);
        }
        $this->softDeleteRows('tx_powermail_domain_model_form', 'uid', $formUids, $now);
    }

    /**
     * @param list<int> $pageUids
     */
    private function softDeleteContentOnPages(array $pageUids, int $now): void
    {
        if ($pageUids === []) {
            return;
        }

        $this->softDeleteRows('tt_content', 'pid', $pageUids, $now);
    }

    /**
     * @param list<int> $values
     */
    private function softDeleteRows(string $table, string $field, array $values, int $now): void
    {
        if ($values === [] || !$this->tableHasColumn($table, 'deleted')) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder
            ->update($table)
            ->set('deleted', '1')
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->in(
                    $field,
                    $queryBuilder->createNamedParameter($values, ArrayParameterType::INTEGER)
                )
            )
            ->executeStatement();
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, true> $columns
     */
    private function insertRow(string $table, array $row, array $columns): int
    {
        $connection = $this->connectionPool->getConnectionForTable($table);
        $connection->insert($table, $this->filterRow($row, $columns));

        return $this->normalizeInteger($connection->lastInsertId());
    }

    private function normalizeInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    /**
     * @param list<mixed> $values
     * @return list<int>
     */
    private function normalizeIntegerList(array $values): array
    {
        $integers = [];
        foreach ($values as $value) {
            if (is_int($value)) {
                $integers[] = $value;
                continue;
            }
            if (is_string($value) && is_numeric($value)) {
                $integers[] = (int)$value;
            }
        }

        return $integers;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, true> $columns
     * @return array<string, mixed>
     */
    private function filterRow(array $row, array $columns): array
    {
        return array_intersect_key($row, $columns);
    }

    private function tableExists(string $table): bool
    {
        try {
            $this->connectionPool->getConnectionForTable($table)->createSchemaManager()->listTableColumns($table);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        return isset($this->getColumnNames($table)[$column]);
    }

    /**
     * @return array<string, true>
     */
    private function getColumnNames(string $table): array
    {
        if (isset($this->tableColumnsCache[$table])) {
            return $this->tableColumnsCache[$table];
        }

        $columns = [];
        foreach ($this->connectionPool->getConnectionForTable($table)->createSchemaManager()->listTableColumns($table) as $column) {
            $columns[$column->getName()] = true;
        }

        $this->tableColumnsCache[$table] = $columns;
        return $columns;
    }
}
