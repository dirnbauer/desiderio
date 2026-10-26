<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Theme;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsAllowedCallable;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * The menu colour and menu accent of the create page on ui.shadcn.com, for
 * Desiderio's menus (header dropdowns and flyouts, the mega menu, the
 * language and colour-scheme menus).
 *
 * The site settings desiderio.shadcn.menuColor and desiderio.shadcn.menuAccent
 * default to "preset": the values encoded in the active preset's create code
 * (b4hb38Fyj carries "inverted", b6G5977cw "default-translucent"). House
 * presets and "custom" are not create codes and resolve to shadcn's
 * defaults, "default" and "subtle". Any other value of a setting is taken as
 * an explicit choice.
 *
 * The resolved pair reaches the page as data-menu-color / data-menu-accent on
 * <body> (setup.typoscript, bodyTagCObject.40), next to data-shadcn-preset, so
 * a per-page preset override changes the menus along with everything else.
 */
final class MenuAppearance
{
    public const string PRESET = 'preset';

    /**
     * Desiderio's own presets (Build/Scripts/generate-shadcn-presets.php).
     * Some of their names are valid preset codes as far as the syntax goes
     * ("aurora", "bloom", "blossom": a version letter and base62 digits), and
     * "aurora" would decode to a translucent menu. They are no create codes;
     * NavigationMenusTest keeps this list in step with shadcn-theme.css.
     */
    public const array HOUSE_PRESETS = [
        'aurora', 'marine', 'forest', 'ember', 'bloom', 'lagoon', 'gold', 'midnight', 'blossom', 'citrus',
    ];

    private ?ContentObjectRenderer $cObj = null;

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }

    /**
     * @return array{menuColor: string, menuAccent: string}
     */
    public static function resolve(string $preset, string $menuColor = self::PRESET, string $menuAccent = self::PRESET): array
    {
        $preset = trim($preset);
        $decoded = in_array($preset, self::HOUSE_PRESETS, true) ? [] : (ShadcnPresetCode::decode($preset) ?? []);

        return [
            'menuColor' => self::pick($menuColor, ShadcnPresetCode::MENU_COLORS, $decoded['menuColor'] ?? null),
            'menuAccent' => self::pick($menuAccent, ShadcnPresetCode::MENU_ACCENTS, $decoded['menuAccent'] ?? null),
        ];
    }

    /**
     * USER cObject of the body tag. Configuration:
     *   preset      (stdWrap) the effective preset id of the page
     *   menuColor   (stdWrap) desiderio.shadcn.menuColor
     *   menuAccent  (stdWrap) desiderio.shadcn.menuAccent
     *
     * @param array<string, mixed> $conf
     */
    #[AsAllowedCallable]
    public function renderBodyAttributes(string $content, array $conf, ServerRequestInterface $request): string
    {
        $resolved = self::resolve(
            $this->value('preset', $conf),
            $this->value('menuColor', $conf),
            $this->value('menuAccent', $conf),
        );

        return sprintf(
            ' data-menu-color="%s" data-menu-accent="%s"',
            htmlspecialchars($resolved['menuColor'], ENT_QUOTES),
            htmlspecialchars($resolved['menuAccent'], ENT_QUOTES),
        );
    }

    /**
     * @param array<string, mixed> $conf
     */
    private function value(string $key, array $conf): string
    {
        if ($this->cObj !== null) {
            return trim((string)$this->cObj->stdWrapValue($key, $conf, ''));
        }
        $value = $conf[$key] ?? '';

        return is_scalar($value) ? trim((string)$value) : '';
    }

    /**
     * @param non-empty-list<string> $allowed
     */
    private static function pick(string $setting, array $allowed, ?string $fromPreset): string
    {
        $setting = trim($setting);
        if (in_array($setting, $allowed, true)) {
            return $setting;
        }
        if ($fromPreset !== null && in_array($fromPreset, $allowed, true)) {
            return $fromPreset;
        }

        return $allowed[0];
    }
}
