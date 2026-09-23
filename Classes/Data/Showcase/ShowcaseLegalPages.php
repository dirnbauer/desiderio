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
            'abstract' => 'A demo imprint from the Desiderio demo site. The desiderio_imprint element holds placeholder data, and a note says what to replace before launch.',
            'description' => 'Demo imprint built with the Desiderio imprint content element. Placeholder company data shows the structure of a legal page on a TYPO3 site.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_imprint', [
                    'header' => 'Imprint (demo data)',
                    'company_name' => 'webconsulting studio GmbH (demo company)',
                    'address' => "Lindengasse 12\n1070 Vienna\nAustria",
                    'contact_email' => 'legal@webconsulting.example',
                    'contact_phone' => '+43 1 555 0182',
                    'registry_info' => "Commercial register: FN 000000x (demo data)\nCommercial Court of Vienna",
                    'vat_id' => 'ATU00000000',
                    'additional_info' => '<p>Responsible for content under §25 MedienG: Mara Lindqvist, Managing Director (example). Every detail on this page is placeholder data from the Desiderio demo site.</p>',
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'This imprint is a demo placeholder',
                    'content' => '<p>The desiderio_imprint element gives your legal page a finished, themed structure. The law is about the content, though. Replace the example company, register and contact details above with your own, and have them reviewed before launch.</p>',
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
            'abstract' => 'Demo privacy page of the Desiderio demo site: a GDPR-style notice and a working data request form. All content is placeholder text.',
            'description' => 'Demo GDPR-style privacy notice built with Desiderio elements, with a styled data request form. Replace the placeholder text with your reviewed policy.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_privacynotice', [
                    'header' => 'Privacy notice (demo)',
                    'intro' => 'This demo notice for the example webconsulting studio site shows the structure of a GDPR-style privacy page, built with desiderio_privacynotice. Replace every section with reviewed text before launch.',
                    'last_updated' => '12 May 2026',
                    'sections' => [
                        ['title' => 'What we collect', 'content' => '<p>This demo site collects nothing. On a real site, list here the personal data you process: contact details from forms, technical data such as IP addresses, and any analytics identifiers.</p>'],
                        ['title' => 'Why we process it', 'content' => '<p>List each purpose with its legal basis under GDPR Art. 6: contract fulfilment, legitimate interest or consent. Use one plain paragraph per purpose; the element needs no legalese.</p>'],
                        ['title' => 'Cookies and consent', 'content' => '<p>Desiderio includes consent-aware banners: the GDPR banner and cookie banner elements. Describe here which cookie categories exist and how visitors can change their choice later.</p>'],
                        ['title' => 'Your rights', 'content' => '<p>Access, rectification, erasure, portability and objection. The data request form below shows how visitors can use these rights on a Desiderio site without writing an email.</p>'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_datarequestform', [
                    'header' => 'Make a data request (demo form)',
                    'description' => 'The desiderio_datarequestform element is a styled GDPR form for access, export or deletion requests. On this demo page it sends nothing.',
                    'request_types' => [
                        ['label' => 'Access my data (Art. 15)', 'value' => 'access'],
                        ['label' => 'Export my data (Art. 20)', 'value' => 'export'],
                        ['label' => 'Delete my data (Art. 17)', 'value' => 'deletion'],
                        ['label' => 'Correct my data (Art. 16)', 'value' => 'rectification'],
                    ],
                    'privacy_text' => 'Demo placeholder: on a live site, explain here how request data is handled and by when you reply (within one month under the GDPR).',
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
            'abstract' => 'A demo accessibility statement from the Desiderio demo site. A second section explains what the package adds: a WCAG 2.2 contrast solver, translated ARIA labels and audited templates.',
            'description' => 'Demo accessibility statement. Desiderio solves WCAG 2.2 contrast for every theme preset, translates screen-reader labels and audits its markup.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_accessibilitystatement', [
                    'header' => 'Accessibility statement (demo)',
                    'conformance_level' => 'aa',
                    'content' => '<p>This demo statement comes with the Desiderio demo site as a template. Replace it with your own audited statement before launch. Desiderio content elements are designed for WCAG 2.2 Level AA.</p><h3>What the markup gives you</h3><ul><li>Every interactive component works with the keyboard, with a visible focus state on buttons, links and form fields.</li><li>All 244 elements use semantic landmarks, native elements and a logical heading order.</li><li>Image fields have an input for alternative text. Decorative SVG icons are hidden from screen readers.</li><li>A template audit keeps inline styles and hard-coded colours out of every release.</li></ul><h3>What you still need to do</h3><p>Editor content, embedded media and uploaded documents still need a human review. This demo template does not replace a real conformance audit of your site.</p>',
                    'contact_email' => 'accessibility@desiderio.example',
                    'last_updated' => '12 May 2026',
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'How Desiderio builds in accessibility',
                    'content' => '<p>Three mechanisms do most of the work. First, a <strong>WCAG 2.2 contrast solver</strong>. The theme generator sets the lightness of each accent hue to reach 4.5:1 for text and 3:1 for interface elements. It never writes failing CSS. A unit test checks the shipped bundle again for every preset, in light and dark mode. Second, <strong>translated assistive text</strong>. Screen-reader labels, carousel controls, dismiss buttons and pagination use XLIFF catalogues, so ARIA labels are in your visitor\'s language. Third, <strong>audits in CI</strong>. A zero-tolerance template audit and 170+ tests keep landmarks, focus states and heading order from regressing.</p>',
                    'variant' => 'muted',
                    'alignment' => 'left',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
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
            'abstract' => 'The 404 page of the Desiderio demo site, built entirely from Desiderio content elements. It apologises, lists the main sections and links back to the homepage.',
            'description' => 'This page does not exist. Go to the homepage, the 10 groups of Desiderio content elements or the pages for agencies, in-house teams and freelancers.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Error 404',
                    'header' => 'This page does not exist',
                    'subheadline' => 'Sorry, the address you opened does not exist, or no longer exists. The main pages are listed below.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Why you see this page',
                    'content' => '<p>The address may contain a typo, or a bookmark is out of date. Content can also move when this demo site is seeded again.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => '{{page:home}}',
                    'link_text' => 'Open the homepage',
                ]),
                ShowcaseBlocks::block('desiderio_sitemapgrid', [
                    'header' => 'Main pages of this site',
                    'columns' => '4',
                    'groups' => [
                        [
                            'title' => 'Start here',
                            'pages' => [
                                ['label' => 'Homepage', 'link' => '{{page:home}}'],
                                ['label' => 'Technical features', 'link' => '{{page:technical-features}}'],
                                ['label' => 'GEO and AI search', 'link' => '{{page:geo-ai-search}}'],
                                ['label' => 'Success stories', 'link' => '{{page:success-stories}}'],
                            ],
                        ],
                        [
                            'title' => 'For your team',
                            'pages' => [
                                ['label' => 'Target groups', 'link' => '{{page:target-groups}}'],
                                ['label' => 'Agencies and integrators', 'link' => '{{page:target-groups/for-agencies}}'],
                                ['label' => 'In-house teams', 'link' => '{{page:target-groups/for-inhouse-teams}}'],
                                ['label' => 'Freelancers', 'link' => '{{page:target-groups/for-freelancers}}'],
                            ],
                        ],
                        [
                            'title' => 'Element groups',
                            'pages' => [
                                ['label' => 'Heroes and page intros', 'link' => '{{page:chapter-hero}}'],
                                ['label' => 'Features and benefits', 'link' => '{{page:chapter-features}}'],
                                ['label' => 'Plans and pricing', 'link' => '{{page:chapter-pricing}}'],
                                ['label' => 'Data and dashboards', 'link' => '{{page:chapter-data}}'],
                                ['label' => 'Trust and social proof', 'link' => '{{page:chapter-social-proof}}'],
                            ],
                        ],
                        [
                            'title' => 'Legal and project',
                            'pages' => [
                                ['label' => 'Imprint', 'link' => '{{page:imprint}}'],
                                ['label' => 'Privacy', 'link' => '{{page:privacy}}'],
                                ['label' => 'Accessibility', 'link' => '{{page:accessibility}}'],
                                ['label' => 'GitHub repository', 'link' => ShowcaseBlocks::REPO_URL],
                            ],
                        ],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Start again on the homepage',
                    'description' => 'The homepage shows all 244 elements, the 15 theme presets and the one command that builds this demo site, including this 404 page.',
                    'cta_text' => 'Visit the homepage',
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
                'description' => 'All example Desiderio success stories in one category.',
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
                'description' => 'All example Desiderio success stories with one tag.',
                'parentSlug' => 'success-stories',
                'hideInNav' => true,
                'content' => [
                    ShowcaseBlocks::block('blog_tag', []),
                ],
            ],
        ];
    }
}
