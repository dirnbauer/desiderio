<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Library;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Pure-PHP, typo-tolerant search over the element catalog for the frontend
 * picker - a small "Solr without Solr": a weighted token index per element,
 * Levenshtein fuzzy matching that corrects simple typos, and Solr-style
 * autocomplete + "did you mean" suggestions.
 *
 * The whole catalog (~255 elements) is already shipped to the browser once, so
 * this endpoint returns just a RANKED list of cTypes (+ suggestions); the panel
 * reorders the items it already has. No external service, no per-element file
 * read on the hot path: the per-language index is persistently cached in the
 * existing "desiderio_library" cache (fingerprint includes the keyword files,
 * so editing a keyword self-invalidates it).
 */
final class ElementSearchService
{
    private const CACHE_IDENTIFIER = 'desiderio_library';

    /**
     * Matches below this score are dropped. Kept low on purpose so the search
     * favours recall: a single solid hit on a low-weight field (e.g. a word that
     * only appears in the description) or a fuzzy near-miss still surfaces.
     */
    private const SCORE_FLOOR = 0.3;

    /** Field weights: title beats keyword beats synonym beats group beats prose. */
    private const WEIGHT_TITLE = 10;
    private const WEIGHT_KEYWORD = 6;
    private const WEIGHT_SYNONYM = 3;
    private const WEIGHT_GROUP = 2;
    private const WEIGHT_DESCRIPTION = 1;

    /**
     * Tiny EN + DE stopword set (already umlaut-folded). Kept short on purpose:
     * only words that are pure noise in a component search.
     *
     * @var array<string, true>
     */
    private const STOPWORDS = [
        'the' => true, 'a' => true, 'an' => true, 'of' => true, 'and' => true,
        'to' => true, 'for' => true, 'with' => true, 'your' => true, 'you' => true,
        'in' => true, 'on' => true, 'or' => true, 'by' => true, 'is' => true,
        'are' => true, 'be' => true, 'as' => true, 'at' => true, 'it' => true,
        'der' => true, 'die' => true, 'das' => true, 'und' => true, 'fuer' => true,
        'mit' => true, 'ein' => true, 'eine' => true, 'im' => true, 'am' => true,
        'den' => true, 'des' => true, 'zur' => true, 'zum' => true, 'auf' => true,
        'von' => true, 'als' => true, 'sie' => true, 'ihre' => true, 'oder' => true,
    ];

    public function __construct(
        private readonly ElementCatalog $elementCatalog,
        private readonly CacheManager $cacheManager,
    ) {}

