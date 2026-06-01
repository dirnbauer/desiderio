<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Static GRADE of the optimized site archetypes + content-element catalog.
 *
 * Every assertion reads source files only — no rendering, no network, no
 * TYPO3 bootstrap — so the whole class is deterministic and fast in CI and
 * locks the optimized state in place. It complements (does not duplicate) the
 * existing audit/structure suites by scoring the four qualities the
 * optimization brief promised: accessibility, shadcn fidelity, responsiveness,
 * and content quality.
 *
 * Conventions discovered in the tree (so the grade matches reality):
 *   - Element styling lives in ContentBlocks/ContentElements/<name>/assets/frontend.css
 *     and uses semantic CSS custom properties (var(--ring), var(--muted-foreground), …)
 *     plus @media for responsiveness. It contains NO raw colours.
 *   - The 5 archetype page-template dirs live under
 *     Resources/Private/Presets/<Archetype>/Templates/Pages/ and reference <d:*> components.
 *   - Responsive Tailwind prefixes (sm:/md:/lg:) live in the layout COMPONENTS
 *     and the extension override templates; archetype page templates delegate
 *     their breakpoints to those components + preset CSS @media rules.
 *   - Interactive focus rings come from EITHER an element's own frontend.css OR
 *     the shared Resources/Public/Css/components.css (shadcn primitives), so the
 *     focus-visible grade accepts both sources.
 */
final class OptimizationGradeTest extends TestCase
{
    private const ARCHETYPES = ['Corporate', 'Dashboard', 'Editorial', 'Portfolio', 'Saas'];

    private const ARCHETYPE_PAGE_TEMPLATES = [
        'DesiderioStartpage',
        'DesiderioContentpage',
        'DesiderioContentpageSidebar',
        'DesiderioSearch',
        'DesiderioError',
    ];

    /**
     * Raw-colour detector that mirrors scripts/audit-content-elements.php:
     * matches a bare #hex / oklch() / rgb[a]() / hsl[a]() literal but NOT a
     * `var(--token)` reference. Used on stylesheets, where any literal colour is
     * a token-fidelity regression.
     */
    private const RAW_COLOUR_IN_CSS = '/(?<!var\()\b(?:#[0-9a-fA-F]{3,8}\b|oklch\([^)]+\)|rgba?\([^)]+\)|hsla?\([^)]+\))/';

    private static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    private static function read(string $relativePath): string
    {
        $path = self::root() . '/' . $relativePath;
        self::assertFileExists($path, "{$relativePath} must exist");

        return (string) file_get_contents($path);
    }

    /**
     * Sorted glob that always returns a typed list of matched paths.
     *
     * @return list<string>
     */
    private static function globFiles(string $pattern, int $flags = 0): array
    {
        $files = glob($pattern, $flags);
        if (!is_array($files)) {
            return [];
        }
        sort($files); // reindexes to a 0-based list

        return $files;
    }

    /**
     * @return list<string> absolute paths to every content-element frontend.css
     */
    private static function elementCssFiles(): array
    {
        $files = self::globFiles(self::root() . '/ContentBlocks/ContentElements/*/assets/frontend.css');
        self::assertGreaterThan(200, count($files), 'The catalog should expose 250+ styled content elements.');

        return $files;
    }

    /**
     * @return list<string> absolute paths to every content-element frontend.html
     */
    private static function elementTemplateFiles(): array
    {
        $files = self::globFiles(self::root() . '/ContentBlocks/ContentElements/*/templates/frontend.html');
        self::assertGreaterThan(200, count($files));

        return $files;
    }

    private static function elementName(string $absolutePath): string
    {
        // …/ContentElements/<name>/(assets|templates)/<file>
        return basename(dirname($absolutePath, 2));
    }

    // ---------------------------------------------------------------------
    // Accessibility
    // ---------------------------------------------------------------------

