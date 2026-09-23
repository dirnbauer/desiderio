<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

/**
 * Static Powermail demo form fixtures for {@see \Webconsulting\Desiderio\Command\PowermailDemoSeeder}.
 *
 * @phpstan-type DemoOption array{0: string, 1: string, 2: string}
 * @phpstan-type DemoField array{type: string, marker: string, titleEn: string, titleDe: string, mandatory: bool, validation: int, validationConfiguration: string, sender_email: bool, sender_name: bool, placeholderEn: string, placeholderDe: string, prefill: string, options: list<DemoOption>, textEn: string, textDe: string, autocompleteToken: string, autocompleteSection: string, autocompleteType: string, autocompletePurpose: string}
 * @phpstan-type DemoPage array{titleEn: string, titleDe: string, fields: list<DemoField>}
 * @phpstan-type DemoForm array{slug: string, titleEn: string, titleDe: string, pageTitleEn: string, pageTitleDe: string, introEn: string, introDe: string, thankTitleEn: string, thankTitleDe: string, thankBodyEn: string, thankBodyDe: string, moresteps: bool, pages: list<DemoPage>}
 */
final class PowermailDemoFormDefinitions
{
    private const array AUTOCOMPLETE_TOKENS_BY_MARKER = [
        'name' => 'name',
        'email' => 'email',
        'phone' => 'tel',
        'company' => 'organization',
    ];

