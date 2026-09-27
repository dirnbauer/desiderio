<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers\Blog;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Webconsulting\Desiderio\ViewHelpers\AbstractRequestAwareViewHelper;

/**
 * Minutes a reader needs for a blog post, counted from the text of the
 * content elements on the post page (header, subheader, bodytext).
 *
 * A post is a page (EXT:blog doktype 137), so the post's uid is the pid of
 * its content. The language comes from the request; a translation without
 * content of its own falls back to the default language, as the page does.
 * Words are counted on whitespace; Chinese, Japanese and Korean characters
 * are counted one by one at 400 characters a minute. Never less than 1.
 *
 *   <di:blog.readingTime page="{post.uid}" />
 */
final class ReadingTimeViewHelper extends AbstractRequestAwareViewHelper
{
    private const int CJK_CHARACTERS_PER_MINUTE = 400;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function initializeArguments(): void
    {
        $this->registerArgument('page', 'int', 'uid of the post page (default language)', true);
        $this->registerArgument('wordsPerMinute', 'int', 'Reading speed in words per minute', false, 200);
    }

    public function render(): int
    {
        $pageUid = $this->intArgument('page', 0);
        if ($pageUid <= 0) {
            return 1;
        }
        $wordsPerMinute = max(1, $this->intArgument('wordsPerMinute', 200));

        $language = $this->request()?->getAttribute('language');
        $languageId = $language instanceof SiteLanguage ? $language->getLanguageId() : 0;

        $texts = $this->contentTexts($pageUid, $languageId);
        if ($texts === [] && $languageId > 0) {
            $texts = $this->contentTexts($pageUid, 0);
        }

        $text = html_entity_decode(strip_tags(implode(' ', $texts)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cjkCharacters = (int)preg_match_all('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]/u', $text);
        $latinText = (string)preg_replace('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]/u', ' ', $text);
        $wordList = preg_split('/\s+/u', trim($latinText), -1, PREG_SPLIT_NO_EMPTY);
        $words = $wordList === false ? 0 : count($wordList);

        $minutes = $words / $wordsPerMinute + $cjkCharacters / self::CJK_CHARACTERS_PER_MINUTE;

        return max(1, (int)ceil($minutes));
    }

    /**
     * @return list<string>
     */
    private function contentTexts(int $pageUid, int $languageId): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $rows = $queryBuilder
            ->select('header', 'subheader', 'bodytext')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('colPos', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->in(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter([$languageId, -1], ArrayParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $texts = [];
        foreach ($rows as $row) {
            foreach (['header', 'subheader', 'bodytext'] as $field) {
                $value = $row[$field] ?? '';
                if (is_string($value) && $value !== '') {
                    $texts[] = $value;
                }
            }
        }

        return $texts;
    }
}
