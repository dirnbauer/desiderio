<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Attribute\AsAllowedCallable;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings as CoreSiteSettings;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;
use Webconsulting\Desiderio\Theme\MenuAppearance;
use Webconsulting\Desiderio\Theme\ShadcnPresetCode;

/**
 * The three-level header menu, the home-page mega menu and the two menu
 * options of the create page on ui.shadcn.com (menu colour, menu accent).
 */
final class NavigationMenusTest extends TestCase
{
    private const string ROOT = __DIR__ . '/../..';

    /**
     * Codes and values as `npx shadcn@latest preset decode <code>` (shadcn
     * 4.22) reports them.
     *
     * @return iterable<string, array{0: string, 1: array<string, string>}>
     */
    public static function presetCodes(): iterable
    {
        yield 'b0' => ['b0', ['menuColor' => 'default', 'menuAccent' => 'subtle', 'style' => 'nova', 'baseColor' => 'neutral', 'iconLibrary' => 'lucide', 'radius' => 'default', 'font' => 'inter']];
        yield 'b3IWPgRwnI' => ['b3IWPgRwnI', ['menuColor' => 'default', 'menuAccent' => 'subtle', 'style' => 'mira', 'baseColor' => 'mist', 'iconLibrary' => 'phosphor', 'radius' => 'small', 'fontHeading' => 'jetbrains-mono']];
        yield 'b4hb38Fyj' => ['b4hb38Fyj', ['menuColor' => 'inverted', 'menuAccent' => 'subtle', 'style' => 'mira', 'baseColor' => 'olive', 'theme' => 'teal', 'font' => 'nunito-sans']];
        yield 'b6G5977cw' => ['b6G5977cw', ['menuColor' => 'default-translucent', 'menuAccent' => 'subtle', 'style' => 'lyra', 'baseColor' => 'olive', 'iconLibrary' => 'tabler', 'radius' => 'none', 'font' => 'jetbrains-mono']];
        yield 'b1FSk5ls0' => ['b1FSk5ls0', ['menuColor' => 'default', 'menuAccent' => 'bold', 'style' => 'nova', 'baseColor' => 'taupe', 'theme' => 'cyan']];
        yield 'b27GcrRo' => ['b27GcrRo', ['menuColor' => 'default', 'menuAccent' => 'subtle', 'style' => 'rhea', 'baseColor' => 'neutral']];
    }

    /**
     * @param array<string, string> $expected
     */
    #[DataProvider('presetCodes')]
    public function testCreatePresetCodesDecodeLikeShadcn(string $code, array $expected): void
    {
        $decoded = ShadcnPresetCode::decode($code);

        self::assertNotNull($decoded);
        foreach ($expected as $field => $value) {
            self::assertSame($value, $decoded[$field] ?? null, sprintf('%s: %s', $code, $field));
        }
        self::assertCount(10, $decoded, 'A "b" code carries ten fields.');
    }

    public function testVersionACodesCarryEightFieldsAndInheritTheHeadingFont(): void
    {
        $decoded = ShadcnPresetCode::decode('a0');

        self::assertNotNull($decoded);
        self::assertSame('default', $decoded['menuColor']);
        self::assertSame('inherit', $decoded['fontHeading']);
        self::assertArrayNotHasKey('chartColor', $decoded);
    }

    public function testJunkIsNoCode(): void
    {
        foreach (['lagoon', 'custom', '', 'b', 'c123', 'b0-x', 'b12345678901'] as $value) {
            self::assertFalse(ShadcnPresetCode::isCode($value), $value);
            self::assertNull(ShadcnPresetCode::decode($value), $value);
        }
    }