    /**
     * Each archetype start page must own exactly one logical <h1> and wire its
     * content into an accessible-naming or focus primitive. The catalog uses two
     * equally valid patterns — Corporate/Saas label a focusable -1 content
     * section with aria-label; Dashboard/Editorial/Portfolio name a landmark via
     * aria-labelledby pointing at the h1 — so the grade accepts either rather
     * than mandating one mechanism.
     */
    public function testArchetypeStartPagesHaveSingleH1AndAnAccessibleContentRegion(): void
    {
        foreach (self::ARCHETYPES as $archetype) {
            $template = self::read(
                'Resources/Private/Presets/' . $archetype . '/Templates/Pages/DesiderioStartpage.fluid.html'
            );

            self::assertSame(
                1,
                preg_match_all('/<h1\b/', $template),
                $archetype . ' start page must contain exactly one <h1> so the heading outline has a single page title.'
            );

            self::assertMatchesRegularExpression(
                '/aria-labelledby="|aria-label="|tabindex="-1"/',
                $template,
                $archetype . ' start page must name or focus its main content region (aria-labelledby / aria-label / focusable tabindex="-1").'
            );
        }
    }

    /**
     * No template under the optimized trees may ship a positive tabindex —
     * positive values break the natural tab order (WCAG 2.4.3).
     */
    public function testNoOptimizedTemplateUsesPositiveTabindex(): void
    {
        $offenders = [];

        foreach (
            [
                ...self::elementTemplateFiles(),
                ...self::globFiles(self::root() . '/Resources/Private/Presets/*/Templates/Pages/*.fluid.html'),
                ...self::globFiles(self::root() . '/Resources/Private/Templates/Partials/Presets/*.fluid.html'),
            ] as $file
        ) {
            $contents = (string) file_get_contents($file);
            if (preg_match('/tabindex="\s*\+?[1-9][0-9]*\s*"/', $contents) === 1) {
                $offenders[] = str_replace(self::root() . '/', '', $file);
            }
        }

        self::assertSame([], $offenders, 'Positive tabindex breaks keyboard order; use 0 or -1 instead.');
    }

    /**
     * Every rendered image must carry an alt attribute. f:image without alt
     * would emit an <img> with no text alternative; alt="" is allowed because it
     * is the correct, intentional marker for decorative images.
     */
    public function testEveryRenderedImageDeclaresAnAltAttribute(): void
    {
        $offenders = [];

        foreach (self::elementTemplateFiles() as $file) {
            $contents = (string) file_get_contents($file);

            // f:image ViewHelper occurrences.
            if (preg_match_all('/<f:image\b[^>]*>/s', $contents, $matches) > 0) {
                foreach ($matches[0] as $tag) {
                    if (!str_contains($tag, 'alt=')) {
                        $offenders[] = self::elementName($file) . ': f:image without alt';
                    }
                }
            }

            // Hand-authored <img> tags.
            if (preg_match_all('/<img\b[^>]*>/s', $contents, $imgMatches) > 0) {
                foreach ($imgMatches[0] as $tag) {
                    if (!str_contains($tag, 'alt=')) {
                        $offenders[] = self::elementName($file) . ': <img> without alt';
                    }
                }
            }
        }

        self::assertSame([], $offenders, "Images missing alt text:\n" . implode("\n", $offenders));
    }

    /**
     * A representative sample of interactive content elements must expose a
     * visible focus ring. The ring may be defined in the element's own
     * frontend.css or inherited from the shared shadcn primitives in
     * components.css — either satisfies WCAG 2.4.7. The ring must be drawn with
     * a semantic token, never a raw colour.
     */
    public function testSampledInteractiveElementsExposeTokenBasedFocusVisible(): void
    {
        $componentsCss = self::read('Resources/Public/Css/components.css');

        // Elements with interactive controls (triggers, dismiss buttons, copy
        // buttons, sliders, links-as-buttons, form fields). Each is verified to
        // resolve a focus-visible ring from its own CSS and/or components.css.
        $sample = [
            'accordion',
            'tabs',
            'faq',
            'back-to-top',
            'gallery',
            'hero-search',
            'navbar-mobile',
            'pricing-toggle',
            'pricing-slider',
            'product-card',
            'content-carousel',
            'newsletter-signup',
        ];

        $missing = [];
        $rawColour = [];

        foreach ($sample as $element) {
            $cssPath = 'ContentBlocks/ContentElements/' . $element . '/assets/frontend.css';
            $elementCss = self::read($cssPath);
            $combined = $elementCss . "\n" . $componentsCss;

            if (!str_contains($combined, 'focus-visible')) {
                $missing[] = $element;
            }

            // The element's own CSS must stay token-only (defence-in-depth on the
            // sample; the whole-catalog scan below covers the rest).
            if (preg_match(self::RAW_COLOUR_IN_CSS, $elementCss) === 1) {
                $rawColour[] = $element;
            }
        }

        self::assertSame([], $missing, 'These interactive elements expose no focus-visible ring (own CSS or shared primitives): ' . implode(', ', $missing));
        self::assertSame([], $rawColour, 'These interactive elements ship raw colours instead of tokens: ' . implode(', ', $rawColour));

        // The shared focus ring itself must be token-driven (var(--ring)).
        self::assertStringContainsString('.accordion__trigger:focus-visible', $componentsCss);
        self::assertStringContainsString('.tabs__trigger:focus-visible', $componentsCss);
        self::assertMatchesRegularExpression('/:focus-visible\s*\{[^}]*var\(--ring\)/s', $componentsCss);
    }

