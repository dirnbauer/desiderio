<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Domain\Finishers;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use Webconsulting\Desiderio\Utility\DesiderioFormIdentifier;

/**
 * @phpstan-type BrevoConfiguration array{enabled: bool, apiKey: string, listIds: list<int>, strict: bool, trackEvent: bool, eventName: string}
 */
final class BrevoContactFinisher extends AbstractFinisher
{
    private const CONTACT_ENDPOINT = 'https://api.brevo.com/v3/contacts';
    private const EVENT_ENDPOINT = 'https://api.brevo.com/v3/events';
    private const SKIPPED_FIELDS = [
        'friendlycaptcha' => true,
    ];

    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly BrevoConfigurationResolver $configurationResolver = new BrevoConfigurationResolver(),
    ) {
        $this->defaultOptions = [
            'emailField' => 'email',
            'contactAttributes' => [],
            'eventName' => 'desiderio_form_submit',
            'trackEvent' => true,
            'updateEnabled' => true,
        ];
    }

    protected function executeInternal(): ?string
    {
        $formIdentifier = $this->finisherContext->getFormRuntime()->getIdentifier();
        if (!DesiderioFormIdentifier::matches($formIdentifier)) {
            return null;
        }

        $configuration = $this->resolveConfiguration();
        if (!$configuration['enabled']) {
            return null;
        }
        if ($configuration['apiKey'] === '') {
            $this->logDebug('Brevo form sync skipped because no API key is configured.', ['formIdentifier' => $formIdentifier]);
            return null;
        }

        $values = $this->normalizeFormValues($this->finisherContext->getFormValues());
        $emailField = $this->getStringOption('emailField');
        if ($emailField === '') {
            $emailField = 'email';
        }

        $email = $this->extractEmail($values, $emailField);
        $contactAttributes = $this->buildContactAttributes($values);

        if ($email === '' && !isset($contactAttributes['SMS'])) {
            $this->logDebug('Brevo form sync skipped because no email or SMS identifier was submitted.', ['formIdentifier' => $formIdentifier]);
            return null;
        }

        $this->syncContact($configuration, $email, $contactAttributes, $formIdentifier);

        if ($configuration['trackEvent'] && $email !== '') {
            $this->trackEvent($configuration, $email, $values, $formIdentifier);
        }

        return null;
    }

    /**
     * @return BrevoConfiguration
     */
    private function resolveConfiguration(): array
    {
        return $this->configurationResolver->resolve($this->options, $this->getCurrentSite());
    }

    /**
     * @param BrevoConfiguration $configuration
     * @param array<string, string|int|float|bool|list<string>> $contactAttributes
     */
    private function syncContact(array $configuration, string $email, array $contactAttributes, string $formIdentifier): void
    {
        $payload = [
            'updateEnabled' => $this->getBooleanOption('updateEnabled', true),
        ];
        if ($email !== '') {
            $payload['email'] = $email;
        }
        if ($contactAttributes !== []) {
            $payload['attributes'] = $contactAttributes;
        }
        if ($configuration['listIds'] !== []) {
            $payload['listIds'] = $configuration['listIds'];
        }

        $response = $this->sendRequest(self::CONTACT_ENDPOINT, $payload, $configuration, $formIdentifier, 'contact');
        if (!$response instanceof ResponseInterface) {
            return;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $this->failOrLog(
                'Brevo contact sync returned a non-success response.',
                ['formIdentifier' => $formIdentifier, 'statusCode' => $statusCode],
                $configuration['strict'],
                1762361001
            );
        }
    }

    /**
     * @param BrevoConfiguration $configuration
     * @param array<string, mixed> $values
     */
    private function trackEvent(array $configuration, string $email, array $values, string $formIdentifier): void
    {
        $payload = [
            'event_name' => $configuration['eventName'],
            'identifiers' => [
                'email_id' => $email,
            ],
            'event_properties' => $this->buildEventProperties($values, $formIdentifier),
        ];

        $response = $this->sendRequest(self::EVENT_ENDPOINT, $payload, $configuration, $formIdentifier, 'event');
        if (!$response instanceof ResponseInterface) {
            return;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $this->failOrLog(
                'Brevo event tracking returned a non-success response.',
                ['formIdentifier' => $formIdentifier, 'statusCode' => $statusCode],
                $configuration['strict'],
                1762361002
            );
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @param BrevoConfiguration $configuration
     */
    private function sendRequest(
        string $endpoint,
        array $payload,
        array $configuration,
        string $formIdentifier,
        string $operation
    ): ?ResponseInterface {
        try {
            return $this->requestFactory->request(
                $endpoint,
                'POST',
                [
                    'headers' => [
                        'accept' => 'application/json',
                        'api-key' => $configuration['apiKey'],
                        'content-type' => 'application/json',
                    ],
                    'http_errors' => false,
                    'json' => $payload,
                    'timeout' => 5,
                ],
                'desiderio-brevo'
            );
        } catch (\Throwable $exception) {
            $this->failOrLog(
                'Brevo request failed.',
                ['formIdentifier' => $formIdentifier, 'operation' => $operation, 'exceptionClass' => $exception::class],
                $configuration['strict'],
                1762361003,
                $exception
            );
        }

        return null;
    }

    /**
     * @param array<mixed> $values
     * @return array<string, mixed>
     */
    private function normalizeFormValues(array $values): array
    {
        $normalizedValues = [];
        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $normalizedValues[$key] = $value;
            }
        }

        return $normalizedValues;
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, string|int|float|bool|list<string>>
     */
    private function buildContactAttributes(array $values): array
    {
        $mappings = $this->getRawOption('contactAttributes');
        if (!is_array($mappings)) {
            return [];
        }

        $attributes = [];
        foreach ($mappings as $attributeName => $fieldName) {
            if (!is_string($attributeName) || !is_string($fieldName)) {
                continue;
            }

            $attributeName = strtoupper(trim($attributeName));
            if ($attributeName === '' || preg_match('/^[A-Z0-9_]+$/', $attributeName) !== 1) {
                continue;
            }

            $value = $this->normalizeBrevoValue($this->getValueByPath($values, $fieldName));
            if ($value !== null) {
                $attributes[$attributeName] = $value;
            }
        }

        return $attributes;
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, string|int|float|bool|list<string>>
     */
    private function buildEventProperties(array $values, string $formIdentifier): array
    {
        $properties = [
            'form_identifier' => $formIdentifier,
            'source_url' => $this->getCurrentUri(),
            'submitted_at' => date(DATE_ATOM),
        ];

        foreach ($values as $fieldName => $value) {
            if (!is_string($fieldName) || isset(self::SKIPPED_FIELDS[$fieldName])) {
                continue;
            }

            $propertyName = $this->sanitizeEventPropertyName($fieldName);
            if ($propertyName === '') {
                continue;
            }

            $normalizedValue = $this->normalizeBrevoValue($value);
            if ($normalizedValue !== null) {
                $properties[$propertyName] = $normalizedValue;
            }
        }

        return $properties;
    }

    /**
     * @param array<string, mixed> $values
     */
    private function extractEmail(array $values, string $fieldName): string
    {
        $value = $this->normalizeBrevoValue($this->getValueByPath($values, $fieldName));
        if (!is_string($value)) {
            return '';
        }

        $email = trim($value);
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : '';
    }

    /**
     * @param array<string, mixed> $values
     */
    private function getValueByPath(array $values, string $path): mixed
    {
        $segments = explode('.', $path);
        $currentValue = $values;

        foreach ($segments as $segment) {
            if (!is_array($currentValue) || !array_key_exists($segment, $currentValue)) {
                return null;
            }

            $currentValue = $currentValue[$segment];
        }

        return $currentValue;
    }

    /**
     * @return string|int|float|bool|list<string>|null
     */
    private function normalizeBrevoValue(mixed $value): string|int|float|bool|array|null
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }
        if (is_array($value)) {
            $normalizedItems = [];
            foreach ($value as $item) {
                $normalizedItem = $this->normalizeBrevoValue($item);
                if ($normalizedItem === null) {
                    continue;
                }
                if (is_array($normalizedItem)) {
                    foreach ($normalizedItem as $nestedItem) {
                        $normalizedItems[] = (string)$nestedItem;
                    }
                    continue;
                }
                $normalizedItems[] = (string)$normalizedItem;
            }

            return $normalizedItems !== [] ? $normalizedItems : null;
        }
        if (is_string($value)) {
            $stringValue = trim($value);
        } elseif ($value instanceof \Stringable) {
            $stringValue = trim($value->__toString());
        } else {
            return null;
        }
        if ($stringValue === '') {
            return null;
        }

        return mb_substr($stringValue, 0, 4000);
    }

    private function sanitizeEventPropertyName(string $fieldName): string
    {
        $propertyName = preg_replace('/[^A-Za-z0-9_-]+/', '_', trim($fieldName)) ?? '';
        $propertyName = trim($propertyName, '_-');

        return mb_substr($propertyName, 0, 255);
    }

    private function getStringOption(string $optionName): string
    {
        $value = $this->parseOption($optionName);

        return is_string($value) ? trim($value) : '';
    }

    private function getBooleanOption(string $optionName, bool $default): bool
    {
        return $this->configurationResolver->resolveBoolean($this->parseOption($optionName), $default);
    }

    private function getRawOption(string $optionName): mixed
    {
        return $this->options[$optionName] ?? null;
    }

    private function getCurrentSite(): ?Site
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $site = is_object($request) && method_exists($request, 'getAttribute')
            ? $request->getAttribute('site')
            : null;

        return $site instanceof Site ? $site : null;
    }

    private function getCurrentUri(): string
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (is_object($request) && method_exists($request, 'getUri')) {
            $uri = $request->getUri();
            if ($uri instanceof \Stringable) {
                return $uri->__toString();
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $context
     */
    private function failOrLog(
        string $message,
        array $context,
        bool $strict,
        int $code,
        ?\Throwable $previous = null
    ): void {
        if ($strict) {
            throw new FinisherException($message, $code, $previous);
        }

        $this->logger?->warning($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function logDebug(string $message, array $context): void
    {
        $this->logger?->debug($message, $context);
    }
}