    public function testHousePresetsNeverDecodeAsCreateCodes(): void
    {
        // Syntactically "aurora" is a version-a code (shadcn's isPresetCode
        // says so too) and decodes to a translucent menu.
        self::assertTrue(ShadcnPresetCode::isCode('aurora'));
        self::assertSame('default-translucent', ShadcnPresetCode::decode('aurora')['menuColor'] ?? null);

        $theme = (string)file_get_contents(self::ROOT . '/Resources/Public/Css/shadcn-theme.css');
        self::assertSame(1, preg_match('/=== BEGIN Desiderio house presets.*?=== END Desiderio house presets ===/s', $theme, $section));
        preg_match_all('/^body\[data-shadcn-preset="([^"]+)"\]/m', $section[0], $house);
        self::assertSame($house[1], MenuAppearance::HOUSE_PRESETS, 'MenuAppearance::HOUSE_PRESETS must list the generated house presets.');

        foreach (MenuAppearance::HOUSE_PRESETS as $preset) {
            self::assertSame(['menuColor' => 'default', 'menuAccent' => 'subtle'], MenuAppearance::resolve($preset), $preset);
        }
    }

    public function testThePresetSuppliesTheMenuOptionsUnlessASettingChoosesThem(): void
    {
        self::assertSame(['menuColor' => 'inverted', 'menuAccent' => 'subtle'], MenuAppearance::resolve('b4hb38Fyj'));
        self::assertSame(['menuColor' => 'default-translucent', 'menuAccent' => 'subtle'], MenuAppearance::resolve('b6G5977cw', 'preset', 'preset'));
        self::assertSame(['menuColor' => 'default', 'menuAccent' => 'bold'], MenuAppearance::resolve('b1FSk5ls0'));
        // House presets are no create codes: shadcn's defaults.
        self::assertSame(['menuColor' => 'default', 'menuAccent' => 'subtle'], MenuAppearance::resolve('lagoon'));
        self::assertSame(['menuColor' => 'default', 'menuAccent' => 'subtle'], MenuAppearance::resolve(''));
        // An explicit choice wins over the preset, an unknown value falls back to it.
        self::assertSame(['menuColor' => 'inverted-translucent', 'menuAccent' => 'bold'], MenuAppearance::resolve('b4hb38Fyj', 'inverted-translucent', 'bold'));
        self::assertSame(['menuColor' => 'inverted', 'menuAccent' => 'subtle'], MenuAppearance::resolve('b4hb38Fyj', 'purple', '"><script>'));
    }

    public function testTheBodyAttributesAreEscapedAndTheUserFunctionIsAllowed(): void
    {
        $attributes = (new MenuAppearance())->renderBodyAttributes('', [
            'preset' => 'b6G5977cw',
            'menuColor' => 'preset',
            'menuAccent' => 'bold',
        ], self::createStub(ServerRequestInterface::class));

        self::assertSame(' data-menu-color="default-translucent" data-menu-accent="bold"', $attributes);

        $method = new \ReflectionMethod(MenuAppearance::class, 'renderBodyAttributes');
        self::assertNotEmpty($method->getAttributes(AsAllowedCallable::class), 'TYPO3 v14 refuses a TypoScript userFunc without #[AsAllowedCallable].');
    }

    public function testSettingsAndBodyTagCarryTheMenuOptions(): void
    {
        $definitions = Yaml::parseFile(self::ROOT . '/Configuration/Sets/Desiderio/settings.definitions.yaml');
        self::assertIsArray($definitions);
        $settings = $definitions['settings'];
        self::assertIsArray($settings);

        self::assertSame('preset', $settings['desiderio.shadcn.menuColor']['default']);
        self::assertSame(['preset', ...ShadcnPresetCode::MENU_COLORS], array_keys($settings['desiderio.shadcn.menuColor']['enum']));
        self::assertSame('preset', $settings['desiderio.shadcn.menuAccent']['default']);
        self::assertSame(['preset', ...ShadcnPresetCode::MENU_ACCENTS], array_keys($settings['desiderio.shadcn.menuAccent']['enum']));
        self::assertSame('off', $settings['desiderio.navigation.megaMenu']['default']);
        self::assertSame(['off', 'home'], array_keys($settings['desiderio.navigation.megaMenu']['enum']));

        $labels = (string)file_get_contents(self::ROOT . '/Configuration/Sets/Desiderio/labels.xlf');
        $germanLabels = (string)file_get_contents(self::ROOT . '/Configuration/Sets/Desiderio/de.labels.xlf');
        foreach (['desiderio.shadcn.menuColor', 'desiderio.shadcn.menuAccent', 'desiderio.navigation.megaMenu'] as $key) {
            foreach (['settings.' . $key, 'settings.description.' . $key] as $unit) {
                self::assertStringContainsString('<unit id="' . $unit . '">', $labels);
                self::assertStringContainsString('<unit id="' . $unit . '">', $germanLabels);
            }
            foreach (array_keys($settings[$key]['enum']) as $value) {
                self::assertStringContainsString('<unit id="settings.' . $key . '.' . $value . '">', $labels);
            }
        }

        $setup = (string)file_get_contents(self::ROOT . '/Configuration/Sets/Desiderio/setup.typoscript');
        self::assertStringContainsString('userFunc = Webconsulting\Desiderio\Theme\MenuAppearance->renderBodyAttributes', $setup);
        self::assertStringContainsString('menuColor = {$desiderio.shadcn.menuColor}', $setup);
        self::assertStringContainsString('menuAccent = {$desiderio.shadcn.menuAccent}', $setup);
    }

