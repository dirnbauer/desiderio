<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Utility;

final class DesiderioFormIdentifier
{
    public static function matches(string $formIdentifier): bool
    {
        return str_starts_with($formIdentifier, 'desiderio-');
    }
}