    /**
     * Ranked search for one query in the given backend-user language.
     *
     * @return array{matches: list<array{cType: string, score: float}>, suggestions: list<string>, didYouMean: string|null}
     */
    public function search(string $query, LanguageService $languageService, string $langKey): array
    {
        $empty = ['matches' => [], 'suggestions' => [], 'didYouMean' => null];

        $queryTokens = $this->tokenize($query);
        if ($queryTokens === []) {
            return $empty;
        }

        $index = $this->getIndex($languageService, $langKey);
        $vocab = $index['vocab'];

        $matches = [];
        // Per query token: did it hit something solid (exact/prefix/substring)?
        // Tokens that only matched fuzzily - or not at all - drive "did you mean".
        $tokenSolid = array_fill_keys($queryTokens, false);

        foreach ($index['elements'] as $cType => $element) {
            $tokens = $element['tokens'];
            $score = 0.0;
            $matched = 0;
            $titleHit = false;

            foreach ($queryTokens as $queryToken) {
                $best = 0.0;
                $bestSolid = false;
                $lq = strlen($queryToken);
                $maxEdits = $lq <= 3 ? 0 : ($lq <= 6 ? 1 : 2);

                foreach ($tokens as $token => $weight) {
                    $token = (string)$token;
                    $contribution = 0.0;
                    $solid = false;
                    if ($token === $queryToken) {
                        $contribution = $weight * 1.0;
                        $solid = true;
                        if ($weight === self::WEIGHT_TITLE) {
                            $titleHit = true;
                        }
                    } elseif ($lq >= 2 && str_starts_with($token, $queryToken)) {
                        $contribution = $weight * 0.85;
                        $solid = true;
                    } elseif ($lq >= 3 && str_contains($token, $queryToken)) {
                        $contribution = $weight * 0.55;
                        $solid = true;
                    } elseif ($maxEdits > 0 && abs(strlen($token) - $lq) <= $maxEdits) {
                        $distance = levenshtein($queryToken, $token);
                        if ($distance <= $maxEdits) {
                            $contribution = $weight * (0.7 - 0.18 * $distance);
                        }
                    }
                    if ($contribution > $best) {
                        $best = $contribution;
                        $bestSolid = $solid;
                    }
                }

                if ($best > 0.0) {
                    $score += $best;
                    $matched++;
                    if ($bestSolid) {
                        $tokenSolid[$queryToken] = true;
                    }
                }
            }

            if ($matched === 0) {
                continue;
            }

            // Reward covering more of the query, but give partial matches real
            // credit instead of crushing them: a strong hit on one word of a
            // multi-word query (e.g. "hero" in "blue hero") should still surface,
            // just ranked below an element that matches every word.
            $coverage = 0.5 + 0.5 * ($matched / count($queryTokens));
            $score *= $coverage;
            if ($titleHit) {
                $score *= 1.25;
            }
            if ($score >= self::SCORE_FLOOR) {
                $matches[$cType] = ['score' => $score, 'title' => $element['title']];
            }
        }

        uasort($matches, static function (array $a, array $b): int {
            $byScore = $b['score'] <=> $a['score'];
            return $byScore !== 0 ? $byScore : strcasecmp($a['title'], $b['title']);
        });

        $result = [];
        foreach ($matches as $cType => $data) {
            $result[] = ['cType' => $cType, 'score' => round($data['score'], 3)];
        }

        return [
            'matches' => $result,
            'suggestions' => $this->suggest((string)end($queryTokens), $vocab),
            'didYouMean' => $this->didYouMean($queryTokens, $tokenSolid, $vocab),
        ];
    }

    /**
     * Autocomplete completions for the last (partial) query token: vocabulary
     * tokens that the partial prefixes, or that are within one edit of it.
     * Prefix hits rank above fuzzy hits, then by field weight.
     *
     * @param array<string, int> $vocab token => max weight
     * @return list<string>
     */
    private function suggest(string $partial, array $vocab): array
    {
        if (strlen($partial) < 2) {
            return [];
        }
        $candidates = [];
        foreach ($vocab as $token => $weight) {
            $token = (string)$token;
            if ($token === $partial) {
                continue;
            }
            if (str_starts_with($token, $partial)) {
                $candidates[$token] = [2, $weight, -strlen($token)];
            } elseif (strlen($partial) >= 3 && levenshtein($partial, $token) <= 1) {
                $candidates[$token] = [1, $weight, -strlen($token)];
            }
        }
        uasort($candidates, static fn(array $a, array $b): int => $b <=> $a);
        return array_slice(array_keys($candidates), 0, 6);
    }

    /**
     * Solr-style "did you mean": when one or more query tokens never matched
     * anything solid (only fuzzy / not at all), replace each with its closest
     * vocabulary token and return the rebuilt query, else null.
     *
     * @param list<string> $queryTokens
     * @param array<string, bool> $tokenSolid
     * @param array<string, int> $vocab
     */
    private function didYouMean(array $queryTokens, array $tokenSolid, array $vocab): ?string
    {
        $corrected = [];
        $changed = false;
        foreach ($queryTokens as $queryToken) {
            if ($tokenSolid[$queryToken] ?? false) {
                $corrected[] = $queryToken;
                continue;
            }
            $budget = max(2, intdiv(strlen($queryToken), 3));
            $bestToken = null;
            $bestDistance = PHP_INT_MAX;
            $bestWeight = -1;
            foreach ($vocab as $token => $weight) {
                $token = (string)$token;
                if (abs(strlen($token) - strlen($queryToken)) > $budget) {
                    continue;
                }
                $distance = levenshtein($queryToken, $token);
                if ($distance < $bestDistance || ($distance === $bestDistance && $weight > $bestWeight)) {
                    $bestDistance = $distance;
                    $bestWeight = $weight;
                    $bestToken = $token;
                }
            }
            if ($bestToken !== null && $bestDistance >= 1 && $bestDistance <= $budget) {
                $corrected[] = $bestToken;
                $changed = true;
            } else {
                $corrected[] = $queryToken;
            }
        }
        return $changed ? implode(' ', $corrected) : null;
    }

