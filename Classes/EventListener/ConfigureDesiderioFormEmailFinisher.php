<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\EventListener;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Form\Event\BeforeEmailFinisherInitializedEvent;
use TYPO3\CMS\Frontend\Page\PageInformation;
use Webconsulting\Desiderio\Utility\DesiderioFormIdentifier;

final class ConfigureDesiderioFormEmailFinisher
{
    private const EMAIL_TEMPLATE_ROOT_PATH = 'EXT:desiderio/Resources/Private/Form/Email/Templates/';
    private const EMAIL_LAYOUT_ROOT_PATH = 'EXT:desiderio/Resources/Private/Form/Email/Layouts/';

    #[AsEventListener('desiderio/configure-form-email-finisher')]
    public function __invoke(BeforeEmailFinisherInitializedEvent $event): void
    {
        $formIdentifier = $event->getFinisherContext()->getFormRuntime()->getIdentifier();
        if (!DesiderioFormIdentifier::matches($formIdentifier)) {
            return;
        }

        $request = $event->getFinisherContext()->getRequest();
        $settings = $this->getSiteFormSettings($request);
        $options = $event->getOptions();
        $receiverAddress = $settings['receiverAddress'] ?? '';
        $receiverName = $settings['receiverName'] ?? '';
        $senderAddress = $settings['senderAddress'] ?? '';
        $senderName = $settings['senderName'] ?? '';

        if ($receiverAddress !== '') {
            $options['recipients'] = [
                $receiverAddress => $receiverName !== '' ? $receiverName : $receiverAddress,
            ];
        }
        if ($senderAddress !== '') {
            $options['senderAddress'] = $senderAddress;
        }
        if ($senderName !== '') {
            $options['senderName'] = $senderName;
        }

        $options = $this->applyDesiderioEmailTemplateOptions($options, $request);

        $event->setOptions($options);
    }

    /**
     * @return array{receiverAddress?: string, receiverName?: string, senderAddress?: string, senderName?: string}
     */
    private function getSiteFormSettings(ServerRequestInterface $request): array
    {
        $site = $request->getAttribute('site');

        if (!$site instanceof Site || $site->getSettings()->isEmpty()) {
            return [];
        }

        return [
            'receiverAddress' => $this->getStringSiteSetting($site, 'desiderio.forms.receiverAddress'),
            'receiverName' => $this->getStringSiteSetting($site, 'desiderio.forms.receiverName'),
            'senderAddress' => $this->getStringSiteSetting($site, 'desiderio.forms.senderAddress'),
            'senderName' => $this->getStringSiteSetting($site, 'desiderio.forms.senderName'),
        ];
    }

    private function getStringSiteSetting(Site $site, string $identifier): string
    {
        $value = $site->getSettings()->get($identifier, '');

        return is_string($value) ? trim($value) : '';
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function applyDesiderioEmailTemplateOptions(array $options, ServerRequestInterface $request): array
    {
        $options['templateName'] = 'Default';
        $options['templateRootPaths'] = $this->withPathOption(
            $options['templateRootPaths'] ?? null,
            self::EMAIL_TEMPLATE_ROOT_PATH
        );
        $options['layoutRootPaths'] = $this->withPathOption(
            $options['layoutRootPaths'] ?? null,
            self::EMAIL_LAYOUT_ROOT_PATH
        );

        $variables = is_array($options['variables'] ?? null) ? $options['variables'] : [];
        $variables['sourcePageTitle'] = $this->getSourcePageTitle($request);
        $variables['sourcePageUrl'] = $this->getSourcePageUrl($request);
        $options['variables'] = $variables;

        return $options;
    }

    /**
     * @return array<int|string, string>
     */
    private function withPathOption(mixed $paths, string $path): array
    {
        $normalizedPaths = [];
        if (is_array($paths)) {
            foreach ($paths as $key => $value) {
                if (is_string($value)) {
                    $normalizedPaths[$key] = $value;
                }
            }
        }
        $normalizedPaths[100] = $path;

        return $normalizedPaths;
    }

    /**
     * The mail should reference the page, not the POST round trip: the raw
     * request URI carries tx_form_formframework[action/controller] and cHash,
     * which triples the link length and means nothing to the reader.
     */
    private function getSourcePageUrl(ServerRequestInterface $request): string
    {
        $uri = $request->getUri();
        parse_str($uri->getQuery(), $queryParams);
        unset($queryParams['tx_form_formframework'], $queryParams['cHash']);
        return (string)$uri->withQuery(http_build_query($queryParams));
    }

    private function getSourcePageTitle(ServerRequestInterface $request): string
    {
        $pageInformation = $request->getAttribute('frontend.page.information');
        if (!$pageInformation instanceof PageInformation) {
            return '';
        }

        $pageRecord = $pageInformation->getPageRecord();
        $pageTitle = $pageRecord['title'] ?? $pageRecord['nav_title'] ?? '';

        return is_string($pageTitle) ? trim($pageTitle) : '';
    }
}