    public function testTheMainMenuHasThreeLevelsWithFlyoutDisclosures(): void
    {
        $typoScript = (string)file_get_contents(self::ROOT . '/Configuration/Sets/Desiderio/TypoScript/page.typoscript');
        self::assertMatchesRegularExpression('/as = mainMenu.*?levels = 3/s', $typoScript);

        $header = (string)file_get_contents(self::ROOT . '/Resources/Private/Components/Organism/SiteHeader/SiteHeader.fluid.html');
        self::assertStringContainsString('data-d-nav-sub data-d-nav-level="2"', $header);
        self::assertStringContainsString('aria-controls="desiderio-subnav-{navIteration.cycle}-{subIteration.cycle}"', $header);
        self::assertStringContainsString('id="desiderio-subnav-{navIteration.cycle}-{subIteration.cycle}"', $header);
        self::assertStringContainsString('<f:for each="{subitem.children}" as="leaf">', $header);
        self::assertStringContainsString('{f:if(condition: \'{leaf.current}\', then: \'{aria-current: \\\'page\\\'}\')}', $header);
        // Every popover is a menu surface, so menu colour and accent reach it.
        self::assertSame(3, substr_count($header, 'd-menu-surface--wide d-menu-popover'));
        self::assertSame(2, substr_count($header, 'd-menu-surface d-menu-popover'));
    }

    public function testTheMegaMenuReplacesTheMainMenuOnTheHomePageOnly(): void
    {
        $layout = (string)file_get_contents(self::ROOT . '/Resources/Private/Templates/Layouts/Pages/Default.fluid.html');
        self::assertStringContainsString('<f:variable name="megaMenu" value="{false}"/>', $layout);
        self::assertStringContainsString(
            '<f:if condition="{site.configuration.settings.desiderio.navigation.megaMenu} == \'home\' && {page.id} == {site.rootPageId}">',
            $layout,
        );
        self::assertStringContainsString('megaMenu="{megaMenu}"/>', $layout);
        self::assertStringNotContainsString('<d:organism.megaMenu', $layout, 'The header renders the mega menu, the layout only decides.');

        // In the header's main-navigation landmark, instead of the list.
        $header = (string)file_get_contents(self::ROOT . '/Resources/Private/Components/Organism/SiteHeader/SiteHeader.fluid.html');
        self::assertStringContainsString('<f:argument name="megaMenu" type="bool" optional="{true}" default="{false}" />', $header);
        self::assertStringContainsString('<f:if condition="{mainMenu} && {megaMenu}">', $header);
        self::assertStringContainsString('<d:organism.megaMenu mainMenu="{mainMenu}" ctaSlug="{ctaSlug}"/>', $header);
        self::assertStringContainsString('<f:if condition="{mainMenu} && !{megaMenu}">', $header);
        self::assertSame(1, substr_count($header, '<nav id="desiderio-main-nav"'));

        $mega = (string)file_get_contents(self::ROOT . '/Resources/Private/Components/Organism/MegaMenu/MegaMenu.fluid.html');
        self::assertStringContainsString('<ul class="desiderio-mega__list" role="list" data-d-mega-menu>', $mega);
        self::assertStringNotContainsString('<nav', $mega, 'One landmark: the header\'s main navigation.');
        // Disclosures: button, aria-expanded, aria-controls on the panel id.
        self::assertSame(1, substr_count($mega, 'aria-expanded="false"'));
        self::assertStringContainsString('aria-controls="desiderio-mega-panel-{megaIteration.cycle}"', $mega);
        self::assertStringContainsString('id="desiderio-mega-panel-{megaIteration.cycle}"', $mega);
        self::assertStringContainsString('{subitem.data.abstract}', $mega);
        self::assertStringContainsString('d-menu-surface--wide d-menu-popover', $mega);
        self::assertStringNotContainsString('nav.mega.note', $mega, 'The note lives in the home page content, not in the menu.');
    }

