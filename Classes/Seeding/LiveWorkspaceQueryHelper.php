<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

final class LiveWorkspaceQueryHelper
{
    public function __construct(
        private readonly DatabaseSchemaHelper $databaseSchema,
    ) {}

    /**
     * Restrict destructive seed cleanup to live rows. TYPO3 stores workspace
     * versions in the same table, so queries with restrictions removed must
     * add the live workspace predicates explicitly.
     *
     * @return list<string>
     */
    public function buildLiveWorkspaceConstraints(QueryBuilder $queryBuilder, string $table): array
    {
        $constraints = [];
        if ($this->databaseSchema->tableHasColumn($table, 't3ver_wsid')) {
            $constraints[] = $queryBuilder->expr()->eq(
                't3ver_wsid',
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
            );
        }
        if ($this->databaseSchema->tableHasColumn($table, 't3ver_oid')) {
            $constraints[] = $queryBuilder->expr()->eq(
                't3ver_oid',
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
            );
        }

        return $constraints;
    }
}