    /**
     * @return list<DemoForm>
     */
    public static function demoForms(): array
    {
        return [
            [
                'slug' => 'contact',
                'titleEn' => 'Contact form',
                'titleDe' => 'Kontaktformular',
                'pageTitleEn' => 'Powermail 01: Contact form',
                'pageTitleDe' => 'Powermail 01: Kontaktformular',
                'introEn' => 'A contact form for general questions. Visitors choose a topic and write a message.',
                'introDe' => 'Ein Standard-Kontaktformular für allgemeine Anfragen mit Themenauswahl und Nachricht.',
                'thankTitleEn' => 'Contact request received',
                'thankTitleDe' => 'Kontaktanfrage erhalten',
                'thankBodyEn' => 'Thank you. We have received your contact request.',
                'thankBodyDe' => 'Danke. Ihre Kontaktanfrage wurde empfangen.',
                'moresteps' => false,
                'pages' => [
                    [
                        'titleEn' => 'Contact',
                        'titleDe' => 'Kontakt',
                        'fields' => [
                            self::field('input', 'name', 'Name', 'Name', ['mandatory' => true, 'sender_name' => true]),
                            self::field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true, 'placeholderEn' => 'you@example.com', 'placeholderDe' => 'sie@example.com']),
                            self::field('input', 'phone', 'Phone', 'Telefon', ['mandatory' => true, 'placeholderEn' => '+43 ...', 'placeholderDe' => '+43 ...']),
                            self::field('input', 'team_size', 'Team size', 'Teamgröße', ['mandatory' => true, 'validation' => 6, 'validationConfiguration' => '5', 'placeholderEn' => '5', 'placeholderDe' => '5']),
                            self::field('select', 'topic', 'Topic', 'Thema', ['mandatory' => true, 'options' => [['Please select', 'Bitte auswählen', ''], ['General question', 'Allgemeine Anfrage', 'general'], ['Sales', 'Vertrieb', 'sales'], ['Support', 'Support', 'support']]]),
                            self::field('textarea', 'message', 'Message', 'Nachricht', ['mandatory' => true, 'placeholderEn' => 'How can we help?', 'placeholderDe' => 'Wie können wir helfen?']),
                            self::field('friendlycaptcha', 'friendlycaptcha', 'Spam protection', 'Spam-Schutz'),
                            self::field('submit', 'submit', 'Send request', 'Anfrage senden'),
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'newsletter',
                'titleEn' => 'Newsletter sign-up',
                'titleDe' => 'Newsletter Anmeldung',
                'pageTitleEn' => 'Powermail 02: Newsletter sign-up',
                'pageTitleDe' => 'Powermail 02: Newsletter Anmeldung',
                'introEn' => 'A short newsletter sign-up with email validation, a choice of topics and consent.',
                'introDe' => 'Eine kompakte Newsletter-Anmeldung mit E-Mail-Validierung, Interessenauswahl und Zustimmung.',
                'thankTitleEn' => 'Newsletter sign-up received',
                'thankTitleDe' => 'Newsletter Anmeldung erhalten',
                'thankBodyEn' => 'Thank you for signing up. We have received your newsletter request.',
                'thankBodyDe' => 'Danke für die Anmeldung. Ihre Newsletter-Anfrage wurde empfangen.',
                'moresteps' => false,
                'pages' => [
                    [
                        'titleEn' => 'Sign-up',
                        'titleDe' => 'Anmeldung',
                        'fields' => [
                            self::field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true, 'placeholderEn' => 'you@example.com', 'placeholderDe' => 'sie@example.com']),
                            self::field('check', 'interests', 'Interests', 'Interessen', ['mandatory' => true, 'options' => [['Company news', 'Unternehmensnews', 'news'], ['Events', 'Events', 'events'], ['Product updates', 'Produktupdates', 'updates']]]),
                            self::field('check', 'consent', 'Consent', 'Zustimmung', ['mandatory' => true, 'options' => [['I agree to receive the newsletter.', 'Ich möchte den Newsletter erhalten.', 'yes']]]),
                            self::field('friendlycaptcha', 'friendlycaptcha', 'Spam protection', 'Spam-Schutz'),
                            self::field('submit', 'submit', 'Subscribe', 'Abonnieren'),
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'callback',
                'titleEn' => 'Callback request',
                'titleDe' => 'Rückrufanfrage',
                'pageTitleEn' => 'Powermail 03: Callback request',
                'pageTitleDe' => 'Powermail 03: Rückrufanfrage',
                'introEn' => 'A callback form for visitors who prefer a phone call.',
                'introDe' => 'Ein Rückruf-Formular für Nutzerinnen und Nutzer, die eine telefonische Rückmeldung bevorzugen.',
                'thankTitleEn' => 'Callback request received',
                'thankTitleDe' => 'Rückrufanfrage erhalten',
                'thankBodyEn' => 'Thank you. We have received your callback request.',
                'thankBodyDe' => 'Danke. Ihre Rückrufanfrage wurde empfangen.',
                'moresteps' => false,
                'pages' => [
                    [
                        'titleEn' => 'Callback',
                        'titleDe' => 'Rückruf',
                        'fields' => [
                            self::field('input', 'name', 'Name', 'Name', ['mandatory' => true, 'sender_name' => true]),
                            self::field('input', 'phone', 'Phone', 'Telefon', ['mandatory' => true, 'placeholderEn' => '+43 ...', 'placeholderDe' => '+43 ...']),
                            self::field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true]),
                            self::field('select', 'preferred_time', 'Preferred time', 'Bevorzugte Zeit', ['mandatory' => true, 'options' => [['Morning', 'Vormittag', 'morning'], ['Afternoon', 'Nachmittag', 'afternoon'], ['Evening', 'Abend', 'evening']]]),
                            self::field('textarea', 'reason', 'Reason', 'Anliegen', ['mandatory' => true, 'placeholderEn' => 'What should we talk about?', 'placeholderDe' => 'Worum soll es gehen?']),
                            self::field('friendlycaptcha', 'friendlycaptcha', 'Spam protection', 'Spam-Schutz'),
                            self::field('submit', 'submit', 'Request a callback', 'Rückruf anfordern'),
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'appointment',
                'titleEn' => 'Appointment request',
                'titleDe' => 'Terminanfrage',
                'pageTitleEn' => 'Powermail 04: Appointment request',
                'pageTitleDe' => 'Powermail 04: Terminanfrage',
                'introEn' => 'A two-step appointment form. Contact details come first, then the preferred date and time.',
                'introDe' => 'Ein zweistufiges Terminformular, das Kontaktdaten und Terminwünsche trennt.',
                'thankTitleEn' => 'Appointment request received',
                'thankTitleDe' => 'Terminanfrage erhalten',
                'thankBodyEn' => 'Thank you. We have received your appointment request.',
                'thankBodyDe' => 'Danke. Ihre Terminanfrage wurde empfangen.',
                'moresteps' => true,
                'pages' => [
                    [
                        'titleEn' => 'Contact details',
                        'titleDe' => 'Kontaktdaten',
                        'fields' => [
                            self::field('input', 'name', 'Name', 'Name', ['mandatory' => true, 'sender_name' => true]),
                            self::field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true]),
                            self::field('input', 'phone', 'Phone', 'Telefon', ['mandatory' => true, 'placeholderEn' => '+43 ...', 'placeholderDe' => '+43 ...']),
                            self::field('input', 'company', 'Company', 'Unternehmen', ['mandatory' => true]),
                        ],
                    ],
                    [
                        'titleEn' => 'Schedule',
                        'titleDe' => 'Termin',
                        'fields' => [
                            self::field('date', 'preferred_date', 'Preferred date', 'Wunschtermin', ['mandatory' => true]),
                            self::field('select', 'preferred_time', 'Preferred time', 'Bevorzugte Zeit', ['mandatory' => true, 'options' => [['Morning', 'Vormittag', 'morning'], ['Afternoon', 'Nachmittag', 'afternoon'], ['Flexible', 'Flexibel', 'flexible']]]),
                            self::field('radio', 'format', 'Format', 'Format', ['mandatory' => true, 'options' => [['Video call', 'Videocall', 'video'], ['Phone call', 'Telefonat', 'phone'], ['On site', 'Vor Ort', 'onsite']]]),
                            self::field('textarea', 'notes', 'Notes', 'Notizen'),
                            self::field('friendlycaptcha', 'friendlycaptcha', 'Spam protection', 'Spam-Schutz'),
                            self::field('submit', 'submit', 'Request an appointment', 'Termin anfragen'),
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'support',
                'titleEn' => 'Support request',
                'titleDe' => 'Supportanfrage',
                'pageTitleEn' => 'Powermail 05: Support request',
                'pageTitleDe' => 'Powermail 05: Supportanfrage',
                'introEn' => 'A support form with priority, affected area, description and an optional attachment.',
                'introDe' => 'Ein Supportformular mit Priorität, betroffenem Bereich, Beschreibung und optionalem Anhang.',
                'thankTitleEn' => 'Support request received',
                'thankTitleDe' => 'Supportanfrage erhalten',
                'thankBodyEn' => 'Thank you. We have received your support request.',
                'thankBodyDe' => 'Danke. Ihre Supportanfrage wurde empfangen.',
                'moresteps' => true,
                'pages' => [
                    [
                        'titleEn' => 'Your details',
                        'titleDe' => 'Anfragende Person',
                        'fields' => [
                            self::field('input', 'name', 'Name', 'Name', ['mandatory' => true, 'sender_name' => true]),
                            self::field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true]),
                            self::field('input', 'customer_number', 'Customer number', 'Kundennummer', ['mandatory' => true]),
                            self::field('select', 'priority', 'Priority', 'Priorität', ['mandatory' => true, 'options' => [['Low', 'Niedrig', 'low'], ['Normal', 'Normal', 'normal'], ['Urgent', 'Dringend', 'urgent']]]),
                        ],
                    ],
                    [
                        'titleEn' => 'Issue',
                        'titleDe' => 'Problem',
                        'fields' => [
                            self::field('select', 'affected_area', 'Affected area', 'Betroffener Bereich', ['mandatory' => true, 'options' => [['Website', 'Website', 'website'], ['Backend', 'Backend', 'backend'], ['Email delivery', 'E-Mail Versand', 'mail'], ['Other', 'Sonstiges', 'other']]]),
                            self::field('textarea', 'description', 'Description', 'Beschreibung', ['mandatory' => true]),
                            self::field('file', 'attachment', 'Attachment', 'Anhang'),
                            self::field('check', 'privacy', 'Privacy', 'Datenschutz', ['mandatory' => true, 'options' => [['I agree that this request may be processed.', 'Ich stimme der Verarbeitung dieser Anfrage zu.', 'accepted']]]),
                            self::field('hidden', 'source', 'Source', 'Quelle', ['prefill' => 'desiderio-powermail-demo']),
                            self::field('friendlycaptcha', 'friendlycaptcha', 'Spam protection', 'Spam-Schutz'),
                            self::field('submit', 'submit', 'Send support request', 'Supportanfrage senden'),
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'project',
                'titleEn' => 'Project request',
                'titleDe' => 'Projektanfrage',
                'pageTitleEn' => 'Powermail 06: Project request',
                'pageTitleDe' => 'Powermail 06: Projektanfrage',
                'introEn' => 'A project request in four steps: contact details, project scope, budget and timing, and a final step with consent.',
                'introDe' => 'Ein vierstufiger Projektanfrage-Assistent: Kontaktdaten, Projektumfang, Budget und Zeitplan sowie ein Abschluss-Schritt mit Zustimmung.',
                'thankTitleEn' => 'Project request received',
                'thankTitleDe' => 'Projektanfrage erhalten',
                'thankBodyEn' => 'Thank you. We have received your project request and will reply within 2 working days.',
                'thankBodyDe' => 'Danke. Ihre Projektanfrage wurde empfangen, wir melden uns innerhalb von zwei Werktagen.',
                'moresteps' => true,
                'pages' => [
                    [
                        'titleEn' => 'Contact',
                        'titleDe' => 'Kontakt',
                        'fields' => [
                            self::field('input', 'name', 'Name', 'Name', ['mandatory' => true, 'sender_name' => true]),
                            self::field('input', 'email', 'Email', 'E-Mail', ['mandatory' => true, 'validation' => 1, 'sender_email' => true, 'placeholderEn' => 'you@example.com', 'placeholderDe' => 'sie@example.com']),
                            self::field('input', 'phone', 'Phone', 'Telefon', ['mandatory' => true, 'placeholderEn' => '+43 ...', 'placeholderDe' => '+43 ...']),
                            self::field('input', 'company', 'Company', 'Unternehmen', ['mandatory' => true]),
                            self::field('select', 'role', 'Your role', 'Ihre Rolle', ['mandatory' => true, 'options' => [['Management', 'Geschäftsführung', 'management'], ['Marketing', 'Marketing', 'marketing'], ['IT and development', 'IT / Entwicklung', 'it'], ['Other', 'Sonstiges', 'other']]]),
                        ],
                    ],
                    [
                        'titleEn' => 'Project',
                        'titleDe' => 'Projekt',
                        'fields' => [
                            self::field('select', 'project_type', 'Project type', 'Projektart', ['mandatory' => true, 'options' => [['New website', 'Neue Website', 'new'], ['Relaunch', 'Relaunch', 'relaunch'], ['Extension development', 'Extension-Entwicklung', 'extension'], ['Consulting', 'Beratung', 'consulting']]]),
                            self::field('radio', 'cms', 'Preferred CMS', 'Bevorzugtes CMS', ['mandatory' => true, 'options' => [['TYPO3', 'TYPO3', 'typo3'], ['Other', 'Anderes', 'other'], ['Not decided yet', 'Noch offen', 'undecided']]]),
                            self::field('check', 'features', 'Required features', 'Benötigte Funktionen', ['mandatory' => true, 'options' => [['Several languages', 'Mehrsprachigkeit', 'multilanguage'], ['Forms', 'Formulare', 'forms'], ['Search', 'Suche', 'search'], ['News or blog', 'News / Blog', 'news'], ['Shop integration', 'Shop-Anbindung', 'shop']]]),
                        ],
                    ],
                    [
                        'titleEn' => 'Budget and timing',
                        'titleDe' => 'Budget & Zeitplan',
                        'fields' => [
                            self::field('radio', 'budget', 'Budget range', 'Budgetrahmen', ['mandatory' => true, 'options' => [['Under €10,000', 'Unter 10.000 Euro', 'small'], ['€10,000 to €25,000', '10.000 bis 25.000 Euro', 'medium'], ['€25,000 to €50,000', '25.000 bis 50.000 Euro', 'large'], ['Over €50,000', 'Über 50.000 Euro', 'enterprise']]]),
                            self::field('select', 'start', 'Preferred start', 'Gewünschter Start', ['mandatory' => true, 'options' => [['As soon as possible', 'So bald wie möglich', 'asap'], ['This quarter', 'Dieses Quartal', 'quarter'], ['This year', 'Dieses Jahr', 'year'], ['Flexible', 'Flexibel', 'flexible']]]),
                            self::field('date', 'deadline', 'Fixed deadline', 'Fixer Endtermin'),
                            self::field('textarea', 'description', 'Project description', 'Projektbeschreibung', ['mandatory' => true, 'placeholderEn' => 'Goals, target groups, existing systems ...', 'placeholderDe' => 'Ziele, Zielgruppen, bestehende Systeme ...']),
                        ],
                    ],
                    [
                        'titleEn' => 'Final step',
                        'titleDe' => 'Abschluss',
                        'fields' => [
                            self::field('select', 'referral', 'How did you hear about us?', 'Wie sind Sie auf uns aufmerksam geworden?', ['mandatory' => true, 'options' => [['Recommendation', 'Empfehlung', 'recommendation'], ['Search engine', 'Suchmaschine', 'search'], ['Social media', 'Social Media', 'social'], ['Event', 'Veranstaltung', 'event'], ['Other', 'Sonstiges', 'other']]]),
                            self::field('check', 'newsletter', 'Newsletter', 'Newsletter', ['options' => [['Send me news about TYPO3 and Desiderio.', 'Halten Sie mich über TYPO3- und Desiderio-News auf dem Laufenden.', 'yes']]]),
                            self::field('check', 'privacy', 'Privacy', 'Datenschutz', ['mandatory' => true, 'options' => [['I agree that this request may be processed.', 'Ich stimme der Verarbeitung dieser Anfrage zu.', 'accepted']]]),
                            self::field('hidden', 'source', 'Source', 'Quelle', ['prefill' => 'desiderio-powermail-demo']),
                            self::field('friendlycaptcha', 'friendlycaptcha', 'Spam protection', 'Spam-Schutz'),
                            self::field('submit', 'submit', 'Send project request', 'Projektanfrage senden'),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $options overrides merged over the defaults
     * @return DemoField
     */
    private static function field(string $type, string $marker, string $titleEn, string $titleDe, array $options = []): array
    {
        return $options + [
            'type' => $type,
            'marker' => $marker,
            'titleEn' => $titleEn,
            'titleDe' => $titleDe,
            'mandatory' => false,
            'validation' => 0,
            'validationConfiguration' => '',
            'sender_email' => false,
            'sender_name' => false,
            'placeholderEn' => '',
            'placeholderDe' => '',
            'prefill' => '',
            'options' => [],
            'textEn' => '',
            'textDe' => '',
            'autocompleteToken' => self::AUTOCOMPLETE_TOKENS_BY_MARKER[$marker] ?? '',
            'autocompleteSection' => '',
            'autocompleteType' => '',
            'autocompletePurpose' => '',
        ];
    }
}
