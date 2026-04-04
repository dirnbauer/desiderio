<?php

declare(strict_types=1);

$_EXTKEY ??= 'desiderio';

$EM_CONF[$_EXTKEY] = [
    'title' => 'Desiderio',
    'description' => 'A camino-inspired TYPO3 v14 theme built on shadcn2fluid components. Thank you to the TYPO3 Core Team and all contributors who created the Camino theme — your work made this possible.',
    'category' => 'templates',
    'author' => 'webconsulting studio',
    'author_email' => '',
    'state' => 'beta',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-8.4.99',
            'typo3' => '14.3.0-14.99.99',
            'fluid' => '14.3.0-14.99.99',
            'shadcn2fluid_templates' => '3.0.0-3.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
