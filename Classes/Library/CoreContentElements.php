<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Library;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Manifest of the classic TYPO3 core content elements (native CTypes) that
 * Desiderio promotes to first-class catalog citizens — same treatment as the
 * Content Blocks: a custom v14 icon, a rich description, search keywords, a
 * preview fixture, and coverage by both seed commands.
 *
 * This is the SINGLE source of truth consumed by:
 *  - {@see ElementCatalog} (visual-editor picker + fuzzy search + previews),
 *  - {@see \Webconsulting\Desiderio\Seeding\LibraryElementUpserter} (one preview
 *    record per element via desiderio:library:seed),
 *  - {@see \Webconsulting\Desiderio\Data\StyleguideContentGroups} (the styleguide
 *    demo page via desiderio:styleguide:seed),
 *  - Configuration/TCA/Overrides/tt_content.php (the core New Content Element
 *    Wizard icon + description override).
 *
 * Unlike Content Blocks, these elements are NOT redefined here — they keep their
 * native rendering (Resources/Private/ClassicContent/Templates/* + the
 * TypoScript in Configuration/Sets/Desiderio/TypoScript/content.typoscript). We
 * only layer catalog metadata + a native-column preview fixture on top.
 *
 * Per-entry shape:
 *  - cType:         the native tt_content.CType value
 *  - name:          default-language display title (localized via library_core.xlf)
 *  - iconSlug:      icon file core-<slug>.svg / identifier desiderio-ce-<slug>
 *  - group:         an EXISTING picker category id (content|navigation|conversion)
 *                   so the visual-editor frontend labels it with no extra wiring
 *  - gateExtension: the extension that must be loaded for this CType to exist
 *                   (null = always available core/frontend element)
 *  - fixture:       NATIVE tt_content columns for the preview (header, bodytext,
 *                   assets/image/media FAL refs, table_*, pi_flexform, …). Empty
 *                   arrays for content-less elements (div).
 */
final class CoreContentElements
{
    public const string HOST = 'core';

    private const string IMAGE_PRIMARY = 'Resources/Public/Styleguide/Unsplash/laptop-mimi-thian.jpg';
    private const string IMAGE_SECONDARY = 'Resources/Public/Styleguide/Unsplash/laptop-glenn-carstens-peters.jpg';
    private const string IMAGE_TERTIARY = 'Resources/Public/Styleguide/Unsplash/workspace-marvin-meyer.jpg';

    /**
     * Every defined core element, regardless of whether its gate extension is
     * installed. Pure (no TYPO3 service calls) so it is safe in unit tests.
     *
     * @return list<array{cType: string, name: string, iconSlug: string, group: string, gateExtension: string|null, fixture: array<string, mixed>}>
     */
    public static function all(): array
    {
        return [
            // --- Typical page content -------------------------------------
            [
                'cType' => 'header',
                'name' => 'Header',
                'iconSlug' => 'header',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'Coworking desks in the old tram depot',
                    'header_layout' => 2,
                    'subheader' => 'Open Monday to Friday from 7:00 to 22:00. The reception desk is staffed until 18:00.',
                ],
            ],
            [
                'cType' => 'text',
                'name' => 'Text',
                'iconSlug' => 'text',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'How the day pass works',
                    'bodytext' => '<p>A day pass gives you a desk in the open area from 8:00 to 20:00. '
                        . 'Coffee, Wi-Fi and 20 printed pages are included. Book by <strong>18:00 the day before</strong>, '
                        . 'or ask at reception on the day if a desk is <em>still free</em>.</p>',
                ],
            ],
            [
                'cType' => 'textpic',
                'name' => 'Text & Images',
                'iconSlug' => 'textpic',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'A quiet floor for focused work',
                    'bodytext' => '<p>The second floor is a quiet zone with 24 desks. '
                        . 'Calls and meetings take place in the phone booths and rooms on the first floor.</p>',
                    'imageorient' => 25,
                    'image' => [
                        [
                            'file' => self::IMAGE_PRIMARY,
                            'title' => 'Working in the quiet zone',
                            'alternative' => 'A person working on a laptop held on their lap.',
                        ],
                    ],
                ],
            ],
            [
                'cType' => 'textmedia',
                'name' => 'Text & Media',
                'iconSlug' => 'textmedia',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'Book a meeting room from your desk',
                    'bodytext' => '<p>Members book the four meeting rooms online, by the half hour. '
                        . 'A screen at each door shows who has the room next.</p>',
                    'imageorient' => 26,
                    'assets' => [
                        [
                            'file' => self::IMAGE_SECONDARY,
                            'title' => 'Booking a meeting room',
                            'alternative' => 'Hands typing on a laptop on a wooden desk.',
                        ],
                    ],
                ],
            ],
            [
                'cType' => 'image',
                'name' => 'Images Only',
                'iconSlug' => 'image',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'A look around the depot',
                    'imagecols' => 3,
                    'image' => [
                        [
                            'file' => self::IMAGE_PRIMARY,
                            'title' => 'Working in the quiet zone',
                            'alternative' => 'A person working on a laptop held on their lap.',
                        ],
                        [
                            'file' => self::IMAGE_SECONDARY,
                            'title' => 'Booking a meeting room',
                            'alternative' => 'Hands typing on a laptop on a wooden desk.',
                        ],
                        [
                            'file' => self::IMAGE_TERTIARY,
                            'title' => 'Shared tables on the ground floor',
                            'alternative' => 'People working on laptops around a shared table.',
                        ],
                    ],
                ],
            ],

            // --- Lists & files --------------------------------------------
            [
                'cType' => 'bullets',
                'name' => 'Bullet List',
                'iconSlug' => 'bullets',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'Included in every membership',
                    'bodytext' => "Wi-Fi and a network cable at every desk\n"
                        . "Coffee, tea and water all day\n"
                        . "A locker on your floor\n"
                        . 'Up to 50 printed pages a month',
                ],
            ],
            [
                'cType' => 'table',
                'name' => 'Table',
                'iconSlug' => 'table',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'Membership plans compared',
                    'bodytext' => "Plan|Desk|Meeting room hours\nDay pass|Open area|None\nFlex|Open area|4 a month\nFixed|Your own desk|10 a month",
                    'table_delimiter' => 124,
                    'table_enclosure' => 0,
                    'table_header_position' => 1,
                    'table_caption' => 'What each membership plan includes.',
                ],
            ],
            [
                'cType' => 'uploads',
                'name' => 'File Links',
                'iconSlug' => 'uploads',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'House rules and floor plans',
                    'media' => [
                        [
                            'file' => self::IMAGE_PRIMARY,
                            'title' => 'House rules',
                            'alternative' => 'The house rules for members, as a download.',
                        ],
                        [
                            'file' => self::IMAGE_SECONDARY,
                            'title' => 'Floor plans',
                            'alternative' => 'Floor plans of both floors, as a download.',
                        ],
                    ],
                ],
            ],

            // --- Special elements -----------------------------------------
            [
                'cType' => 'div',
                'name' => 'Divider',
                'iconSlug' => 'divider',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [],
            ],
            [
                'cType' => 'html',
                'name' => 'Plain HTML',
                'iconSlug' => 'html',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'bodytext' => "<div class=\"ce-html-demo\">\n"
                        . "  <!-- Raw HTML is output verbatim, without processing. -->\n"
                        . "  <p>Today the reception desk closes early, at 16:00.</p>\n"
                        . '</div>',
                ],
            ],
            [
                'cType' => 'shortcut',
                'name' => 'Insert Records',
                'iconSlug' => 'shortcut',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'Opening hours and contact',
                ],
            ],

            // --- Menus ----------------------------------------------------
            [
                'cType' => 'menu_pages',
                'name' => 'Menu: Selected Pages',
                'iconSlug' => 'menu-pages',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Popular pages'],
            ],
            [
                'cType' => 'menu_subpages',
                'name' => 'Menu: Subpages of Selected Pages',
                'iconSlug' => 'menu-subpages',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'In this section'],
            ],
            [
                'cType' => 'menu_sitemap',
                'name' => 'Menu: Sitemap',
                'iconSlug' => 'menu-sitemap',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Sitemap'],
            ],
            [
                'cType' => 'menu_sitemap_pages',
                'name' => 'Menu: Sitemaps of Selected Pages',
                'iconSlug' => 'menu-sitemap-pages',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Membership and event pages'],
            ],
            [
                'cType' => 'menu_section',
                'name' => 'Menu: Section Index',
                'iconSlug' => 'menu-section',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'On this page'],
            ],
            [
                'cType' => 'menu_section_pages',
                'name' => 'Menu: Section Index of Selected Pages',
                'iconSlug' => 'menu-section-pages',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Sections of the membership pages'],
            ],
            [
                'cType' => 'menu_abstract',
                'name' => 'Menu: Abstracts',
                'iconSlug' => 'menu-abstract',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Guides for new members'],
            ],
            [
                'cType' => 'menu_recently_updated',
                'name' => 'Menu: Recently Updated',
                'iconSlug' => 'menu-recently-updated',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Recently updated pages'],
            ],
            [
                'cType' => 'menu_related_pages',
                'name' => 'Menu: Related Pages',
                'iconSlug' => 'menu-related',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Related pages'],
            ],
            [
                'cType' => 'menu_categorized_pages',
                'name' => 'Menu: Categorized Pages',
                'iconSlug' => 'menu-categorized-pages',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Pages in this category'],
            ],
            [
                'cType' => 'menu_categorized_content',
                'name' => 'Menu: Categorized Content',
                'iconSlug' => 'menu-categorized-content',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'More on this topic'],
            ],

            // --- Form plugins (gated on their extension) ------------------
            [
                'cType' => 'form_formframework',
                'name' => 'Form',
                'iconSlug' => 'form',
                'group' => 'conversion',
                'gateExtension' => 'form',
                'fixture' => ['header' => 'Ask about a membership'],
            ],
            [
                // felogin registers its plugin as CType "felogin_login" (v13+
                // ExtensionUtility::registerPlugin('Felogin', 'Login', …)).
                'cType' => 'felogin_login',
                'name' => 'Login Form',
                'iconSlug' => 'login',
                'group' => 'conversion',
                'gateExtension' => 'felogin',
                'fixture' => ['header' => 'Log in to the member area'],
            ],
            [
                'cType' => 'powermail_pi1',
                'name' => 'Form (Powermail)',
                'iconSlug' => 'powermail',
                'group' => 'conversion',
                'gateExtension' => 'powermail',
                'fixture' => ['header' => 'Book a tour of the depot'],
            ],
        ];
    }

    /**
     * Entries whose gate extension is loaded (or which have no gate). This is
     * what the catalog and seeders should use, so a CType the site cannot render
     * never appears in a picker.
     *
     * @return list<array{cType: string, name: string, iconSlug: string, group: string, gateExtension: string|null, fixture: array<string, mixed>}>
     */
    public static function available(): array
    {
        return array_values(array_filter(
            self::all(),
            static fn(array $element): bool => $element['gateExtension'] === null
                || ExtensionManagementUtility::isLoaded($element['gateExtension']),
        ));
    }

    /**
     * Native (non-plugin) elements that render standalone on a styleguide page.
     * Plugins are excluded: they need a configured form/flexform and Powermail
     * has its own dedicated demo seeder.
     *
     * @return list<array{cType: string, name: string, iconSlug: string, group: string, gateExtension: string|null, fixture: array<string, mixed>}>
     */
    public static function styleguideElements(): array
    {
        return array_values(array_filter(
            self::available(),
            static fn(array $element): bool => $element['gateExtension'] === null,
        ));
    }

    /**
     * All defined core CType identifiers (used for idempotent seed cleanup).
     *
     * @return list<string>
     */
    public static function cTypes(): array
    {
        return array_map(static fn(array $element): string => $element['cType'], self::all());
    }
}
