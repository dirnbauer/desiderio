<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Icon;

final class IconRegistry
{
    public const DEFAULT_LIBRARY = 'tabler';

    /**
     * @return list<string>
     */
    public static function supportedLibraries(): array
    {
        return ['lucide', 'tabler', 'phosphor'];
    }

    public static function isSupportedLibrary(string $library): bool
    {
        return in_array($library, self::supportedLibraries(), true);
    }

    public static function normalizeLibrary(string $library): string
    {
        $library = strtolower(trim($library));

        return self::isSupportedLibrary($library) ? $library : self::DEFAULT_LIBRARY;
    }

    public static function libraryForPreset(string $preset): ?string
    {
        return match ($preset) {
            'b0' => 'lucide',
            'b3IWPgRwnI', 'b4hb38Fyj' => 'phosphor',
            'b6G5977cw' => 'tabler',
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    public static function demoKeys(): array
    {
        return [
            'sparkles',
            'shield-check',
            'chart-no-axes-combined',
            'users',
            'rocket',
            'database',
            'settings',
            'book-open',
            'check-circle',
            'clock',
            'globe',
            'map-pin',
        ];
    }

    /**
     * @return list<array{value: string, label: string, group: string}>
     */
    public static function selectItems(): array
    {
        $items = [];
        foreach (self::icons() as $value => $icon) {
            $items[] = [
                'value' => $value,
                'label' => $icon['label'],
                'group' => $icon['group'],
            ];
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::icons());
    }

    public static function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $aliases = self::aliases();

        if (isset($aliases[$key])) {
            return $aliases[$key];
        }

        return isset(self::icons()[$key]) ? $key : 'sparkles';
    }

    /**
     * @return array{label: string, group: string, paths: string, libraries?: array<string, string>}
     */
    public static function icon(string $key): array
    {
        $key = self::normalizeKey($key);

        return self::icons()[$key];
    }

    public static function paths(string $key, string $library): string
    {
        $icon = self::icon($key);
        $paths = $icon['libraries'][$library] ?? null;

        return is_string($paths) && $paths !== '' ? $paths : $icon['paths'];
    }

    /**
     * @return array<string, string>
     */
    public static function aliases(): array
    {
        return [
            '' => 'sparkles',
            'default' => 'sparkles',
            'destructive' => 'x-circle',
            'error' => 'x-circle',
            'success' => 'check-circle',
            'warning' => 'alert-triangle',
            'lightning-bolt' => 'zap',
        ];
    }

    /**
     * @return array<string, array{label: string, group: string, paths: string, libraries?: array<string, string>}>
     */
    public static function icons(): array
    {
        return [
            'sparkles' => [
                'label' => 'Sparkles',
                'group' => 'General',
                'paths' => '<path d="M9.94 14.7 8.5 18.5 7.06 14.7 3.5 13.25l3.56-1.45L8.5 8l1.44 3.8 3.56 1.45z"/><path d="M17.5 3.5 16.4 6.4 13.5 7.5l2.9 1.1 1.1 2.9 1.1-2.9 2.9-1.1-2.9-1.1z"/><path d="M18 15.5 17.3 17.3 15.5 18l1.8.7.7 1.8.7-1.8 1.8-.7-1.8-.7z"/>',
            ],
            'info' => [
                'label' => 'Info',
                'group' => 'Status',
                'paths' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
            ],
            'alert-triangle' => [
                'label' => 'Alert Triangle',
                'group' => 'Status',
                'paths' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
            ],
            'check' => [
                'label' => 'Check',
                'group' => 'Status',
                'paths' => '<path d="m20 6-11 11-5-5"/>',
            ],
            'check-circle' => [
                'label' => 'Check Circle',
                'group' => 'Status',
                'paths' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
            ],
            'x-circle' => [
                'label' => 'X Circle',
                'group' => 'Status',
                'paths' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
            ],
            'shield' => [
                'label' => 'Shield',
                'group' => 'Trust',
                'paths' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>',
            ],
            'shield-check' => [
                'label' => 'Shield Check',
                'group' => 'Trust',
                'paths' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
            ],
            'lock' => [
                'label' => 'Lock',
                'group' => 'Trust',
                'paths' => '<rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            ],
            'heart' => [
                'label' => 'Heart',
                'group' => 'Social',
                'paths' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7z"/>',
            ],
            'star' => [
                'label' => 'Star',
                'group' => 'Social',
                'paths' => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
            ],
            'users' => [
                'label' => 'Users',
                'group' => 'Social',
                'paths' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            ],
            'handshake' => [
                'label' => 'Handshake',
                'group' => 'Social',
                'paths' => '<path d="m11 17 2 2a2.8 2.8 0 0 0 4-4"/><path d="m14 14 2.5 2.5a2.8 2.8 0 0 0 4-4L15 7l-3 3a2.8 2.8 0 0 1-4-4l1-1"/><path d="m7 12-2 2a2.8 2.8 0 1 0 4 4l2-2"/><path d="M2 7l4-4 4 4"/><path d="m22 7-4-4-4 4"/>',
            ],
            'rocket' => [
                'label' => 'Rocket',
                'group' => 'Product',
                'paths' => '<path d="M4.5 16.5c-1.5 1.26-2 4-2 4s2.74-.5 4-2c.84-.99.78-2.49-.14-3.4-.91-.92-2.41-.98-3.4-.14z"/><path d="m12 15-3-3a22 22 0 0 1 2-5.5C13.5 1.5 19 2 22 2c0 3 .5 8.5-4.5 11a22 22 0 0 1-5.5 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/><circle cx="16" cy="8" r="2"/>',
            ],
            'zap' => [
                'label' => 'Zap',
                'group' => 'Product',
                'paths' => '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46L12 9h8a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46L12 15H4z"/>',
            ],
            'lightning' => [
                'label' => 'Lightning',
                'group' => 'Product',
                'paths' => '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46L12 9h8a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46L12 15H4z"/>',
            ],
            'settings' => [
                'label' => 'Settings',
                'group' => 'Product',
                'paths' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.72l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
            ],
            'chart' => [
                'label' => 'Chart',
                'group' => 'Data',
                'paths' => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
            ],
            'chart-no-axes-combined' => [
                'label' => 'Combined Chart',
                'group' => 'Data',
                'paths' => '<path d="M12 16v5"/><path d="M16 14v7"/><path d="M20 10v11"/><path d="m22 3-8.646 8.646a.5.5 0 0 1-.708 0L9.354 8.354a.5.5 0 0 0-.708 0L2 15"/><path d="M4 18v3"/><path d="M8 14v7"/>',
            ],
            'database' => [
                'label' => 'Database',
                'group' => 'Data',
                'paths' => '<ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v6c0 1.66 3.13 3 7 3s7-1.34 7-3V5"/><path d="M5 11v6c0 1.66 3.13 3 7 3s7-1.34 7-3v-6"/>',
            ],
            'cloud' => [
                'label' => 'Cloud',
                'group' => 'Data',
                'paths' => '<path d="M17.5 19H7a5 5 0 1 1 1.6-9.73A6 6 0 0 1 20 12.5 3.5 3.5 0 0 1 17.5 19z"/>',
            ],
            'book-open' => [
                'label' => 'Book Open',
                'group' => 'Content',
                'paths' => '<path d="M12 7v14"/><path d="M3 18a2 2 0 0 1 2-2h7"/><path d="M3 6a2 2 0 0 1 2-2h7v17H5a2 2 0 0 0-2 2z"/><path d="M21 6a2 2 0 0 0-2-2h-7v17h7a2 2 0 0 1 2 2z"/>',
            ],
            'file' => [
                'label' => 'File',
                'group' => 'Content',
                'paths' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
            ],
            'download' => [
                'label' => 'Download',
                'group' => 'Content',
                'paths' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>',
            ],
            'clock' => [
                'label' => 'Clock',
                'group' => 'Communication',
                'paths' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
            ],
            'mail' => [
                'label' => 'Mail',
                'group' => 'Communication',
                'paths' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/>',
            ],
            'phone' => [
                'label' => 'Phone',
                'group' => 'Communication',
                'paths' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13 1 .36 1.98.7 2.92a2 2 0 0 1-.45 2.11L8.09 10a16 16 0 0 0 6 6l1.25-1.27a2 2 0 0 1 2.11-.45c.94.34 1.92.57 2.92.7A2 2 0 0 1 22 16.92z"/>',
            ],
            'search' => [
                'label' => 'Search',
                'group' => 'Communication',
                'paths' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            ],
            'globe' => [
                'label' => 'Globe',
                'group' => 'Places',
                'paths' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
            ],
            'map-pin' => [
                'label' => 'Map Pin',
                'group' => 'Places',
                'paths' => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
            ],
            'building' => [
                'label' => 'Building',
                'group' => 'Places',
                'paths' => '<path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/><path d="M16 8h2a2 2 0 0 1 2 2v11"/><path d="M9 7h2"/><path d="M9 11h2"/><path d="M9 15h2"/><path d="M4 21h16"/>',
            ],
            'briefcase' => [
                'label' => 'Briefcase',
                'group' => 'Places',
                'paths' => '<path d="M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1"/><path d="M3 7h18v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M3 13h18"/><path d="M10 13v1h4v-1"/>',
            ],
            'none' => [
                'label' => 'No icon',
                'group' => 'General',
                'paths' => '',
            ],
        ];
    }
}
