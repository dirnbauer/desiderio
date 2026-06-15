<?php

declare(strict_types=1);

return [
    'frontend' => [
        'webconsulting/desiderio-extbase-plugin-request-sanitizer' => [
            'target' => \Webconsulting\Desiderio\Middleware\ExtbasePluginRequestSanitizerMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
        'webconsulting/desiderio-friendlycaptcha-test-mode' => [
            'target' => \Webconsulting\Desiderio\Middleware\FriendlyCaptchaTestModeMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
        'webconsulting/desiderio-element-library' => [
            'target' => \Webconsulting\Desiderio\Middleware\ElementLibraryMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
        // Render element-library previews cacheable even inside an authenticated
        // edit session: turn the admin panel off for elPreview requests before
        // EXT:adminpanel's initiator so the preview is not flagged no_cache.
        'webconsulting/desiderio-element-preview-cacheable' => [
            'target' => \Webconsulting\Desiderio\Middleware\ElementPreviewCacheableMiddleware::class,
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-adminpanel/initiator',
            ],
        ],
    ],
];
