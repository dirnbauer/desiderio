<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

/**
 * What an element-library upsert did to the row it was handed.
 */
enum UpsertStatus
{
    case Created;
    case Updated;
}
