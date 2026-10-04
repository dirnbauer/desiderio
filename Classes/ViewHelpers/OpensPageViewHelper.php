<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Whether a content element opens the page: it is the first element of the
 * main column (colPos 0, the first column in every Desiderio page layout) on
 * the page being rendered, right below the page title. Its picture is usually
 * the largest paint, so it should load at once instead of lazily.
 *
 * The column is read the way the page reads it (ContentObjectRenderer::
 * getRecords: visibility, start/stop, access, language overlay, workspace), so
 * a hidden element before it does not count, and an element shown on another
 * page by reference does not open that page. Without a request (backend
 * preview, CLI) the answer is false.
 *
 *     <f:variable name="opensPage" value="{dv:opensPage(record: data)}"/>
 */
final class OpensPageViewHelper extends AbstractRequestAwareViewHelper
{
    private const int MAIN_COLUMN = 0;

    /**
     * A boolean for f:variable and conditions, not text.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('record', RecordInterface::class, 'The content element (tt_content record)', true);
    }

    public function render(): bool
    {
        $record = $this->arguments['record'] ?? null;
        $request = $this->request();
        if (
            !$record instanceof RecordInterface
            || $request === null
            || $record->getMainType() !== 'tt_content'
            || !$record->has('colPos')
            || (int)$record->get('colPos') !== self::MAIN_COLUMN
        ) {
            return false;
        }

        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->setRequest($request);
        // The whole column, not one row: a language overlay may drop the
        // first default-language element, and then the next one opens the page.
        $first = $contentObjectRenderer->getRecords('tt_content', [
            'where' => '{#colPos}=' . self::MAIN_COLUMN,
            'orderBy' => 'sorting',
        ])[0] ?? null;
        if (!is_array($first)) {
            return false;
        }

        $uid = $record->getUid();

        return $uid === (int)($first['uid'] ?? 0)
            || $uid === (int)($first['_LOCALIZED_UID'] ?? 0);
    }
}
