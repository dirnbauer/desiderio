<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Library\CoreContentElements;

/**
 * Idempotent per-CType upsert of element library demo records: every catalog
 * element gets exactly one tt_content row in the library storage folder.
 * Existing rows are updated IN PLACE (the uid is what pickers and preview
 * URLs reference, it must stay stable across re-runs); their collection
 * children and file references are rebuilt.
 *
 * Content comes from the element's own library.json, never from its
 * fixture.json: the picker preview has to read like a believable page the
 * editor could keep, not a slide that promotes the design system (which is
 * exactly what the styleguide fixture is for). Whatever library.json leaves
 * out is completed by the neutral demo value generator, so an element without
 * one still seeds a full record. Every registered Content Block provider uses
 * the shared definition registry. Native TYPO3 elements use their manifest
 * fixtures instead.
 */
final readonly class LibraryElementUpserter
{
    private const string FAL_FOLDER = 'desiderio-element-library';

    private ExtensionFalSeeder $falSeeder;
    private CollectionRecordSeeder $collectionRecordSeeder;

    public function __construct(
        private ConnectionPool $connectionPool,
        StorageRepository $storageRepository,
        private DatabaseSchemaHelper $databaseSchema,
        private StyleguideFixtureResolver $fixtureResolver,
        private CollectionCleanupService $collectionCleanupService,
        private ContentBlockCollectionMap $collectionMap,
        private FlexFormTools $flexFormTools,
    ) {
        $this->falSeeder = new ExtensionFalSeeder(
            $connectionPool,
            $storageRepository,
            $databaseSchema,
            self::FAL_FOLDER,
            1777200002,
        );
        $this->collectionRecordSeeder = new CollectionRecordSeeder(
            $connectionPool,
            $databaseSchema,
            $this->falSeeder,
        );
    }

    /**
     * @param array{cType: string, name: string, hostExtension: string, fixture: array<string, mixed>, libraryFixture?: array<string, mixed>} $element
     */
    public function upsert(int $folderPid, array $element, int $sorting, int $now): UpsertStatus
    {
        $columns = $this->databaseSchema->getColumnNames('tt_content');
        $contentData = $this->buildContentData($folderPid, $element, $sorting, $now, $columns);

        $existingUid = $this->findExistingUid($folderPid, $element['cType']);
        if ($existingUid === null) {
            $connection = $this->connectionPool->getConnectionForTable('tt_content');
            $connection->insert('tt_content', $contentData['row']);
            $contentUid = CollectionRecordSeeder::normalizeLastInsertId($connection->lastInsertId());
            $this->seedChildren($contentUid, $folderPid, $now, $contentData);
            $this->removeDuplicateVisibleRows($folderPid, $element['cType'], $contentUid, $now);
            return UpsertStatus::Created;
        }

        // uid-stable update: clear children + file references, update the row, reseed
        $this->collectionCleanupService->deleteFileReferencesForRecords('tt_content', [$existingUid]);
        $this->collectionCleanupService->deleteCollectionRowsForParentUids(
            [$existingUid],
            'tt_content',
            $this->collectionMap->getCollectionsByParentTable()
        );

        $row = $contentData['row'];
        unset($row['pid'], $row['crdate']);
        $this->connectionPool->getConnectionForTable('tt_content')->update('tt_content', $row, ['uid' => $existingUid]);
        $this->seedChildren($existingUid, $folderPid, $now, $contentData);
        $this->removeDuplicateVisibleRows($folderPid, $element['cType'], $existingUid, $now);

        return UpsertStatus::Updated;
    }

    /**
     * The library guarantee is exactly ONE visible record per CType: stray
     * visible duplicates (e.g. rows copied into the folder by hand) would
     * otherwise survive every reseed, because the upsert only ever touches
     * the lowest uid. Hidden rows are deliberately spared — they are parked
     * work-in-progress, invisible to the picker and the preview warmer.
     */
    private function removeDuplicateVisibleRows(int $folderPid, string $cType, int $keepUid, int $now): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $rows = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderPid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter($cType)),
                $queryBuilder->expr()->neq('uid', $queryBuilder->createNamedParameter($keepUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        $duplicateUids = [];
        foreach ($rows as $uid) {
            if (is_numeric($uid)) {
                $duplicateUids[] = (int)$uid;
            }
        }
        if ($duplicateUids === []) {
            return;
        }

        $this->collectionCleanupService->deleteFileReferencesForRecords('tt_content', $duplicateUids);
        $this->collectionCleanupService->deleteCollectionRowsForParentUids(
            $duplicateUids,
            'tt_content',
            $this->collectionMap->getCollectionsByParentTable()
        );

        $update = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $update
            ->update('tt_content')
            ->set('deleted', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $update->expr()->in(
                    'uid',
                    $update->createNamedParameter($duplicateUids, ArrayParameterType::INTEGER)
                )
            )
            ->executeStatement();
    }

    /**
     * Soft-deletes library records whose CType is no longer part of the catalog.
     *
     * @param list<string> $knownCTypes
     */
    public function removeObsolete(int $folderPid, array $knownCTypes, int $now): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->set('deleted', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderPid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            );
        if ($knownCTypes !== []) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn(
                    'CType',
                    $queryBuilder->createNamedParameter($knownCTypes, ArrayParameterType::STRING)
                )
            );
        }
        return $queryBuilder->executeStatement();
    }

    /**
     * @param array{cType: string, name: string, hostExtension: string, fixture: array<string, mixed>, libraryFixture?: array<string, mixed>} $element
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, column?: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string, autoplay?: bool}>>}
     */
    private function buildContentData(int $pid, array $element, int $sorting, int $now, array $columns): array
    {
        $fixture = $element['hostExtension'] === CoreContentElements::HOST
            ? $this->withPowermailDemoForm($element['cType'], $element['fixture'])
            : ($element['libraryFixture'] ?? []);

        return $this->fixtureResolver->buildContentInsert(
            $pid,
            $element['cType'],
            $element['name'],
            $fixture,
            $sorting,
            $now,
            $columns
        );
    }

    /**
     * The Powermail plugin renders nothing until its flexform names a form,
     * and a form is a record whose uid differs per installation, so the
     * static fixture cannot carry it. The preview uses a form the Powermail
     * demo seeder created (their css marks them): the appointment request,
     * which matches the preview's heading, else the contact form, else any
     * demo form. When that seeder replaces its forms it re-points this
     * record too. Without a demo form the preview keeps its heading only.
     *
     * @param array<string, mixed> $fixture
     * @return array<string, mixed>
     */
    private function withPowermailDemoForm(string $cType, array $fixture): array
    {
        if ($cType !== 'powermail_pi1' || isset($fixture['pi_flexform'])) {
            return $fixture;
        }
        $columns = $this->databaseSchema->getColumnNames('tx_powermail_domain_model_form');
        if (!isset($columns['css'])) {
            return $fixture;
        }
        $conditions = ['deleted = 0', 'css LIKE :css'];
        foreach (['hidden', 'sys_language_uid', 't3ver_wsid'] as $column) {
            if (isset($columns[$column])) {
                $conditions[] = $column . ' = 0';
            }
        }
        $forms = $this->connectionPool->getConnectionForTable('tx_powermail_domain_model_form')
            ->executeQuery(
                'SELECT uid, css FROM tx_powermail_domain_model_form WHERE ' . implode(' AND ', $conditions) . ' ORDER BY uid DESC',
                ['css' => '%desiderio-powermail-demo%'],
                ['css' => ParameterType::STRING]
            )
            ->fetchAllAssociative();
        $formUid = 0;
        foreach (['appointment', 'contact'] as $slug) {
            foreach ($forms as $form) {
                if (in_array('desiderio-powermail-' . $slug, explode(' ', (string)$form['css']), true)) {
                    $formUid = (int)$form['uid'];
                    break 2;
                }
            }
        }
        if ($formUid <= 0 && $forms !== []) {
            $formUid = (int)$forms[0]['uid'];
        }
        if ($formUid <= 0) {
            return $fixture;
        }
        $fixture['pi_flexform'] = $this->powermailFlexform($formUid);

        return $fixture;
    }

    /**
     * The plugin's main sheet: one form, all pages at once, no confirmation step. Stored as
     * DataHandler stores it, so Powermail's form overview lists the library among the pages
     * that use the form.
     */
    private function powermailFlexform(int $formUid): string
    {
        $fields = [
            'settings.flexform.main.form' => (string)$formUid,
            'settings.flexform.main.confirmation' => '0',
            'settings.flexform.main.optin' => '0',
            'settings.flexform.main.moresteps' => '0',
        ];
        $data = [];
        foreach ($fields as $field => $value) {
            $data['main']['lDEF'][$field]['vDEF'] = $value;
        }

        return $this->flexFormTools->flexArray2Xml(['data' => $data]);
    }

    /**
     * The two categorized-menu demos are the only records whose meaning lives
     * outside their own row: core inlines selected_categories into the query
     * (an empty selection is IN() — a SQL syntax error) and an unrelated
     * category shows nothing. Give the folder two demo categories, point both
     * menu records at them, and tag the table + textmedia demos so the
     * categorized-content menu lists real records. Idempotent; runs after the
     * element loop so the tagged siblings exist regardless of seed order.
     */
    public function seedCategoryDemos(int $folderPid, int $now): void
    {
        $contentConnection = $this->connectionPool->getConnectionForTable('tt_content');
        $menuUids = self::intColumn($contentConnection->fetchFirstColumn(
            "SELECT uid FROM tt_content WHERE pid = ? AND CType IN ('menu_categorized_pages', 'menu_categorized_content') AND deleted = 0",
            [$folderPid]
        ));
        if ($menuUids === []) {
            return;
        }

        $categoryConnection = $this->connectionPool->getConnectionForTable('sys_category');
        $categoryUids = [];
        foreach (['Product updates', 'Company news'] as $index => $title) {
            $existing = $categoryConnection->fetchOne(
                'SELECT uid FROM sys_category WHERE pid = ? AND title = ? AND deleted = 0',
                [$folderPid, $title]
            );
            if (is_numeric($existing)) {
                $categoryUids[] = (int)$existing;
                continue;
            }
            $categoryConnection->insert('sys_category', [
                'pid' => $folderPid,
                'tstamp' => $now,
                'crdate' => $now,
                'sorting' => ($index + 1) * 256,
                'title' => $title,
            ]);
            $categoryUids[] = CollectionRecordSeeder::normalizeLastInsertId($categoryConnection->lastInsertId());
        }

        foreach ($menuUids as $menuUid) {
            $contentConnection->update('tt_content', [
                'selected_categories' => implode(',', $categoryUids),
                'category_field' => 'categories',
            ], ['uid' => $menuUid]);
        }

        $taggedUids = self::intColumn($contentConnection->fetchFirstColumn(
            "SELECT uid FROM tt_content WHERE pid = ? AND CType IN ('table', 'textmedia') AND deleted = 0",
            [$folderPid]
        ));
        $mmConnection = $this->connectionPool->getConnectionForTable('sys_category_record_mm');
        foreach ($taggedUids as $taggedUid) {
            foreach ($categoryUids as $index => $categoryUid) {
                $exists = $mmConnection->fetchOne(
                    "SELECT 1 FROM sys_category_record_mm WHERE uid_local = ? AND uid_foreign = ? AND tablenames = 'tt_content' AND fieldname = 'categories'",
                    [$categoryUid, $taggedUid]
                );
                if ($exists === false) {
                    $mmConnection->insert('sys_category_record_mm', [
                        'uid_local' => $categoryUid,
                        'uid_foreign' => $taggedUid,
                        'tablenames' => 'tt_content',
                        'fieldname' => 'categories',
                        'sorting' => $index + 1,
                        'sorting_foreign' => $index + 1,
                    ]);
                }
            }
            $contentConnection->update('tt_content', ['categories' => count($categoryUids)], ['uid' => $taggedUid]);
        }
    }

    /**
     * @param list<mixed> $values
     * @return list<int>
     */
    private static function intColumn(array $values): array
    {
        $out = [];
        foreach ($values as $value) {
            if (is_numeric($value)) {
                $out[] = (int)$value;
            }
        }
        return $out;
    }

    /**
     * @param array{collections: array<string, array{table: string, column?: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string, autoplay?: bool}>>} $contentData
     */
    private function seedChildren(int $contentUid, int $pageUid, int $now, array $contentData): void
    {
        $this->falSeeder->seedFileReferences('tt_content', $contentUid, $pageUid, $now, $contentData['fileReferences']);
        $this->collectionRecordSeeder->seed($contentUid, $pageUid, $now, $contentData['collections']);
    }

    private function findExistingUid(int $folderPid, string $cType): ?int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $uid = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderPid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter($cType)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('uid')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return is_numeric($uid) ? (int)$uid : null;
    }
}