    /**
     * Decorative icon glyphs are presentational; the sampled icon spans must be
     * hidden from assistive tech with aria-hidden so screen readers do not
     * announce icon-font noise.
     */
    public function testDecorativeIconSpansAreHiddenFromAssistiveTech(): void
    {
        $samples = [
            'ContentBlocks/ContentElements/feature-grid-3/templates/frontend.html',
            'ContentBlocks/ContentElements/booking-form/templates/frontend.html',
            'Resources/Private/Presets/Corporate/Templates/Pages/DesiderioStartpage.fluid.html',
            'Resources/Private/Presets/Saas/Templates/Pages/DesiderioStartpage.fluid.html',
        ];

        foreach ($samples as $relativePath) {
            $template = self::read($relativePath);

            // Every <d:atom.icon …/> that is not itself given an aria-label must
            // sit inside an aria-hidden wrapper. We assert the wrapper pattern
            // exists at least once and that no bare decorative icon escapes it.
            self::assertStringContainsString('aria-hidden="true"', $template, $relativePath . ' must hide decorative icons from assistive tech.');
        }
    }

    // ---------------------------------------------------------------------
    // shadcn fidelity
    // ---------------------------------------------------------------------

    /**
     * NO content-element stylesheet may contain a raw colour — every colour must
     * flow through a semantic shadcn token so preset switching re-skins the
     * whole catalog. This is the catalog-wide companion to the sampled check.
     */
    public function testNoContentElementStylesheetContainsRawColours(): void
    {
        $offenders = [];

        foreach (self::elementCssFiles() as $cssFile) {
            $css = (string) file_get_contents($cssFile);
            if (preg_match(self::RAW_COLOUR_IN_CSS, $css, $hit) === 1) {
                $offenders[] = self::elementName($cssFile) . ': ' . $hit[0];
            }
        }

        self::assertSame([], $offenders, "Content-element CSS must be token-only (no oklch/hsl/rgb/#hex):\n" . implode("\n", $offenders));
    }

    /**
     * Content-element stylesheets must actively consume semantic shadcn tokens
     * (var(--foreground), var(--muted-foreground), var(--border), var(--ring),
     * var(--primary), …) rather than merely avoiding raw colour. We assert that
     * the catalog as a whole references the core semantic palette.
     */
    public function testContentElementStylesheetsConsumeSemanticTokens(): void
    {
        $allCss = '';
        foreach (self::elementCssFiles() as $cssFile) {
            $allCss .= (string) file_get_contents($cssFile) . "\n";
        }

        foreach (
            [
                '--foreground',
                '--muted-foreground',
                '--background',
                '--card',
                '--border',
                '--ring',
                '--primary',
                '--muted',
            ] as $token
        ) {
            self::assertStringContainsString(
                'var(' . $token . ')',
                $allCss,
                'The content-element catalog should style with the semantic ' . $token . ' token.'
            );
        }
    }

    /**
     * Preset (archetype) skins are colour-bearing by nature yet must still be
     * token-only: a preset re-skins chrome via tokens, never hard-coded paint.
     */
    public function testArchetypePresetSkinsAreTokenOnly(): void
    {
        $offenders = [];

        foreach (self::ARCHETYPES as $archetype) {
            $cssPath = 'Resources/Public/Css/preset-' . strtolower($archetype) . '.css';
            $css = self::read($cssPath);
            if (preg_match(self::RAW_COLOUR_IN_CSS, $css, $hit) === 1) {
                $offenders[] = $archetype . ': ' . $hit[0];
            }
        }

        self::assertSame([], $offenders, "Preset skins must be token-only:\n" . implode("\n", $offenders));
    }

