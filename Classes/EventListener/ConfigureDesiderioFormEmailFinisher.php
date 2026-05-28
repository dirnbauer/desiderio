<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Form\Event\BeforeEmailFinisherInitializedEvent;

final class ConfigureDesiderioFormEmailFinisher
{
    #[AsEventListener('desiderio/configure-form-email-finisher')]
    public function __invoke(BeforeEmailFinisherInitializedEvent $event): void
    {
        $formIdentifier = $event->getFinisherContext()->getFormRuntime()->getIdentifier();
        if (!str_starts_with($formIdentifier, 'desiderio-')) {
            return;
        }

        $settings = $this->getSiteFormSettings();
        if ($settings === []) {
            return;
        }

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

        $event->setOptions($options);
    }

    /**
     * @return array{receiverAddress?: string, receiverName?: string, senderAddress?: string, senderName?: string}
     */
    private function getSiteFormSettings(): array
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $site = is_object($request) && method_exists($request, 'getAttribute')
            ? $request->getAttribute('site')
            : null;

        if (!$site instanceof Site || $site->getSettings()->isEmpty()) {
            return [];
        }

        return [
            'receiverAddress' => trim((string)$site->getSettings()->get('desiderio.forms.receiverAddress', '')),
            'receiverName' => trim((string)$site->getSettings()->get('desiderio.forms.receiverName', '')),
            'senderAddress' => trim((string)$site->getSettings()->get('desiderio.forms.senderAddress', '')),
            'senderName' => trim((string)$site->getSettings()->get('desiderio.forms.senderName', '')),
        ];
    }
}
