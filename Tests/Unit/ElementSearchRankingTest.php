<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Library\ElementSearchService;

/**
 * Ranking of the element library search. One exact keyword hit scores the
 * same in every element that lists the term, so equal scores are common; the
 * order of such ties must follow the authored keyword order, then a title
 * match, then catalogue order - and never change where the scores differ.
 */
final class ElementSearchRankingTest extends TestCase
{
    private ElementSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new \ReflectionClass(ElementSearchService::class)->newInstanceWithoutConstructor();
    }

    public function testEqualScoresRankByThePositionOfTheMatchedKeyword(): void
    {
        // The Yoga Fitness labels (de): all three list "probestunde" among
        // their primary keywords - booking second, the CTA eighth, the hero
        // ninth. Catalogue order is deliberately the reverse.
        $matches = $this->search('probestunde', [
            'webcon_templates_hero' => [
                'title' => 'Eröffnungsfoto oder Seitenband',
                'keywords' => ['seitenband', 'einstieg', 'hero', 'seitentitel', 'kursseite', 'fotoband', 'überschrift', 'fakten', 'probestunde buchen', 'h1'],
                'group' => 'Einstiege',
            ],
            'webcon_templates_cta' => [
                'title' => 'Newsletter, Studiobox oder Abschlussband',
                'keywords' => ['handlungsaufruf', 'newsletter', 'buttons', 'nächster schritt', 'anmelden', 'abschlussband', 'box', 'probestunde buchen', 'reservieren', 'zweiter button'],
                'group' => 'Kurs buchen',
            ],
            'webcon_templates_contact' => [
                'title' => 'Besuch im Studio',
                'keywords' => ['buchung', 'probestunde', 'kontakt', 'formular', 'kurs buchen', 'ausprobieren', 'öffnungszeiten', 'nächste schritte', 'reservieren', 'besuch'],
                'group' => 'Kurs buchen',
                'description' => 'Das Buchungsformular für die kostenlose Probestunde.',
            ],
        ]);

        self::assertSame(['webcon_templates_contact', 'webcon_templates_cta', 'webcon_templates_hero'], array_column($matches, 'cType'));
        // It is a tie-break: the three scores are equal.
        self::assertCount(1, array_unique(array_column($matches, 'score')));
    }

    public function testTheBestMatchingKeywordCountsNotTheFirstPartialOne(): void
    {
        // "probestunden" (first) only matches as a prefix; the exact
        // "probestunde" (fifth) is the matched term, so B (third) leads.
        $matches = $this->search('probestunde', [
            'a' => ['title' => 'Kurspakete', 'keywords' => ['probestunden', 'paket', 'preise', 'abo', 'probestunde']],
            'b' => ['title' => 'Buchung', 'keywords' => ['buchung', 'formular', 'probestunde']],
        ]);

        self::assertSame(['b', 'a'], array_column($matches, 'cType'));
    }

    public function testAHigherScoreWinsOverAnEarlierKeyword(): void
    {
        $matches = $this->search('probestunde', [
            'keyword_first' => ['title' => 'Kurs buchen', 'keywords' => ['probestunde', 'kurs']],
            'title_match' => ['title' => 'Probestunde', 'keywords' => ['kurs', 'buchen', 'yoga', 'probestunde']],
            'synonym_only' => ['title' => 'Studio', 'keywords' => ['studio'], 'synonyms' => ['probestunde']],
        ]);

        self::assertSame(['title_match', 'keyword_first', 'synonym_only'], array_column($matches, 'cType'));
        $scores = array_column($matches, 'score');
        self::assertSame($scores, array_values(array_unique($scores)), 'the scores differ, so the score alone orders them');
    }

    public function testFullTiesKeepCatalogueOrder(): void
    {
        $texts = ['title' => 'Kurs', 'keywords' => ['probestunde']];

        self::assertSame(['first', 'second'], array_column($this->search('probestunde', ['first' => $texts, 'second' => $texts]), 'cType'));
        self::assertSame(['second', 'first'], array_column($this->search('probestunde', ['second' => $texts, 'first' => $texts]), 'cType'));
    }

    public function testOnASiteWithItsOwnLabelsCatalogueOrderIsTitleOrder(): void
    {
        // The panel lists such a site's elements by the titles it shows, so
        // a full tie follows that order rather than the host catalog's.
        $elements = [
            'zeta' => ['title' => 'Zeitplan', 'keywords' => ['probestunde']],
            'alpha' => ['title' => 'Anmeldung', 'keywords' => ['probestunde']],
        ];

        self::assertSame(['zeta', 'alpha'], array_column($this->search('probestunde', $elements), 'cType'));
        self::assertSame(['alpha', 'zeta'], array_column($this->search('probestunde', $elements, true), 'cType'));
    }

    public function testTheIndexKeepsCatalogOrderForTheVocabulary(): void
    {
        // Suggestions and "did you mean" break their own ties by vocabulary
        // order; listing a site by title must not reorder them.
        $method = new \ReflectionMethod($this->service, 'assembleIndex');
        $indexElement = new \ReflectionMethod($this->service, 'indexElement');
        $elements = [];
        foreach (['zeta' => 'Zeitplan', 'alpha' => 'Anmeldung'] as $cType => $title) {
            $elements[$cType] = $indexElement->invoke($this->service, ['title' => $title, 'keywords' => [], 'synonyms' => [], 'group' => '', 'description' => '']);
        }
        $index = $method->invoke($this->service, $elements, true);

        self::assertIsArray($index);
        self::assertIsArray($index['elements']);
        self::assertIsArray($index['vocab']);
        self::assertSame(['zeta', 'alpha'], array_keys($index['elements']));
        self::assertSame(['zeitplan', 'anmeldung'], array_keys($index['vocab']));
    }

    public function testTheEarliestKeywordOfAnyQueryWordDecides(): void
    {
        // Both match "yoga" and "kurs" once as keyword, once as synonym, so
        // the scores tie; B lists one of the two words earlier.
        $matches = $this->search('yoga kurs', [
            'a' => ['title' => 'Stundenplan', 'keywords' => ['woche', 'plan', 'zeiten', 'yoga'], 'synonyms' => ['kurs']],
            'b' => ['title' => 'Angebot', 'keywords' => ['angebot', 'kurs'], 'synonyms' => ['yoga']],
        ]);

        self::assertSame(['b', 'a'], array_column($matches, 'cType'));
        self::assertSame($matches[0]['score'], $matches[1]['score']);
    }

    public function testTheIndexRecordsTheFirstKeywordPositionOfEachWord(): void
    {
        $method = new \ReflectionMethod($this->service, 'indexElement');
        $element = $method->invoke($this->service, [
            'title' => 'Kurs buchen',
            'keywords' => ['Probestunde buchen', 'Kurs', 'Probestunde', 'Öffnungszeiten'],
            'synonyms' => ['Schnupperstunde'],
            'group' => 'Buchung',
            'description' => '',
        ]);

        self::assertIsArray($element);
        self::assertSame(
            ['probestunde' => 0, 'buchen' => 0, 'kurs' => 1, 'oeffnungszeiten' => 3],
            $element['keywordPositions'] ?? null,
        );
    }

    public function testATitleMatchBreaksATieOfScoreAndKeywordPosition(): void
    {
        $title = ['cType' => 'title', 'score' => 4.25, 'keywordPosition' => PHP_INT_MAX, 'titleMatch' => true, 'catalogPosition' => 1];
        $other = ['cType' => 'other', 'score' => 4.25, 'keywordPosition' => PHP_INT_MAX, 'titleMatch' => false, 'catalogPosition' => 0];

        self::assertSame(['title', 'other'], $this->sorted([$other, $title]));
        // ... but the keyword position comes first, the score before both.
        self::assertSame(['other', 'title'], $this->sorted([$title, [...$other, 'keywordPosition' => 3]]));
        self::assertSame(['other', 'title'], $this->sorted([$title, [...$other, 'score' => 4.251]]));
    }

    /**
     * Ranks the query against an index of the given elements, in the given
     * catalog order, as ElementSearchService::search() does after reading
     * the index.
     *
     * @param array<string, array{title: string, keywords?: list<string>, synonyms?: list<string>, group?: string, description?: string}> $elements
     * @param bool $listedByTitle as on a site with its own labels
     * @return list<array{cType: string, score: float}>
     */
    private function search(string $query, array $elements, bool $listedByTitle = false): array
    {
        $indexed = [];
        foreach ($elements as $cType => $texts) {
            $indexed[$cType] = new \ReflectionMethod($this->service, 'indexElement')
                ->invoke($this->service, $texts + ['keywords' => [], 'synonyms' => [], 'group' => '', 'description' => '']);
        }
        $index = new \ReflectionMethod($this->service, 'assembleIndex')->invoke($this->service, $indexed, $listedByTitle);
        $tokens = new \ReflectionMethod($this->service, 'tokenize')->invoke($this->service, $query);
        $result = new \ReflectionMethod($this->service, 'rank')->invoke($this->service, $tokens, $index);

        self::assertIsArray($result);
        /** @var list<array{cType: string, score: float}> $matches */
        $matches = $result['matches'];

        return $matches;
    }

    /**
     * @param list<array{cType: string, score: float, keywordPosition: int, titleMatch: bool, catalogPosition: int}> $matches
     * @return list<string>
     */
    private function sorted(array $matches): array
    {
        $compare = new \ReflectionMethod(ElementSearchService::class, 'compareMatches');
        usort($matches, static function (array $a, array $b) use ($compare): int {
            $order = $compare->invoke(null, $a, $b);
            self::assertIsInt($order);

            return $order;
        });

        return array_column($matches, 'cType');
    }
}
