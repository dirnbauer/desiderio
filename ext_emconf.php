<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Desiderio',
    'description' => 'Desiderio — shadcn/ui-inspired Fluid 5 component library, 255 content elements, and theme layer for TYPO3 14.3 LTS.',
    'category' => 'templates',
    'author' => 'webconsulting studio',
    'author_email' => '',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'php' => '8.3.0-8.5.99',
            'typo3' => '14.3.0-14.99.99',
            'workspaces' => '14.3.0-14.99.99',
            'content_blocks' => '2.2.0-2.99.99',
            'vite_asset_collector' => '1.7.0-1.99.99',
        ],
        'conflicts' => [
            'shadcn2fluid_templates' => '',
        ],
        'suggests' => [
            'solr' => '',
            'news' => '',
        ],
    ],
];
