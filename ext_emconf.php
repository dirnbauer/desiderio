<?php

declare(strict_types=1);

/** @var array<string, mixed> $EM_CONF */
$EM_CONF ??= [];
$EM_CONF[$_EXTKEY] = [
    'title' => 'Desiderio',
    'description' => 'Desiderio — shadcn/ui-inspired Fluid 5 component library, 244 Desiderio Content Blocks, and theme layer for TYPO3 14.3 LTS.',
    'category' => 'templates',
    'author' => 'webconsulting studio',
    'author_email' => '',
    'state' => 'stable',
    'version' => '4.1.3',
    'constraints' => [
        'depends' => [
            'php' => '8.4.0-8.5.99',
            'typo3' => '14.3.6-14.99.99',
            'workspaces' => '14.3.6-14.99.99',
            'content_blocks' => '2.2.0-2.99.99',
        ],
        'conflicts' => [
            'shadcn2fluid_templates' => '',
        ],
        'suggests' => [
            'solr' => '',
            'news' => '',
            'blog' => '',
            'powermail' => '',
        ],
    ],
];
