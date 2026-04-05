<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'ext-desiderio' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/Extension.svg',
    ],
    'plugin-desiderio' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/plugin-desiderio.svg',
    ],
];
