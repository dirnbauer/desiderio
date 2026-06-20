<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;
use Webconsulting\Desiderio\Library\CoreContentElements;

/**
 * Gives the classic TYPO3 core content elements Desiderio's custom v14 icons and
 * richer descriptions in the New Content Element Wizard, the page module and the
 * list view.
 *
 * This runs on AfterTcaCompilationEvent — i.e. AFTER every extension's
 * Configuration/TCA/Overrides has been applied — instead of in Desiderio's own
 * Overrides/tt_content.php. That ordering matters: CType items registered by
 * extensions that load after Desiderio (felogin's "felogin_login", and
 * potentially other plugins depending on package order) do not yet exist while
 * Desiderio's Overrides run, so a static override there silently misses them.
 * Patching the fully-compiled TCA here is load-order independent.
 */
final class CoreContentElementIcons
{
    #[AsEventListener('desiderio/core-content-element-icons')]
    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        $tca = $event->getTca();

        $ttContent = $tca['tt_content'] ?? null;
        $columns = is_array($ttContent) ? ($ttContent['columns'] ?? null) : null;
        $cTypeField = is_array($columns) ? ($columns['CType'] ?? null) : null;
        $config = is_array($cTypeField) ? ($cTypeField['config'] ?? null) : null;
        $items = is_array($config) ? ($config['items'] ?? null) : null;
        if (!is_array($ttContent) || !is_array($columns) || !is_array($cTypeField)
            || !is_array($config) || !is_array($items)
        ) {
            return;
        }

        $overrides = $this->buildOverrides();

        $ctrl = $ttContent['ctrl'] ?? [];
        $ctrl = is_array($ctrl) ? $ctrl : [];
        $typeicons = $ctrl['typeicon_classes'] ?? [];
        $typeicons = is_array($typeicons) ? $typeicons : [];

        $changed = false;
        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                continue;
            }
            $value = $item['value'] ?? null;
            if (!is_string($value) || !isset($overrides[$value])) {
                continue;
            }
            $item['icon'] = $overrides[$value]['icon'];
            $item['description'] = $overrides[$value]['description'];
            $items[$key] = $item;
            $typeicons[$value] = $overrides[$value]['icon'];
            $changed = true;
        }

        if (!$changed) {
            return;
        }

        $config['items'] = $items;
        $cTypeField['config'] = $config;
        $columns['CType'] = $cTypeField;
        $ttContent['columns'] = $columns;
        $ctrl['typeicon_classes'] = $typeicons;
        $ttContent['ctrl'] = $ctrl;
        $tca['tt_content'] = $ttContent;

        $event->setTca($tca);
    }

    /**
     * @return array<string, array{icon: string, description: string}>
     */
    private function buildOverrides(): array
    {
        $overrides = [];
        foreach (CoreContentElements::all() as $element) {
            $overrides[$element['cType']] = [
                'icon' => 'desiderio-ce-' . $element['iconSlug'],
                'description' => 'LLL:EXT:desiderio/Resources/Private/Language/library_core.xlf:'
                    . $element['cType'] . '.description',
            ];
        }

        return $overrides;
    }
}
