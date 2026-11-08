<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseVisualEditorInlinePage;

/**
 * Renders single elements of the seeded Visual Editor inline showcase page
 * through the element preview page type, for visitors and in the Visual
 * Editor's edit mode (a real backend session cookie: the Visual Editor
 * creates form tokens, which the testing framework's session-less backend
 * user cannot).
 *
 * The test case loads content-blocks, visual-editor, vite-asset-collector and
 * desiderio, rte_ckeditor among the core extensions, and sets
 * FE.cacheHash.enforceValidation=false, BE.lockIP(v6)=0 and
 * EXTENSIONS.vite_asset_collector.useDevServer=1.
 *
 * @phpstan-require-extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
 */
trait ShowcasePreviewRendering
{
    private ?string $backendSession = null;

    /**
     * Seeds the Visual Editor inline showcase page below the root page and
     * points the element preview page type (?type=1777200001&elPreview=<uid>)
     * at it; returns the showcase page uid.
     */
    private function seedShowcaseWithPreviewSite(): int
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SeedingBase.csv');
        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        $connection->insert('be_users', ['uid' => 1, 'pid' => 0, 'username' => 'admin', 'password' => 'x', 'admin' => 1]);
        $connection->insert('pages', ['pid' => 1, 'title' => 'Content types', 'slug' => '/content-types', 'doktype' => 1]);
        foreach (['/content-types/rte-combinations', '/pricing/order', '/privacy', '/imprint', '/terms'] as $slug) {
            $connection->insert('pages', ['pid' => 1, 'title' => $slug, 'slug' => $slug, 'doktype' => 1]);
        }
        $tester = new CommandTester($this->get(SeedStyleguidePagesCommand::class));
        self::assertSame(Command::SUCCESS, $tester->execute(['--parent' => '1', '--pages' => ShowcaseVisualEditorInlinePage::SLUG]), $tester->getDisplay());
        $pageUid = (int)$connection->executeQuery('SELECT uid FROM pages WHERE slug = ? AND deleted = 0', [ShowcaseVisualEditorInlinePage::SLUG])->fetchOne();

        $siteWriter = $this->get(SiteWriter::class);
        $siteWriter->write('main', [
            'rootPageId' => 1,
            'base' => 'http://localhost/',
            'dependencies' => ['webconsulting/desiderio-content-elements'],
            'languages' => [['title' => 'English', 'enabled' => true, 'languageId' => 0, 'base' => '/', 'locale' => 'en_US.UTF-8']],
        ]);
        $siteWriter->writeSettings('main', ['elementLibrary' => ['storagePid' => $pageUid]]);

        return $pageUid;
    }

    private function renderElement(int $uid, bool $editMode): string
    {
        $parameters = ['type' => 1777200001, 'elPreview' => $uid];
        $request = new InternalRequest('http://localhost/');
        if ($editMode) {
            $parameters['editMode'] = 1;
            $request = $request->withCookieParams(['be_typo_user' => $this->backendSessionJwt()]);
        }
        $response = $this->executeFrontendSubRequest($request->withQueryParameters($parameters), new InternalRequestContext());
        self::assertSame(200, $response->getStatusCode(), 'elPreview=' . $uid . ($editMode ? ' (edit mode)' : ''));

        return (string)$response->getBody();
    }

    private function backendSessionJwt(): string
    {
        if ($this->backendSession === null) {
            $user = $this->getBackendUserRecordFromDatabase(1);
            self::assertIsArray($user);
            $this->backendSession = GeneralUtility::makeInstance(BackendUserAuthentication::class)->createUserSession($user)->getJwt();
        }
        return $this->backendSession;
    }

    /**
     * Visible inline children of a record, recursively, grouped by the
     * parent's inline column: column => sorted list of "<table>:<uid>".
     *
     * @return array<string, list<string>>
     */
    private function visibleChildren(string $table, int $uid): array
    {
        $children = [];
        $row = $this->getConnectionPool()->getConnectionForTable($table)->select(['*'], $table, ['uid' => $uid])->fetchAssociative();
        self::assertIsArray($row);
        $typeField = $GLOBALS['TCA'][$table]['ctrl']['type'] ?? null;
        $type = is_string($typeField) ? (string)($row[$typeField] ?? '') : '';
        $showitem = (string)($GLOBALS['TCA'][$table]['types'][$type]['showitem'] ?? $GLOBALS['TCA'][$table]['types']['1']['showitem'] ?? $GLOBALS['TCA'][$table]['types']['0']['showitem'] ?? '');
        foreach ($GLOBALS['TCA'][$table]['columns'] as $column => $definition) {
            $config = array_replace_recursive($definition['config'] ?? [], $GLOBALS['TCA'][$table]['types'][$type]['columnsOverrides'][$column]['config'] ?? []);
            if (($config['type'] ?? '') !== 'inline' || !is_string($config['foreign_table'] ?? null) || preg_match('/(^|[,;\s])' . preg_quote((string)$column, '/') . '([,;\s]|$)/', $showitem) !== 1) {
                continue;
            }
            $childTable = $config['foreign_table'];
            $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($childTable);
            $queryBuilder->select('uid')->from($childTable)->where($queryBuilder->expr()->eq((string)$config['foreign_field'], $uid));
            if (isset($config['foreign_table_field'])) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq((string)$config['foreign_table_field'], $queryBuilder->createNamedParameter($table)));
            }
            foreach ($config['foreign_match_fields'] ?? [] as $field => $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq((string)$field, $queryBuilder->createNamedParameter($value)));
            }
            foreach ($queryBuilder->executeQuery()->fetchFirstColumn() as $childUid) {
                $children[(string)$column][] = $childTable . ':' . $childUid;
                foreach ($this->visibleChildren($childTable, (int)$childUid) as $nestedColumn => $nested) {
                    $children[$nestedColumn] = [...$children[$nestedColumn] ?? [], ...$nested];
                }
            }
        }
        foreach ($children as &$items) {
            sort($items);
        }
        unset($items);
        ksort($children);

        return $children;
    }
}