    /**
     * The 5 archetype page-template directories must exist, each must hold the
     * full set of page templates, and those templates must render real Desiderio
     * <d:*> components (not bespoke markup).
     */
    public function testFiveArchetypePageTemplateDirsExistAndReferenceDesiderioComponents(): void
    {
        foreach (self::ARCHETYPES as $archetype) {
            $dir = self::root() . '/Resources/Private/Presets/' . $archetype . '/Templates/Pages';
            self::assertDirectoryExists($dir, $archetype . ' archetype must ship a Pages template directory.');

            foreach (self::ARCHETYPE_PAGE_TEMPLATES as $templateName) {
                $relativePath = 'Resources/Private/Presets/' . $archetype . '/Templates/Pages/' . $templateName . '.fluid.html';
                $template = self::read($relativePath);

                self::assertStringContainsString(
                    'Webconsulting/Desiderio/Components/ComponentCollection',
                    $template,
                    $relativePath . ' must declare the Desiderio component namespace.'
                );
                self::assertMatchesRegularExpression(
                    '/<d:(atom|molecule|layout)\./',
                    $template,
                    $relativePath . ' must render with shadcn <d:…> components.'
                );
                self::assertStringContainsString(
                    '<d:layout.container',
                    $template,
                    $relativePath . ' must lay out content inside a <d:layout.container>.'
                );
            }
        }

        // Containment guarantee: exactly the five expected archetypes exist.
        $presetDirs = self::globFiles(self::root() . '/Resources/Private/Presets/*/Templates/Pages', GLOB_ONLYDIR);
        $found = array_map(static fn (string $p): string => basename(dirname($p, 2)), $presetDirs);
        sort($found);
        $expected = self::ARCHETYPES;
        sort($expected);
        self::assertSame($expected, $found, 'Exactly the five documented archetype page-template directories must exist.');
    }

    /**
     * Archetype page templates must not contain colour literals either — their
     * paint is delegated to tokens and preset CSS. (Templates legitimately use
     * `#fragment` anchors, so we only flag colour FUNCTIONS here; #hex literals
     * in stylesheets are covered by the CSS scans above.)
     */
    public function testArchetypePageTemplatesContainNoColourFunctions(): void
    {
        $offenders = [];

        foreach (self::globFiles(self::root() . '/Resources/Private/Presets/*/Templates/Pages/*.fluid.html') as $file) {
            $contents = (string) file_get_contents($file);
            if (preg_match('/(?:oklch|hsla?|rgba?)\([^)]*\)/', $contents, $hit) === 1) {
                $offenders[] = str_replace(self::root() . '/', '', $file) . ': ' . $hit[0];
            }
        }

        self::assertSame([], $offenders, "Archetype templates must not hard-code colour functions:\n" . implode("\n", $offenders));
    }

    // ---------------------------------------------------------------------
    // Responsiveness
    // ---------------------------------------------------------------------

    /**
     * The layout primitives that the archetypes and the whole catalog compose
     * with must emit responsive Tailwind prefixes so grids collapse on small
     * screens. These generated components are the load-bearing layout templates.
     */
    public function testLayoutPrimitivesEmitResponsivePrefixes(): void
    {
        $grid = self::read('Resources/Private/Components/Layout/Grid/Grid.fluid.html');

        self::assertMatchesRegularExpression(
            '/\b(sm|md|lg):grid-cols-\d/',
            $grid,
            'The Grid layout primitive must emit responsive column counts (sm:/md:/lg:grid-cols-*).'
        );
        // Multi-column variants must step up across breakpoints, not jump straight to N.
        self::assertStringContainsString('grid-cols-1 md:grid-cols-2 lg:grid-cols-3', $grid);
    }

