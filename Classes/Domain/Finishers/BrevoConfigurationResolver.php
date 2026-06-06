<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Domain\Finishers;

use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * @phpstan-type BrevoConfiguration array{enabled: bool, apiKey: string, listIds: list<int>, strict: bool, trackEvent: bool, eventName: string}
 */
final class BrevoConfigurationResolver
{
    /**
     * @param array<string, mixed> $finisherOptions
     * @return BrevoConfiguration
     */
    public function resolve(array $finisherOptions, ?Site $site): array
    {
        $extensionConfiguration = self::readExtensionConfiguration();

        return [
            'enabled' => $this->resolveBoolean(
                $finisherOptions['enabled'] ?? null,
                $extensionConfiguration['enabled'] ?? null,
                $this->getSiteSetting($site, 'desiderio.forms.brevo.enabled'),
                $this->getEnvironmentValue('BREVO_ENABLED'),
                false
            ),
            'apiKey' => $this->resolveString(
                $extensionConfiguration['apiKey'] ?? null,
                $this->getEnvironmentValue('BREVO_API_KEY')
            ),
            'listIds' => $this->parseListIds(
                $finisherOptions['listIds']
                    ?? ($extensionConfiguration['listIds'] ?? null)
                    ?? $this->getSiteSetting($site, 'desiderio.forms.brevo.listIds')
                    ?? $this->getEnvironmentValue('BREVO_LIST_IDS')
            ),
            'strict' => $this->resolveBoolean(
                $finisherOptions['strict'] ?? null,
                $extensionConfiguration['strict'] ?? null,
                $this->getSiteSetting($site, 'desiderio.forms.brevo.strict'),
                $this->getEnvironmentValue('BREVO_STRICT'),
                false
            ),
            'trackEvent' => $this->resolveBoolean(
                $finisherOptions['trackEvent'] ?? null,
                $extensionConfiguration['trackEvent'] ?? null,
                $this->getSiteSetting($site, 'desiderio.forms.brevo.trackEvent'),
                $this->getEnvironmentValue('BREVO_TRACK_EVENT'),
                true
            ),
            'eventName' => $this->sanitizeEventName($this->resolveString(
                $finisherOptions['eventName'] ?? null,
                $extensionConfiguration['eventName'] ?? null,
                $this->getSiteSetting($site, 'desiderio.forms.brevo.eventName'),
                $this->getEnvironmentValue('BREVO_EVENT_NAME'),
                'desiderio_form_submit'
            )),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function readExtensionConfiguration(): array
    {
        $typo3Configuration = $GLOBALS['TYPO3_CONF_VARS'] ?? [];
        if (!is_array($typo3Configuration)) {
            return [];
        }

        $extensionsConfiguration = $typo3Configuration['EXTENSIONS'] ?? [];
        if (!is_array($extensionsConfiguration)) {
            return [];
        }

        $desiderioConfiguration = $extensionsConfiguration['desiderio'] ?? [];
        if (!is_array($desiderioConfiguration)) {
            return [];
        }

        $configuration = $desiderioConfiguration['brevo'] ?? [];
        if (!is_array($configuration)) {
            return [];
        }

        $filteredConfiguration = [];
        foreach ($configuration as $key => $value) {
            if (is_string($key)) {
                $filteredConfiguration[$key] = $value;
            }
        }

        return $filteredConfiguration;
    }

    /**
     * @param mixed ...$candidates
     */
    public function resolveBoolean(...$candidates): bool
    {
        $default = false;
        if ($candidates !== []) {
            $lastCandidate = $candidates[array_key_last($candidates)];
            if (is_bool($lastCandidate)) {
                $default = $lastCandidate;
                array_pop($candidates);
            }
        }

        foreach ($candidates as $candidate) {
            if (is_bool($candidate)) {
                return $candidate;
            }
            if (is_int($candidate)) {
                return $candidate === 1;
            }
            if (is_string($candidate) && trim($candidate) !== '') {
                $normalized = strtolower(trim($candidate));
                if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                    return true;
                }
                if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                    return false;
                }
            }
        }

        return $default;
    }

    /**
     * @param mixed ...$candidates
     */
    public function resolveString(...$candidates): string
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
            if (is_int($candidate) || is_float($candidate)) {
                return (string)$candidate;
            }
        }

        return '';
    }

    /**
     * @return list<int>
     */
    public function parseListIds(mixed $value): array
    {
        if (is_int($value)) {
            return $value > 0 ? [$value] : [];
        }

        if (is_string($value)) {
            $splitValue = preg_split('/[,\s]+/', trim($value));
            $value = is_array($splitValue) ? $splitValue : [];
        }

        if (!is_array($value)) {
            return [];
        }

        $listIds = [];
        foreach ($value as $listId) {
            if (is_int($listId) && $listId > 0) {
                $listIds[] = $listId;
                continue;
            }
            if (is_string($listId) && preg_match('/^\d+$/', trim($listId)) === 1) {
                $listIds[] = (int)$listId;
            }
        }

        return array_values(array_unique($listIds));
    }

    public function sanitizeEventName(string $eventName): string
    {
        $eventName = preg_replace('/[^A-Za-z0-9_-]+/', '_', trim($eventName)) ?? '';
        $eventName = trim($eventName, '_-');

        return $eventName !== '' ? mb_substr($eventName, 0, 255) : 'desiderio_form_submit';
    }

    private function getSiteSetting(?Site $site, string $identifier): mixed
    {
        if (!$site instanceof Site || $site->getSettings()->isEmpty()) {
            return null;
        }

        return $site->getSettings()->get($identifier, null);
    }

    private function getEnvironmentValue(string $name): ?string
    {
        $value = getenv($name);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
