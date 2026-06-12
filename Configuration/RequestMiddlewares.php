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
    ],
];
