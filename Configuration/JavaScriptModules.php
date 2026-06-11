<?php

declare(strict_types=1);

// Exposes Desiderio ES modules to the backend importmap so the RTE preset
// (Configuration/RTE/Desiderio.yaml) can import the abbreviation plugin.
return [
    'dependencies' => ['backend', 'rte_ckeditor'],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@webconsulting/desiderio/' => 'EXT:desiderio/Resources/Public/JavaScript/',
    ],
];
