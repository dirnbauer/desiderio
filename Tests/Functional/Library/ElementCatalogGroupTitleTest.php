<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Library;

use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Library\ElementCatalog;

/**
 * The element library panel shows group names exactly as they are written,
 * so the catalog has to hand it titles, not group keys: a host group shows
 * the label its host registered for the New Content Element wizard, in the
 * editor's language, and a site's own group name stays as the site wrote it.
 */
final class ElementCatalogGroupTitleTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'fluid_styled_content',
        'form',
        'workspaces',
    ];

    protected array $testExtensionsToLoad = [
        'webconsulting/desiderio',
    ];

    public function testAHostGroupShowsItsRegisteredTitle(): void
    {
        $catalog = $this->get(ElementCatalog::class);
        self::assertInstanceOf(ElementCatalog::class, $catalog);
        $languageServiceFactory = $this->get(LanguageServiceFactory::class);

        self::assertSame('Trust & Social Proof', $catalog->groupTitle('social-proof', $languageServiceFactory->create('default')));
        self::assertSame('Vertrauen & Referenzen', $catalog->groupTitle('social-proof', $languageServiceFactory->create('de')));
        self::assertSame('Hero & Seiteneinstiege', $catalog->groupTitle('hero', $languageServiceFactory->create('de')));
    }

    public function testAGroupWithoutRegisteredTitleStaysAsWritten(): void
    {
        $catalog = $this->get(ElementCatalog::class);
        self::assertInstanceOf(ElementCatalog::class, $catalog);
        $language = $this->get(LanguageServiceFactory::class)->create('de');

        // A site's own group name (elementLibrary.labels) - not a group key.
        self::assertSame('Büro und Notizen', $catalog->groupTitle('Büro und Notizen', $language));
        self::assertSame('Kurs buchen', $catalog->groupTitle('Kurs buchen', $language));
        self::assertSame('no-such-group', $catalog->groupTitle('no-such-group', $language));
    }
}
