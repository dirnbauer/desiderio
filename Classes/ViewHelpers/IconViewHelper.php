<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Webconsulting\Desiderio\Icon\IconRegistry;

final class IconViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Semantic Desiderio icon key', true);
        $this->registerArgument('size', 'string', 'Icon size token', false, 'default');
        $this->registerArgument('class', 'string', 'Additional CSS classes', false, '');
    }

    public function render(): string
    {
        $name = is_string($this->arguments['name'] ?? null) ? $this->arguments['name'] : '';
        $key = IconRegistry::normalizeKey($name);
        if ($key === 'none') {
            return '';
        }

        $size = is_string($this->arguments['size'] ?? null) ? $this->arguments['size'] : 'default';
        $class = is_string($this->arguments['class'] ?? null) ? $this->arguments['class'] : '';
        $sizeClass = $this->sizeClass($size);
        $classAttribute = trim('d-icon shrink-0 ' . $sizeClass . ' ' . $class);
        $label = IconRegistry::icon($key)['label'];
        $output = '';

        foreach (IconRegistry::supportedLibraries() as $library) {
            $paths = IconRegistry::paths($key, $library);
            if ($paths === '') {
                continue;
            }
            $output .= sprintf(
                '<svg class="%s" data-icon-library="%s" data-icon-name="%s" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%s" stroke-linecap="%s" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
                htmlspecialchars($classAttribute, ENT_QUOTES | ENT_HTML5),
                htmlspecialchars($library, ENT_QUOTES | ENT_HTML5),
                htmlspecialchars($key, ENT_QUOTES | ENT_HTML5),
                $library === 'phosphor' ? '1.75' : '2',
                $library === 'tabler' ? 'round' : 'round',
                $paths
            );
        }

        return $output !== ''
            ? $output
            : '<span class="d-sr-only">' . htmlspecialchars($label, ENT_QUOTES | ENT_HTML5) . '</span>';
    }

    private function sizeClass(string $size): string
    {
        return match ($size) {
            'xs' => 'h-3 w-3',
            'sm' => 'h-4 w-4',
            'lg' => 'h-6 w-6',
            'xl' => 'h-8 w-8',
            default => 'h-5 w-5',
        };
    }
}
