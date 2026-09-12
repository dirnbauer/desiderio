<?php

declare(strict_types=1);

use Webconsulting\Desiderio\Middleware\ElementLibraryMiddleware;
use Webconsulting\Desiderio\Middleware\ElementPreviewCacheableMiddleware;
use Webconsulting\Desiderio\Middleware\ExtbasePluginRequestSanitizerMiddleware;
use Webconsulting\Desiderio\Middleware\FriendlyCaptchaTestModeMiddleware;

return [
    'frontend' => [
        'webconsulting/desiderio-extbase-plugin-request-sanitizer' => [
            'target' => ExtbasePluginRequestSanitizerMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
        'webconsulting/desiderio-friendlycaptcha-test-mode' => [
            'target' => FriendlyCaptchaTestModeMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
        'webconsulting/desiderio-element-library' => [
            'target' => ElementLibraryMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
        // Render element-library previews cacheable even inside an authenticated
        // edit session: for elPreview requests turn the admin panel off (before
        // EXT:adminpanel's initiator) and pin the workspace to live (before
        // EXT:workspaces' preview), so the preview is not flagged no_cache and is
        // served from the warmed live page cache.
        'webconsulting/desiderio-element-preview-cacheable' => [
            'target' => ElementPreviewCacheableMiddleware::class,
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-adminpanel/initiator',
                'typo3/cms-workspaces/preview',
            ],
        ],
    ],
];
