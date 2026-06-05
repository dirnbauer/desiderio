<?php

declare(strict_types=1);

namespace StudioMitte\FriendlyCaptcha;

use TYPO3\CMS\Core\Site\Entity\Site;

class Configuration
{
    public function __construct(Site $site) {}

    public function isEnabled(): bool
    {
        return false;
    }
}