    /**
     * Builds (or reads from cache) the weighted token index for one language.
     * Best-effort cache: a cache failure only ever slows the search, never
     * breaks it (mirrors ElementCatalog::getElementMetadata()).
     *
     * @return array{elements: array<string, array{title: string, tokens: array<string, int>}>, vocab: array<string, int>}
     */
    private function getIndex(LanguageService $languageService, string $langKey): array
    {
        $cache = null;
        $cacheKey = '';
        try {
            $cache = $this->cacheManager->getCache(self::CACHE_IDENTIFIER);
            $cacheKey = 'search-index-' . $this->sanitizeKey($langKey) . '-' . $this->elementCatalog->getSearchFingerprint();
            $cached = $cache->get($cacheKey);
            if (is_array($cached)) {
                /** @var array{elements: array<string, array{title: string, tokens: array<string, int>}>, vocab: array<string, int>} $cached */
                return $cached;
            }
        } catch (\Throwable) {
            $cache = null;
        }

        $index = $this->buildIndex($languageService);

        if ($cache !== null) {
            try {
                $cache->set($cacheKey, $index, [], 0);
            } catch (\Throwable) {
                // best-effort; serving uncached this once is fine
            }
        }

        return $index;
    }

    /**
     * @return array{elements: array<string, array{title: string, tokens: array<string, int>}>, vocab: array<string, int>}
     */
    private function buildIndex(LanguageService $languageService): array
    {
        $elements = [];
        $vocab = [];

        foreach ($this->elementCatalog->getElementMetadata() as $element) {
            $localized = $this->elementCatalog->localizeElement($element, $languageService);
            $keywords = $this->elementCatalog->localizeKeywords($element, $languageService);

            /** @var array<string, int> $tokens token => max weight */
            $tokens = [];
            $add = function (string $text, int $weight) use (&$tokens): void {
                foreach ($this->tokenize($text) as $token) {
                    if (!isset($tokens[$token]) || $tokens[$token] < $weight) {
                        $tokens[$token] = $weight;
                    }
                }
            };

            $add($localized['title'], self::WEIGHT_TITLE);
            foreach ($keywords['keywords'] as $keyword) {
                $add($keyword, self::WEIGHT_KEYWORD);
            }
            foreach ($keywords['synonyms'] as $synonym) {
                $add($synonym, self::WEIGHT_SYNONYM);
            }
            $add($element['group'], self::WEIGHT_GROUP);
            $add($localized['description'], self::WEIGHT_DESCRIPTION);

            foreach ($tokens as $token => $weight) {
                if (!isset($vocab[$token]) || $vocab[$token] < $weight) {
                    $vocab[$token] = $weight;
                }
            }

            $elements[$element['cType']] = [
                'title' => $localized['title'],
                'tokens' => $tokens,
            ];
        }

        return ['elements' => $elements, 'vocab' => $vocab];
    }

    /**
     * Lowercase, fold German umlauts/ß and transliterate diacritics to ASCII,
     * then split on non-alphanumerics. Folding MUST happen before any
     * levenshtein()/strlen() downstream, because those are byte-based and would
     * miscount multibyte characters.
     *
     * @return list<string>
     */
    private function tokenize(string $text): array
    {
        $text = $this->normalize($text);
        $parts = preg_split('/[^a-z0-9]+/', $text);
        if ($parts === false) {
            return [];
        }
        $out = [];
        foreach ($parts as $part) {
            if (strlen($part) < 2 || isset(self::STOPWORDS[$part])) {
                continue;
            }
            $out[] = $part;
        }
        return $out;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, [
            'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e',
            'ê' => 'e', 'ë' => 'e', 'í' => 'i', 'î' => 'i', 'ñ' => 'n', 'ó' => 'o',
            'ô' => 'o', 'ú' => 'u', 'û' => 'u',
        ]);
        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($transliterated !== false) {
            $text = strtolower($transliterated);
        }
        return $text;
    }

    private function sanitizeKey(string $langKey): string
    {
        $clean = preg_replace('/[^a-z0-9]/i', '', $langKey);
        return $clean === null || $clean === '' ? 'default' : strtolower($clean);
    }
}
