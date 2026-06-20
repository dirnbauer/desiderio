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
 * native rendering (Resources/Private/FluidStyledContent/Templates/* + the
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
    public const HOST = 'core';

    private const IMAGE_PRIMARY = 'Resources/Public/Styleguide/Unsplash/laptop-mimi-thian.jpg';
    private const IMAGE_SECONDARY = 'Resources/Public/Styleguide/Unsplash/laptop-glenn-carstens-peters.jpg';
    private const IMAGE_TERTIARY = 'Resources/Public/Styleguide/Unsplash/workspace-marvin-meyer.jpg';

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
                    'header' => 'A standalone section heading',
                    'header_layout' => 2,
                    'subheader' => 'Use a header-only element to introduce the block that follows.',
                ],
            ],
            [
                'cType' => 'text',
                'name' => 'Text',
                'iconSlug' => 'text',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'A regular text element',
                    'bodytext' => '<p>A rich-text element with a heading and formatted body copy. '
                        . 'Use it for paragraphs, lists, links and inline emphasis such as <strong>bold</strong> '
                        . 'or <em>italic</em> text — the everyday building block of a page.</p>',
                ],
            ],
            [
                'cType' => 'textpic',
                'name' => 'Text & Images',
                'iconSlug' => 'textpic',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'Text wrapped around images',
                    'bodytext' => '<p>Body copy with one or more images arranged beside or around it. '
                        . 'Image position, columns and link behaviour are controlled per element.</p>',
                    'imageorient' => 25,
                    'image' => [
                        [
                            'file' => self::IMAGE_PRIMARY,
                            'title' => 'Focused work session',
                            'alternative' => 'A laptop open during a focused work session.',
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
                    'header' => 'Text wrapped around media',
                    'bodytext' => '<p>Like Text &amp; Images, but the media column also accepts video and '
                        . 'audio alongside images, with the same layout and gallery options.</p>',
                    'imageorient' => 26,
                    'assets' => [
                        [
                            'file' => self::IMAGE_SECONDARY,
                            'title' => 'Planning on a laptop',
                            'alternative' => 'Hands using a laptop while planning work on a desk.',
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
                    'header' => 'An image gallery',
                    'imagecols' => 3,
                    'image' => [
                        [
                            'file' => self::IMAGE_PRIMARY,
                            'title' => 'Focused work session',
                            'alternative' => 'A laptop open during a focused work session.',
                        ],
                        [
                            'file' => self::IMAGE_SECONDARY,
                            'title' => 'Planning on a laptop',
                            'alternative' => 'Hands using a laptop while planning work on a desk.',
                        ],
                        [
                            'file' => self::IMAGE_TERTIARY,
                            'title' => 'Collaborative workspace',
                            'alternative' => 'People working together around laptops.',
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
                    'header' => 'A bullet list',
                    'bodytext' => "Each line becomes one bullet point\n"
                        . "Great for short, scannable lists\n"
                        . "Keep items parallel and concise\n"
                        . 'No rich text — one idea per line',
                ],
            ],
            [
                'cType' => 'table',
                'name' => 'Table',
                'iconSlug' => 'table',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'A simple table',
                    'bodytext' => "Plan|Storage|Seats\nStarter|10 GB|3\nTeam|100 GB|10\nScale|1 TB|Unlimited",
                    'table_delimiter' => 124,
                    'table_enclosure' => 0,
                    'table_header_position' => 1,
                    'table_caption' => 'Example comparison of plan tiers.',
                ],
            ],
            [
                'cType' => 'uploads',
                'name' => 'File Links',
                'iconSlug' => 'uploads',
                'group' => 'content',
                'gateExtension' => null,
                'fixture' => [
                    'header' => 'Files for download',
                    'media' => [
                        [
                            'file' => self::IMAGE_PRIMARY,
                            'title' => 'Brand guidelines',
                            'alternative' => 'Downloadable brand guidelines file.',
                        ],
                        [
                            'file' => self::IMAGE_SECONDARY,
                            'title' => 'Product one-pager',
                            'alternative' => 'Downloadable product one-pager file.',
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
                        . "  <p>Raw, unprocessed HTML for embeds and snippets.</p>\n"
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
                    'header' => 'Reused content',
                ],
            ],

            // --- Menus ----------------------------------------------------
            [
                'cType' => 'menu_pages',
                'name' => 'Menu: Selected Pages',
                'iconSlug' => 'menu-pages',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Selected pages'],
            ],
            [
                'cType' => 'menu_subpages',
                'name' => 'Menu: Subpages of Selected Pages',
                'iconSlug' => 'menu-subpages',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Subpages'],
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
                'fixture' => ['header' => 'Sitemaps of selected pages'],
            ],
            [
                'cType' => 'menu_section',
                'name' => 'Menu: Section Index',
                'iconSlug' => 'menu-section',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Section index'],
            ],
            [
                'cType' => 'menu_section_pages',
                'name' => 'Menu: Section Index of Selected Pages',
                'iconSlug' => 'menu-section-pages',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Section index of selected pages'],
            ],
            [
                'cType' => 'menu_abstract',
                'name' => 'Menu: Abstracts',
                'iconSlug' => 'menu-abstract',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Abstracts'],
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
                'fixture' => ['header' => 'Categorized pages'],
            ],
            [
                'cType' => 'menu_categorized_content',
                'name' => 'Menu: Categorized Content',
                'iconSlug' => 'menu-categorized-content',
                'group' => 'navigation',
                'gateExtension' => null,
                'fixture' => ['header' => 'Categorized content'],
            ],

            // --- Form plugins (gated on their extension) ------------------
            [
                'cType' => 'form_formframework',
                'name' => 'Form',
                'iconSlug' => 'form',
                'group' => 'conversion',
                'gateExtension' => 'form',
                'fixture' => ['header' => 'A form'],
            ],
            [
                // felogin registers its plugin as CType "felogin_login" (v13+
                // ExtensionUtility::registerPlugin('Felogin', 'Login', …)).
                'cType' => 'felogin_login',
                'name' => 'Login Form',
                'iconSlug' => 'login',
                'group' => 'conversion',
                'gateExtension' => 'felogin',
                'fixture' => ['header' => 'Member login'],
            ],
            [
                'cType' => 'powermail_pi1',
                'name' => 'Form (Powermail)',
                'iconSlug' => 'powermail',
                'group' => 'conversion',
                'gateExtension' => 'powermail',
                'fixture' => ['header' => 'A Powermail form'],
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
            static fn (array $element): bool => $element['gateExtension'] === null
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
            static fn (array $element): bool => $element['gateExtension'] === null,
        ));
    }

    /**
     * All defined core CType identifiers (used for idempotent seed cleanup).
     *
     * @return list<string>
     */
    public static function cTypes(): array
    {
        return array_map(static fn (array $element): string => $element['cType'], self::all());
    }
}
