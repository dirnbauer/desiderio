<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\EventListener;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Content Blocks intentionally stores many fields in shared tt_content columns
 * because the extension uses prefixFields: false. Select fields still need
 * CType-local item lists; otherwise values from one element leak into another
 * element's backend dropdown.
 */
final class ContentBlockSelectItemOverrides
{
    #[AsEventListener('desiderio/content-block-select-item-overrides')]
    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        $tca = $event->getTca();
        $ttContent = $tca['tt_content'] ?? null;
        if (!is_array($ttContent)) {
            return;
        }

        $types = $ttContent['types'] ?? null;
        if (!is_array($types)) {
            return;
        }

        $changed = false;
        foreach ($this->buildOverrides() as $typeName => $fieldOverrides) {
            $typeConfiguration = $types[$typeName] ?? null;
            if (!is_array($typeConfiguration)) {
                continue;
            }

            $columnsOverrides = $typeConfiguration['columnsOverrides'] ?? [];
            $columnsOverrides = is_array($columnsOverrides) ? $columnsOverrides : [];

            foreach ($fieldOverrides as $fieldName => $fieldOverride) {
                $existingOverride = $columnsOverrides[$fieldName] ?? [];
                $existingOverride = is_array($existingOverride) ? $existingOverride : [];

                $existingConfig = $existingOverride['config'] ?? [];
                $existingConfig = is_array($existingConfig) ? $existingConfig : [];
                $overrideConfig = $fieldOverride['config'] ?? [];
                $overrideConfig = is_array($overrideConfig) ? $overrideConfig : [];

                $fieldOverride['config'] = array_replace($existingConfig, $overrideConfig);
                $columnsOverrides[$fieldName] = array_replace(
                    $existingOverride,
                    $fieldOverride
                );
                $changed = true;
            }

            $typeConfiguration['columnsOverrides'] = $columnsOverrides;
            $types[$typeName] = $typeConfiguration;
        }

        if ($changed) {
            $ttContent['types'] = $types;
            $tca['tt_content'] = $ttContent;
            $event->setTca($tca);
        }
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function buildOverrides(): array
    {
        $overrides = [];
        $contentElementPath = ExtensionManagementUtility::extPath('desiderio') . 'ContentBlocks/ContentElements';
        $configFiles = glob($contentElementPath . '/*/config.yaml');
        if (!is_array($configFiles)) {
            return [];
        }

        foreach ($configFiles as $configFile) {
            $configuration = Yaml::parseFile($configFile);
            if (!is_array($configuration)) {
                continue;
            }

            $typeName = $configuration['typeName'] ?? null;
            $fields = $configuration['fields'] ?? null;
            if (!is_string($typeName) || !is_array($fields)) {
                continue;
            }

            foreach ($fields as $field) {
                if (!is_array($field)) {
                    continue;
                }

                $fieldName = $this->getStaticSelectFieldName($field);
                if ($fieldName === null) {
                    continue;
                }

                $overrides[$typeName][$fieldName] = $this->buildFieldOverride($field);
            }
        }

        return $overrides;
    }

    /**
     * @param array<mixed, mixed> $field
     */
    private function getStaticSelectFieldName(array $field): ?string
    {
        $identifier = $field['identifier'] ?? null;
        if (($field['type'] ?? null) !== 'Select'
            || !is_string($identifier)
            || ($field['useExistingField'] ?? false) === true
            || !isset($field['items'])
            || !is_array($field['items'])
        ) {
            return null;
        }

        return $identifier;
    }

    /**
     * @param array<mixed, mixed> $field
     * @return array<string, mixed>
     */
    private function buildFieldOverride(array $field): array
    {
        $override = $this->copyKnownKeys($field, [
            'label',
            'description',
            'displayCond',
            'onChange',
        ]);

        $override['config'] = $this->copyKnownKeys($field, [
            'items',
            'default',
            'renderType',
            'itemGroups',
            'sortItems',
            'authMode',
            'disableNoMatchingValueElement',
            'exclusiveKeys',
            'fieldInformation',
            'fieldWizard',
        ]);

        return $override;
    }

    /**
     * @param array<mixed, mixed> $source
     * @param list<string> $keys
     * @return array<string, mixed>
     */
    private function copyKnownKeys(array $source, array $keys): array
    {
        $target = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $source)) {
                $target[$key] = $source[$key];
            }
        }

        return $target;
    }
}
