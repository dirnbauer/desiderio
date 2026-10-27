<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseLegalPages;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

/**
 * Every menu entry explains itself: the menu shows the nav title, and the
 * page abstract is the summary search engines and the error page fall back
 * to. A first-level name stays short enough for the header bar.
 */
final class MenuNamesTest extends TestCase
{
    private const int FIRST_LEVEL_MAX = 20;
    private const int DEEPER_LEVEL_MAX = 42;
    private const int ABSTRACT_MAX = 160;

    public function testEveryMenuPageHasAShortNavTitleAndAnAbstract(): void
    {
        $legalSlugs = array_column(ShowcaseLegalPages::pages(), 'slug');
        $problems = [];
        foreach (StyleguideShowcasePages::subpages() as $page) {
            if (($page['hideInNav'] ?? false) || in_array($page['slug'], $legalSlugs, true)) {
                continue;
            }
            $max = $page['parentSlug'] === null ? self::FIRST_LEVEL_MAX : self::DEEPER_LEVEL_MAX;
            if (trim($page['navTitle']) === '' || mb_strlen($page['navTitle']) > $max) {
                $problems[] = sprintf('%s: nav title "%s" must have 1 to %d characters', $page['slug'], $page['navTitle'], $max);
            }
            if (trim($page['abstract']) === '') {
                $problems[] = sprintf('%s: the abstract is empty', $page['slug']);
            }
        }

        self::assertSame([], $problems);
    }

    /**
     * The success stories are invented. Their menu names say so, so no entry
     * reads like a real customer.
     */
    public function testEverySuccessStoryIsNamedAsAnExample(): void
    {
        foreach (StyleguideShowcasePages::subpages() as $page) {
            if ($page['parentSlug'] !== 'success-stories' || ($page['hideInNav'] ?? false)) {
                continue;
            }
            self::assertStringStartsWith('Example: ', $page['navTitle'], $page['slug']);
        }
    }

    /**
     * The chapter pages keep the content wizard's group title as their page
     * title; their menu name and abstract say what the elements are for, and
     * the abstract opens with the group's element count.
     */
    public function testEveryChapterPageHasMenuTextsWithItsElementCount(): void
    {
        $slugs = (new \ReflectionClass(SeedStyleguidePagesCommand::class))->getConstant('CONTENT_TYPE_GROUP_SLUGS');
        self::assertIsArray($slugs);
        $groups = json_decode((string)file_get_contents(dirname(__DIR__, 2) . '/Resources/Private/Data/styleguide-content-groups.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($groups);
        $groupSizes = [];
        foreach ($groups as $group) {
            self::assertIsArray($group);
            self::assertIsArray($group['elements'] ?? null);
            $groupSizes[(string)($group['groupId'] ?? '')] = count($group['elements']);
        }

        foreach (array_keys($slugs) as $groupId) {
            $groupId = (string)$groupId;
            $chapterPage = StyleguideContentGroups::chapterPage($groupId);
            self::assertNotNull($chapterPage, 'Chapter ' . $groupId . ' has no menu texts.');
            self::assertLessThanOrEqual(self::DEEPER_LEVEL_MAX, mb_strlen($chapterPage['navTitle']), $groupId);
            self::assertLessThanOrEqual(self::ABSTRACT_MAX, mb_strlen($chapterPage['abstract']), $groupId);
            self::assertArrayHasKey($groupId, $groupSizes);
            self::assertStringStartsWith($groupSizes[$groupId] . ' ', $chapterPage['abstract'], $groupId);
        }
    }

    public function testEveryAdoptedMenuPageHasShortMenuTexts(): void
    {
        foreach (StyleguideShowcasePages::adoptedMenuTexts() as $slug => $menuTexts) {
            self::assertNotSame('', $menuTexts['navTitle'], $slug);
            self::assertLessThanOrEqual(self::DEEPER_LEVEL_MAX, mb_strlen($menuTexts['navTitle']), $slug);
            self::assertNotSame('', $menuTexts['abstract'], $slug);
            self::assertLessThanOrEqual(self::ABSTRACT_MAX, mb_strlen($menuTexts['abstract']), $slug);
        }
    }
}