    public function testTheHomePageNoteFollowsTheProductFilmOnlyWithTheMegaMenu(): void
    {
        self::assertSame(StyleguideShowcasePages::homeContent(), StyleguideShowcasePages::homeContentFor(false));

        $withNote = StyleguideShowcasePages::homeContentFor(true);
        self::assertCount(count(StyleguideShowcasePages::homeContent()) + 1, $withNote);
        $types = array_column($withNote, 'ctype');
        $film = array_search('desiderio_featurevideo', $types, true);
        self::assertIsInt($film);
        self::assertSame(StyleguideShowcasePages::megaMenuNote(), $withNote[$film + 1]);
        self::assertSame('desiderio_alert', $withNote[$film + 1]['ctype']);
        self::assertSame('info', $withNote[$film + 1]['fields']['icon']);
    }

    public function testOnlyASiteThatShowsTheMegaMenuOnItsRootPageGetsTheNote(): void
    {
        $site = static fn(mixed $megaMenu): Site => new Site('desiderio', 505, ['base' => '/'], CoreSiteSettings::createFromSettingsTree(
            $megaMenu === null ? [] : ['desiderio' => ['navigation' => ['megaMenu' => $megaMenu]]]
        ));

        self::assertTrue(StyleguideShowcasePages::showsMegaMenuNote($site('home'), 505));
        self::assertFalse(StyleguideShowcasePages::showsMegaMenuNote($site('off'), 505));
        self::assertFalse(StyleguideShowcasePages::showsMegaMenuNote($site(null), 505), 'No setting: the default is off.');
        self::assertFalse(StyleguideShowcasePages::showsMegaMenuNote($site('home'), 600), 'Seeded below the root page: no mega menu there.');

        $command = (string)file_get_contents(self::ROOT . '/Classes/Command/SeedStyleguidePagesCommand.php');
        self::assertStringContainsString('StyleguideShowcasePages::homeContentFor($this->showsMegaMenuNote($parentPid))', $command);
        self::assertStringContainsString('getSiteByPageId($homePageUid)', $command);
    }

    public function testTheInvertedMenuColoursAreGeneratedFromTheThemeStylesheet(): void
    {
        $script = self::ROOT . '/Build/Scripts/build-menu-surface-tokens.php';
        exec(PHP_BINARY . ' ' . escapeshellarg($script) . ' --check 2>&1', $output, $exitCode);
        self::assertSame(0, $exitCode, implode("\n", $output));

        $tokens = (string)file_get_contents(self::ROOT . '/Resources/Private/Css/desiderio/26-menu-surface-tokens.css');
        $theme = (string)file_get_contents(self::ROOT . '/Resources/Public/Css/shadcn-theme.css');
        preg_match_all('/^body\[data-shadcn-preset="([^"]+)"\]\s*\{/m', $theme, $presets);
        self::assertNotEmpty($presets[1]);
        foreach ($presets[1] as $preset) {
            self::assertStringContainsString('/* ' . $preset, $tokens, $preset . ' has no inverted menu colours.');
        }

        $manifest = (string)file_get_contents(self::ROOT . '/Resources/Private/Css/desiderio/manifest.txt');
        self::assertMatchesRegularExpression('/01-menu-surface\.css\n01-header\.css/', $manifest, 'Menu surfaces must load before the header, whose inline contexts override them.');
        self::assertStringContainsString("25-mega-menu.css\n26-menu-surface-tokens.css", $manifest);
    }
}
