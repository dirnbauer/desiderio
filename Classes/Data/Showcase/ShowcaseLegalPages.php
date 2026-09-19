<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Legal and support pages: imprint, privacy, the accessibility statement and
 * the 404 page, plus the blog support pages seeded alongside them.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseLegalPages
{
    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [
            self::imprintPage(),
            self::privacyPage(),
            self::accessibilityPage(),
            self::notFoundPage(),
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function imprintPage(): array
    {
        return [
            'title' => 'Imprint',
            'navTitle' => 'Imprint',
            'slug' => '/imprint',
            'abstract' => 'Demo imprint page seeded by the Desiderio styleguide: the desiderio_imprint element filled with clearly illustrative placeholder data, plus a note on replacing it before go-live.',
            'description' => 'Demo imprint built with the Desiderio imprint content element — illustrative placeholder company data showing the legal-page structure for TYPO3 sites.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_imprint', [
                    'header' => 'Imprint (demo data)',
                    'company_name' => 'webconsulting studio GmbH (illustrative demo company)',
                    'address' => "Lindengasse 12\n1070 Vienna\nAustria",
                    'contact_email' => 'legal@webconsulting.example',
                    'contact_phone' => '+43 1 555 0182',
                    'registry_info' => "Commercial register: FN 000000x (demo data)\nCommercial Court of Vienna",
                    'vat_id' => 'ATU00000000',
                    'additional_info' => '<p>Responsible for content under §25 MedienG: Mara Lindqvist, Managing Director (illustrative). Every value on this page is seeded placeholder data from the Desiderio styleguide.</p>',
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'This is a demo placeholder',
                    'content' => '<p>The desiderio_imprint element gives your legal page a finished, token-themed structure — but the law cares about the content. Replace the illustrative company, register, and contact data above with your real details (and have them reviewed) before this page goes anywhere near production.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => '',
                    'link_text' => '',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function privacyPage(): array
    {
        return [
            'title' => 'Privacy notice',
            'navTitle' => 'Privacy',
            'slug' => '/privacy',
            'abstract' => 'Demo privacy page seeded by the Desiderio styleguide: a GDPR-style notice built with desiderio_privacynotice plus a working desiderio_datarequestform — all clearly placeholder content.',
            'description' => 'Demo GDPR-style privacy notice built with Desiderio elements, including a styled data-request form — placeholder structure to replace with your reviewed policy.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_privacynotice', [
                    'header' => 'Privacy notice (demo)',
                    'intro' => 'Seeded demo notice for the illustrative webconsulting studio site. It demonstrates the structure of a GDPR-style privacy page with the desiderio_privacynotice element — replace every section with your own reviewed text before go-live.',
                    'last_updated' => '12 May 2026',
                    'sections' => [
                        ['title' => 'What we collect', 'content' => '<p>This demo site collects nothing. A real deployment would describe here which personal data you process: contact details from forms, technical data such as IP addresses, and any analytics identifiers.</p>'],
                        ['title' => 'Why we process it', 'content' => '<p>List your purposes and legal bases per GDPR Art. 6 — contract fulfilment, legitimate interest, consent — one plain-language paragraph per purpose, no legalese required by the element.</p>'],
                        ['title' => 'Cookies and consent', 'content' => '<p>Desiderio ships consent-aware banners (GDPR banner, cookie banner elements); document here which categories exist and how visitors change their choice later.</p>'],
                        ['title' => 'Your rights', 'content' => '<p>Access, rectification, erasure, portability, objection. The data-request form below shows how Desiderio lets visitors exercise these rights without writing an email.</p>'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_datarequestform', [
                    'header' => 'Exercise your data rights (demo form)',
                    'description' => 'This is the desiderio_datarequestform element: a styled GDPR request form your visitors can use for access, export, or deletion requests. On this demo page it submits nowhere.',
                    'request_types' => [
                        ['label' => 'Access my data (Art. 15)', 'value' => 'access'],
                        ['label' => 'Export my data (Art. 20)', 'value' => 'export'],
                        ['label' => 'Delete my data (Art. 17)', 'value' => 'deletion'],
                        ['label' => 'Correct my data (Art. 16)', 'value' => 'rectification'],
                    ],
                    'privacy_text' => 'Demo placeholder: in production, explain here how request data is handled and within which deadline you respond (30 days under GDPR).',
                    'submit_text' => 'Send demo request',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function accessibilityPage(): array
    {
        return [
            'title' => 'Accessibility statement',
            'navTitle' => 'Accessibility',
            'slug' => '/accessibility',
            'abstract' => 'Demo accessibility statement seeded by the Desiderio styleguide, plus an engineering note on what the package contributes: a WCAG 2.2 contrast solver, translated ARIA labels, and audited templates.',
            'description' => 'Demo accessibility statement with Desiderio\'s a11y engineering explained: WCAG 2.2 contrast solving on every theme, translated screen-reader labels, audited markup.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_accessibilitystatement', [
                    'header' => 'Accessibility statement (demo)',
                    'conformance_level' => 'aa',
                    'content' => '<p>This demo statement ships with the Desiderio styleguide as a template — replace it with your own audited statement before go-live. Desiderio content elements are engineered against WCAG 2.2 Level AA.</p><h3>What the markup gives you</h3><ul><li>Every interactive component is keyboard reachable, with visible focus states on buttons, links, and form fields.</li><li>Semantic landmarks, native elements, and a logical heading order come baked into all 244 elements.</li><li>Image fields carry alternative-text inputs; decorative SVG icons are hidden from screen readers.</li><li>An 11-category template audit keeps inline styles and hardcoded colors out of every release.</li></ul><h3>What remains your job</h3><p>Editor-entered content, embedded media, and uploaded documents still need human review. This demo template does not replace a real conformance audit of your site.</p>',
                    'contact_email' => 'accessibility@desiderio.example',
                    'last_updated' => '12 May 2026',
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'How Desiderio engineers accessibility, not just claims it',
                    'content' => '<p>Three mechanisms do the heavy lifting. First, a <strong>WCAG 2.2 contrast solver</strong>: the theme generator solves accent lightness per hue against 4.5:1 text and 3:1 UI targets and refuses to emit failing CSS — and a unit test re-checks the shipped bundle for every preset, in light and dark mode. Second, <strong>translated assistive text</strong>: screen-reader labels, carousel controls, dismiss buttons, and pagination run through XLIFF catalogues, so ARIA speaks your visitor\'s language. Third, <strong>audits in CI</strong>: an 11-category template audit at zero tolerance plus 170+ tests keep landmarks, focus states, and heading order from regressing release after release.</p>',
                    'variant' => 'muted',
                    'alignment' => 'left',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'More engineering facts',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function notFoundPage(): array
    {
        return [
            'title' => 'Page not found',
            'navTitle' => '404',
            'slug' => '/404',
            'abstract' => 'The 404 page of the Desiderio styleguide: a short apology, a sitemap of the main sections, and a way back home — built entirely from Desiderio content elements.',
            'description' => 'Page not found — but 244 Desiderio content elements are exactly where they should be. Jump to the styleguide chapters, audience pages, or back to the homepage.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Error 404',
                    'header' => 'This page took the day off',
                    'subheadline' => 'The address you opened does not exist (anymore). The good news: everything worth seeing is one click away — and yes, even this error page is built from seeded Desiderio elements.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'What probably happened',
                    'content' => '<p>A mistyped address, an outdated bookmark, or a link to content that moved when this styleguide was reseeded. Use the map below, or head straight back to the homepage.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => '{{page:home}}',
                    'link_text' => 'Back to the homepage',
                ]),
                ShowcaseBlocks::block('desiderio_sitemapgrid', [
                    'header' => 'Find your way from here',
                    'columns' => '4',
                    'groups' => [
                        [
                            'title' => 'Start here',
                            'pages' => [
                                ['label' => 'Homepage', 'link' => '{{page:home}}'],
                                ['label' => 'Technical features', 'link' => '{{page:technical-features}}'],
                                ['label' => 'GEO & AI search', 'link' => '{{page:geo-ai-search}}'],
                                ['label' => 'Success stories', 'link' => '{{page:success-stories}}'],
                            ],
                        ],
                        [
                            'title' => 'For your team',
                            'pages' => [
                                ['label' => 'Target groups overview', 'link' => '{{page:target-groups}}'],
                                ['label' => 'Agencies & integrators', 'link' => '{{page:target-groups/for-agencies}}'],
                                ['label' => 'In-house teams', 'link' => '{{page:target-groups/for-inhouse-teams}}'],
                                ['label' => 'Freelancers & solo devs', 'link' => '{{page:target-groups/for-freelancers}}'],
                            ],
                        ],
                        [
                            'title' => 'Element chapters',
                            'pages' => [
                                ['label' => 'Hero & Landing Intros', 'link' => '{{page:chapter-hero}}'],
                                ['label' => 'Features & Benefits', 'link' => '{{page:chapter-features}}'],
                                ['label' => 'Plans & Pricing', 'link' => '{{page:chapter-pricing}}'],
                                ['label' => 'Data & Dashboards', 'link' => '{{page:chapter-data}}'],
                                ['label' => 'Trust & Social Proof', 'link' => '{{page:chapter-social-proof}}'],
                            ],
                        ],
                        [
                            'title' => 'Legal & project',
                            'pages' => [
                                ['label' => 'Imprint', 'link' => '{{page:imprint}}'],
                                ['label' => 'Privacy notice', 'link' => '{{page:privacy}}'],
                                ['label' => 'Accessibility', 'link' => '{{page:accessibility}}'],
                                ['label' => 'GitHub repository', 'link' => ShowcaseBlocks::REPO_URL],
                            ],
                        ],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Nothing here — everything there',
                    'description' => 'The homepage has the whole story: 244 elements, 15 themes, and the one command that seeded this site (404 page included).',
                    'cta_text' => 'Take me home',
                    'cta_link' => '{{page:home}}',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * Helper pages for the success-story blog section. Category and tag badges
     * on the posts link here (plugin.tx_blog.settings.categoryUid/tagUid), so
     * these pages only make sense — and only get seeded — when EXT:blog is
     * installed. They stay out of the navigation.
     *
     * @return array<int, ShowcasePage>
     */
    public static function blogSupportPages(): array
    {
        return [
            [
                'title' => 'Category',
                'navTitle' => 'Category',
                'slug' => '/success-stories/category',
                'abstract' => 'Success stories filtered by category.',
                'description' => 'All illustrative Desiderio success stories in a category.',
                'parentSlug' => 'success-stories',
                'hideInNav' => true,
                'content' => [
                    ShowcaseBlocks::block('blog_category', []),
                ],
            ],
            [
                'title' => 'Tag',
                'navTitle' => 'Tag',
                'slug' => '/success-stories/tag',
                'abstract' => 'Success stories filtered by tag.',
                'description' => 'All illustrative Desiderio success stories carrying a tag.',
                'parentSlug' => 'success-stories',
                'hideInNav' => true,
                'content' => [
                    ShowcaseBlocks::block('blog_tag', []),
                ],
            ],
        ];
    }
}