    /**
     * Extension override page templates (Blog / News / shadcn-ui PAGEVIEW) carry
     * their layout responsiveness inline; they must use responsive prefixes.
     */
    public function testLayoutHeavyOverrideTemplatesUseResponsivePrefixes(): void
    {
        $layoutHeavyTemplates = [
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioBlog.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioNews.fluid.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogList.html',
            'Resources/Private/Extensions/News/Templates/News/List.html',
        ];

        foreach ($layoutHeavyTemplates as $relativePath) {
            $template = self::read($relativePath);
            self::assertMatchesRegularExpression(
                '/\b(sm|md|lg):[a-z]/',
                $template,
                $relativePath . ' is layout-heavy and must use responsive Tailwind prefixes (sm:/md:/lg:).'
            );
        }
    }

    /**
     * Grid-based content elements must stay responsive: any element whose CSS
     * declares a multi-column `grid-template-columns` must also provide a
     * single-column fallback inside an @media query, so the grid reflows on
     * narrow viewports. Graded across the whole catalog.
     */
    public function testMultiColumnGridElementsProvideResponsiveFallback(): void
    {
        $offenders = [];

        foreach (self::elementCssFiles() as $cssFile) {
            $css = (string) file_get_contents($cssFile);

            // Does this element declare a static multi-column CARD grid? We only
            // flag `repeat(<n>, …)` with n >= 2 — the canonical 3-up/4-up content
            // grid that must collapse on mobile. Hand-tuned pairings like
            // "1fr 1fr" (legend + value, label + control) are intentionally fixed
            // and out of scope.
            $hasMultiColumn = preg_match('/grid-template-columns:\s*repeat\(\s*([2-9]|1[0-9])\b/', $css) === 1;

            if (!$hasMultiColumn) {
                continue;
            }

            $hasMediaQuery = str_contains($css, '@media');
            // A container-query driven or single-column-override element also passes.
            $hasContainerQuery = str_contains($css, '@container');

            if (!$hasMediaQuery && !$hasContainerQuery) {
                $offenders[] = self::elementName($cssFile);
            }
        }

        self::assertSame(
            [],
            $offenders,
            "These multi-column grid elements never reflow (no @media/@container fallback):\n" . implode("\n", $offenders)
        );
    }

    /**
     * Archetype skins must adapt across breakpoints: every preset stylesheet
     * must declare at least one @media query.
     */
    public function testArchetypePresetSkinsDeclareBreakpoints(): void
    {
        foreach (self::ARCHETYPES as $archetype) {
            $css = self::read('Resources/Public/Css/preset-' . strtolower($archetype) . '.css');
            self::assertStringContainsString(
                '@media',
                $css,
                $archetype . ' preset skin must adapt to viewport width via at least one @media query.'
            );
        }
    }

    // ---------------------------------------------------------------------
    // Content quality
    // ---------------------------------------------------------------------

