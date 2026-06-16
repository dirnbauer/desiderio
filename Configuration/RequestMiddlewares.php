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
        // edit session: for elPreview requests turn the admin panel off (before
        // EXT:adminpanel's initiator) and pin the workspace to live (before
        // EXT:workspaces' preview), so the preview is not flagged no_cache and is
        // served from the warmed live page cache.
        'webconsulting/desiderio-element-preview-cacheable' => [
            'target' => \Webconsulting\Desiderio\Middleware\ElementPreviewCacheableMiddleware::class,
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
