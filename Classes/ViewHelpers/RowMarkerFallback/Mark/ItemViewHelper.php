<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers\RowMarkerFallback\Mark;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Stand-in for `f:mark.item` when Visual Editor Enhancements (1.6 or later)
 * is not loaded.
 *
 * The Content Block templates mark each collection entry's root element with
 * `{entry -> f:mark.item(collection: '...')}`; the real ViewHelper ships with
 * Visual Editor Enhancements and writes `data-ve-*` attributes in edit mode
 * only. Without that extension the call must not become an unknown-ViewHelper
 * error, so ext_localconf.php registers this namespace on `f` in that case and
 * the marker renders exactly what the real one renders outside edit mode:
 * nothing ('' for an HTML tag, [] for a component's `attributes`). The
 * published markup is the same either way.
 *
 * Same arguments as the real ViewHelper, so a template is valid with or
 * without the extension.
 */
final class ItemViewHelper extends AbstractViewHelper
{
    protected $escapeChildren = false;

    protected $escapeOutput = false;

    #[\Override]
    public function initializeArguments(): void
    {
        $this->registerArgument('record', 'mixed', 'The collection entry (child record), usually the f:for item');
        $this->registerArgument('collection', 'string', 'The parent\'s collection (inline) field that holds this entry', true);
        $this->registerArgument('format', 'string', '"attributes" (an attribute string for an HTML tag) or "array" (for a component\'s attributes argument)', false, 'attributes');
    }

    #[\Override]
    public function getContentArgumentName(): string
    {
        return 'record';
    }

    /**
     * @return string|array<never, never>
     */
    #[\Override]
    public function render(): string|array
    {
        return ($this->arguments['format'] ?? 'attributes') === 'array' ? [] : '';
    }
}