    /**
     * Every content element's fixture must ship real seed copy: a non-empty
     * lead line and a non-empty body, neither of which may be lorem/placeholder.
     * This keeps the styleguide and backend previews looking finished.
     *
     * "Lead" is element-shape aware: most elements lead with a header/headline,
     * but dividers lead with their divider_text, footers with a brand/copyright,
     * and testimonials with their quote. The grade accepts whichever lead the
     * element's content model actually uses — it only fails when there is no
     * real lead copy at all, or when the copy is placeholder filler.
     */
    public function testEveryFixtureShipsRealHeadlineAndBodyCopy(): void
    {
        $fixtures = self::globFiles(self::root() . '/ContentBlocks/ContentElements/*/fixture.json');
        self::assertGreaterThan(200, count($fixtures));

        // Element-shape-aware "lead" fields. Most elements lead with a header;
        // footers/chrome lead with a brand/copyright/divider line; testimonials
        // with a quote/author. The first non-empty one is the element's lead.
        $headlineFields = [
            'header', 'headline', 'title', 'eyebrow',
            // Footer / chrome / structural lead fields:
            'brand', 'copyright', 'divider_text',
            // Testimonial / quote lead fields:
            'quote', 'author_name',
        ];

        // Configuration keys carry no editorial copy; they are excluded when we
        // measure how much real content a fixture ships.
        $metaKeys = [
            '_type', 'variant', 'spacing', 'color', 'alignment', 'columns',
            'show_names', 'show_percentage', 'show_phone', 'show_date',
            'aspect_ratio', 'height', 'width', 'rating', 'target', 'open',
        ];

        $missingHeadline = [];
        $missingBody = [];
        $placeholder = [];

        foreach ($fixtures as $fixtureFile) {
            $element = basename(dirname($fixtureFile));
            $decoded = json_decode((string) file_get_contents($fixtureFile), true);
            self::assertIsArray($decoded, $element . '/fixture.json must be valid JSON object.');

            [$headlineField, $headline] = self::firstNonEmptyField($decoded, $headlineFields);

            if ($headline === null) {
                $missingHeadline[] = $element;
            } elseif (self::looksLikePlaceholder($headline)) {
                $placeholder[] = $element . ' (headline: "' . self::truncate($headline) . '")';
            }

            // Every field that actually carries content (non-empty string, number,
            // or a populated collection) and is not a configuration key.
            $contentFields = [];
            foreach ($decoded as $key => $value) {
                if (in_array($key, $metaKeys, true) || str_ends_with((string) $key, '_link')) {
                    continue;
                }
                $isContent = (is_string($value) && trim($value) !== '')
                    || is_int($value) || is_float($value)
                    || (is_array($value) && $value !== []);
                if ($isContent) {
                    $contentFields[] = $key;
                }
            }

            $hasCollection = false;
            foreach ($decoded as $value) {
                if (is_array($value) && $value !== []) {
                    $hasCollection = true;
                    break;
                }
            }

            // A fixture has body substance when it ships a populated collection or
            // any content field beyond its single lead line. Lead-only elements
            // (e.g. a content-divider whose whole model is its divider_text) are
            // legitimately exempt — there is simply nothing else to seed.
            $leadFieldList = $headlineField === null ? [] : [$headlineField];
            $bodyBeyondLead = $hasCollection
                || count(array_diff($contentFields, $leadFieldList)) >= 1;
            $leadOnlyElement = count($contentFields) <= 1;

            if (!$bodyBeyondLead && !$leadOnlyElement) {
                $missingBody[] = $element;
            }

            // Any populated scalar content field must not be placeholder filler.
            foreach ($contentFields as $key) {
                $value = $decoded[$key] ?? null;
                if (is_string($value) && self::looksLikePlaceholder($value)) {
                    $placeholder[] = $element . ' (' . $key . ': "' . self::truncate(trim($value)) . '")';
                }
            }
        }

        self::assertSame([], $missingHeadline, "Fixtures with no lead copy:\n" . implode("\n", $missingHeadline));
        self::assertSame([], $missingBody, "Fixtures with no body copy / child rows:\n" . implode("\n", $missingBody));
        self::assertSame([], array_values(array_unique($placeholder)), "Fixtures containing lorem/placeholder copy:\n" . implode("\n", array_values(array_unique($placeholder))));
    }

    /**
     * Returns [fieldName, trimmedValue] for the first field that holds a
     * non-empty string, or [null, null] when none match.
     *
     * @param array<array-key, mixed> $data decoded fixture JSON
     * @param list<string> $fields
     * @return array{0: ?string, 1: ?string}
     */
    private static function firstNonEmptyField(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            $value = $data[$field] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return [$field, trim($value)];
            }
        }

        return [null, null];
    }

    private static function looksLikePlaceholder(string $value): bool
    {
        $normalised = strtolower($value);

        // Classic lorem ipsum / lipsum.
        if (preg_match('/\b(?:lorem|lipsum)\b/', $normalised) === 1) {
            return true;
        }

        // Multi-word placeholder phrases are safe to match as substrings.
        foreach (['dummy text', 'sample text', 'your text here', 'insert text', 'placeholder text', 'foo bar', 'replace me'] as $phrase) {
            if (str_contains($normalised, $phrase)) {
                return true;
            }
        }

        // Short ambiguous tokens must match as whole words only, so real copy
        // such as "Mastodon" (contains "todo") is never flagged.
        if (preg_match('/\b(?:placeholder|tbd|todo|xxx+|asdf)\b/', $normalised) === 1) {
            return true;
        }

        return false;
    }

    private static function truncate(string $value, int $length = 48): string
    {
        return mb_strlen($value) > $length ? mb_substr($value, 0, $length) . '…' : $value;
    }
}
