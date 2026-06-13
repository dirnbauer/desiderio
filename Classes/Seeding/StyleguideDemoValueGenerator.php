<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\DataHandling\IconItemsProcessor;
use Webconsulting\Desiderio\Icon\IconRegistry;
use Webconsulting\Desiderio\Rte\RteHtmlConverter;

/**
 * Generates deterministic demo field values for styleguide seed fixtures.
 *
 * The demo vocabulary (badges, copy, people, …) is exposed through protected
 * provider methods so a subclass can swap it without touching the field-type
 * heuristics. {@see ElementLibraryValueGenerator} uses this to produce neutral,
 * non-promotional examples for the element library picker.
 */
class StyleguideDemoValueGenerator
{
    public const FIELD_SKIP = '__skip__';

    /** @return list<string> */
    protected function demoBadges(): array
    {
        return [
            'shadcn/ui',
            'TYPO3 Form Framework',
            'Fluid 5.3',
            'A11y checked',
            'Responsive by default',
            'Editor-ready',
        ];
    }

    /** @return list<string> */
    protected function demoButtonLabels(): array
    {
        return [
            'View pattern',
            'Open preview',
            'Compare states',
            'Get checklist',
            'Start conversation',
        ];
    }

    /** @return list<string> */
    protected function demoCopy(): array
    {
        return [
            'Lead with the decision visitors need to make, then support it with compact proof and a clear next step.',
            'Keep the surface calm, scannable, and token-driven so editors can reuse it across real TYPO3 pages.',
            'Balance short copy, responsive spacing, and accessible controls so mobile stacks stay intentional.',
            'Pair concrete product context with restrained shadcn surfaces, visible focus states, and useful media.',
            'Use specific labels, realistic numbers, and TYPO3-native form handling so the preview feels publish-ready.',
        ];
    }

    /** @return list<string> */
    protected function demoFeatures(): array
    {
        return [
            'Theme-aware states',
            'Keyboard focus built in',
            'Reusable Content Blocks',
            'Responsive editorial density',
            'Token-based chart colors',
            'Curated demo media',
            'TYPO3 Form finishers',
        ];
    }

    /** @return list<string> */
    protected function demoLinkLabels(): array
    {
        return ['Overview', 'Components', 'Examples', 'Pricing', 'Contact'];
    }

    /** @return list<array{0: string, 1: string, 2: string}> */
    protected function demoPeople(): array
    {
        return [
            ['Mara Weiss', 'Product Design Lead', 'Northstar Labs'],
            ['Jonas Klein', 'Frontend Engineer', 'Studio Atlas'],
            ['Sofia Berg', 'Customer Success', 'Helio Systems'],
            ['Noah Ritter', 'Content Architect', 'Vienna Digital'],
            ['Lea Fischer', 'Accessibility Reviewer', 'Signal Bureau'],
        ];
    }

    /** @return list<string> */
    protected function demoSubjects(): array
    {
        return [
            'Launch Readiness Review',
            'Pattern Library Rollout',
            'Content Operations Brief',
            'Customer Evidence Hub',
            'Product Adoption Report',
            'Editor Workflow Upgrade',
            'Service Performance Snapshot',
            'Reusable Section Blueprint',
        ];
    }

    /** @return list<string> */
    protected function demoTabPanelCopy(): array
    {
        return [
            'Organize related topics into focused panels so visitors compare options without leaving the page. The first tab stays selected by default, and spacing stays aligned with the rest of your editorial layout.',
            'Preview how tab labels wrap, how icons align, and how panel copy scales on smaller breakpoints. Each seeded panel carries enough text to judge line length, hierarchy, and the gap between the tab list and body content.',
            'Give editors realistic labels, icons, and body copy so previews feel publish-ready. The tabs element should read like a finished section, not a placeholder, which makes spacing and default-state checks faster during QA.',
        ];
    }

    /** @return list<string> */
    protected function demoTopics(): array
    {
        return ['Artikel Hero', 'Content Strategy', 'Editorial Systems', 'Launch Notes', 'Customer Stories'];
    }

    protected function demoRowData(int $index): string
    {
        return ['Components|Ready|98%', 'Tokens|Synced|24', 'A11y|Passing|AA'][$index % 3];
    }

    protected function demoTierValues(): string
    {
        return 'Included,Token based,Priority review';
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function buildDefaultFieldValue(string $ctype, string $name, string $field, array $fieldConfig, int $index): mixed
    {
        $chartDefault = $this->buildDefaultChartFieldValue($ctype, $field, $fieldConfig, $index);
        if ($chartDefault !== null) {
            return $chartDefault;
        }

        $configuredType = $fieldConfig['type'] ?? 'Textarea';
        $type = is_scalar($configuredType) ? (string)$configuredType : 'Textarea';

        $value = match ($type) {
            'Checkbox' => 1,
            'Date' => $this->buildDefaultDateTimestamp($index),
            'DateTime' => $this->buildDefaultDateTimeTimestamp($index),
            'File' => self::FIELD_SKIP,
            'Link' => $this->buildDefaultLinkValue($field, $index),
            'Number' => $this->buildDefaultNumberValue($field, $fieldConfig, $index),
            'Select' => $this->buildDefaultSelectValue($fieldConfig),
            default => $this->buildDefaultTextValue($ctype, $name, $field, $index),
        };

        // enableRichtext fields store RTE HTML; wrap plain fallback copy so
        // seeded records match what CKEditor would save.
        if (($fieldConfig['enableRichtext'] ?? false) === true
            && is_string($value)
            && $value !== ''
            && !RteHtmlConverter::looksLikeHtml($value)
        ) {
            $value = RteHtmlConverter::convert($value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function buildDefaultChartFieldValue(string $ctype, string $field, array $fieldConfig, int $index): mixed
    {
        if ($this->normalizeIdentifier($ctype) !== 'desideriochart') {
            return null;
        }

        $normalizedField = $this->normalizeIdentifier($field);
        $value = match ($normalizedField) {
            'chartdata' => $this->buildDefaultChartData($index),
            'charttype' => ['area', 'line', 'bar', 'horizontal_bar'][$index % 4],
            'colorvariant' => ['primary', 'blue', 'green', 'orange', 'red'][$index % 5],
            'showgrid', 'showlegend', 'showvalues' => 1,
            'legendposition' => $index % 4 === 3 ? 'right' : 'bottom',
            'filltype' => $index % 2 === 0 ? 'gradient' : 'solid',
            'chartheight' => ['medium', 'large', 'small'][$index % 3],
            default => null,
        };

        if ($value === null || ($fieldConfig['type'] ?? null) !== 'Select') {
            return $value;
        }

        return $this->normalizeSelectDefaultValue($fieldConfig, $value);
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function normalizeSelectDefaultValue(array $fieldConfig, mixed $preferredValue): mixed
    {
        $itemValues = $this->getSelectItemValues($fieldConfig);
        if ($itemValues === []) {
            return $preferredValue;
        }

        if (!is_scalar($preferredValue)) {
            return $this->buildDefaultSelectValue($fieldConfig);
        }

        $normalizedPreferredValue = (string)$preferredValue;
        foreach ($itemValues as $itemValue) {
            if (is_scalar($itemValue) && (string)$itemValue === $normalizedPreferredValue) {
                return $itemValue;
            }
        }

        return $this->buildDefaultSelectValue($fieldConfig);
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function buildDefaultNumberValue(string $field, array $fieldConfig, int $index): int
    {
        if (isset($fieldConfig['default']) && is_numeric($fieldConfig['default'])) {
            return (int)$fieldConfig['default'];
        }

        $normalizedField = $this->normalizeIdentifier($field);

        return match (true) {
            str_contains($normalizedField, 'rating') => 5,
            $normalizedField === 'defaulttab' => 0,
            str_contains($normalizedField, 'columns') => [3, 4, 2][$index % 3],
            str_contains($normalizedField, 'duration') => 45,
            str_contains($normalizedField, 'interval') => 6000,
            str_contains($normalizedField, 'percent') => max(80, 96 - $index),
            $this->fieldIdentifierContainsAnyWord($field, ['count', 'counter', 'total', 'quantity', 'qty']) => [128, 2400, 86, 12][$index % 4],
            str_contains($normalizedField, 'year') => 2026,
            default => [12, 24, 48, 72, 96][$index % 5],
        };
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function buildDefaultSelectValue(array $fieldConfig): mixed
    {
        $itemValues = $this->getSelectItemValues($fieldConfig);

        if (array_key_exists('default', $fieldConfig) && $fieldConfig['default'] !== '') {
            if ($itemValues === []) {
                return $fieldConfig['default'];
            }

            $default = $fieldConfig['default'];
            $defaultString = is_scalar($default) ? (string)$default : '';
            foreach ($itemValues as $itemValue) {
                if (is_scalar($itemValue) && (string)$itemValue === $defaultString) {
                    return $itemValue;
                }
            }
        }

        if ($itemValues !== []) {
            return $itemValues[0];
        }

        return '';
    }

    /**
     * @param array<string, mixed> $fixture
     */
    public function buildFixtureBackedFieldValue(string $field, array $fixture): mixed
    {
        if ($field === 'chart_data') {
            return $this->buildChartDataJsonFromFixture($fixture);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $fixture
     */
    public function buildChartDataJsonFromFixture(array $fixture): ?string
    {
        $stats = $fixture['stats'] ?? null;
        if (!is_array($stats)) {
            return null;
        }

        $points = [];
        foreach ($stats as $stat) {
            if (!is_array($stat)) {
                continue;
            }

            $labelValue = $stat['label'] ?? $stat['title'] ?? $stat['name'] ?? '';
            $label = trim(is_scalar($labelValue) ? (string)$labelValue : '');
            $value = $this->parseFixtureChartNumber($stat['value'] ?? $stat['amount'] ?? $stat['number'] ?? null);
            if ($label === '' || $value === null) {
                continue;
            }

            $points[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        if ($points === []) {
            return null;
        }

        $json = json_encode($points, JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : null;
    }

    public function parseFixtureChartNumber(mixed $value): int|float|null
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = str_replace([',', ' '], '', (string)$value);
        if (preg_match('/-?\d+(?:\.\d+)?/', $normalized, $matches) !== 1) {
            return null;
        }

        $number = (float)$matches[0];

        return floor($number) === $number ? (int)$number : $number;
    }

    public function buildDefaultTextValue(string $ctype, string $name, string $field, int $index): string
    {
        $normalizedField = $this->normalizeIdentifier($field);
        $subject = $this->buildDemoSubject($name, $index);
        $elementLabel = $this->buildReadableLabel($name);
        $fieldLabel = $this->buildReadableLabel($field);
        $people = $this->demoPeople();
        $person = $people[$index % count($people)];

        return match (true) {
            preg_match('/^(?:link|child)(\d+)label$/', $normalizedField, $matches) === 1 => $this->getDefaultLinkLabel((int)$matches[1]),
            $normalizedField === 'accepttext' => 'Accept all',
            $normalizedField === 'declinetext' => 'Decline',
            $normalizedField === 'settingstext' => 'Settings',
            $normalizedField === 'privacytext' => 'Privacy',
            str_contains($normalizedField, 'authortitle') || str_contains($normalizedField, 'jobtitle') || str_contains($normalizedField, 'role') || str_contains($normalizedField, 'position') => $person[1],
            str_contains($normalizedField, 'subheadline') => $this->buildDefaultDemoCopy($elementLabel, $fieldLabel, $index),
            $normalizedField === 'header' || str_contains($normalizedField, 'headline') || str_contains($normalizedField, 'title') => $subject,
            str_contains($normalizedField, 'eyebrow') || str_contains($normalizedField, 'badge') || str_contains($normalizedField, 'kicker') || str_contains($normalizedField, 'status') => $this->pickDemoString($this->demoBadges(), $name . '-' . $field, $index),
            str_contains($normalizedField, 'url') || str_contains($normalizedField, 'href') => $this->buildDefaultUrlTextValue($ctype, $field, $index),
            str_contains($normalizedField, 'alt') || str_contains($normalizedField, 'alternative') => 'Accessible demo image for ' . $subject . '.',
            str_contains($normalizedField, 'copyright') => 'Images are credited on their Unsplash file references.',
            str_contains($normalizedField, 'credit') || str_contains($normalizedField, 'source') || str_contains($normalizedField, 'photographer') => 'Photo source: Unsplash demo image with photographer credit stored on the file reference.',
            str_contains($normalizedField, 'quote') => $this->buildDefaultQuote($elementLabel),
            $normalizedField === 'tabcontent' => $this->buildDefaultTabPanelCopy($index),
            str_contains($normalizedField, 'description') || str_contains($normalizedField, 'content') || str_contains($normalizedField, 'body') || str_contains($normalizedField, 'copy') || str_contains($normalizedField, 'summary') || str_contains($normalizedField, 'bio') => $this->buildDefaultDemoCopy($elementLabel, $fieldLabel, $index),
            $normalizedField === 'prefix' => '',
            $normalizedField === 'suffix' => $this->buildDefaultMetricSuffix($ctype, $index),
            $normalizedField === 'step' => ['Plan', 'Build', 'Review', 'Publish'][$index % 4],
            $normalizedField === 'topic' || str_contains($normalizedField, 'topic') => $this->pickDemoString($this->demoTopics(), $name . '-' . $field, $index),
            str_contains($normalizedField, 'readingtime') || (str_contains($normalizedField, 'reading') && str_contains($normalizedField, 'time')) => 'Dauer in min',
            $normalizedField === 'meta' => '5min read',
            $normalizedField === 'language' => 'PHP',
            $normalizedField === 'filename' => 'ArticleTeaserRenderer.php',
            $normalizedField === 'code' => $this->buildDefaultCodeBlockValue(),
            str_contains($normalizedField, 'ctatext') || str_contains($normalizedField, 'buttontext') || str_contains($normalizedField, 'submittext') => $this->buildDefaultButtonText($normalizedField, $elementLabel),
            str_contains($normalizedField, 'linktext') => $this->pickDemoString($this->demoButtonLabels(), $name . '-' . $field, $index),
            str_contains($normalizedField, 'placeholder') => 'name@example.com',
            $normalizedField === 'text' => $this->pickDemoString($this->demoFeatures(), $name . '-' . $field, $index),
            $normalizedField === 'unitlabel' => $this->buildDefaultUnitLabel($ctype),
            $normalizedField === 'volume' => $this->buildDefaultVolumeLabel($index),
            str_contains($normalizedField, 'feature') || str_contains($normalizedField, 'points') || str_contains($normalizedField, 'specs') => $this->buildDefaultList($this->demoFeatures(), $name . '-' . $field, $index),
            str_contains($normalizedField, 'links') || str_contains($normalizedField, 'pages') || str_contains($normalizedField, 'children') => implode("\n", $this->demoLinkLabels()),
            str_contains($normalizedField, 'members') || str_contains($normalizedField, 'people') => $this->buildDefaultPeopleList(),
            $normalizedField === 'chartdata' || (str_contains($normalizedField, 'chart') && str_contains($normalizedField, 'data')) => $this->buildDefaultChartData($index),
            str_contains($normalizedField, 'rowdata') => $this->demoRowData($index),
            str_contains($normalizedField, 'tiervalues') => $this->demoTierValues(),
            str_contains($normalizedField, 'columnkey') => $this->buildColumnKey($subject . ' ' . ($index + 1)),
            str_contains($normalizedField, 'columnlabel') => $fieldLabel . ' ' . ($index + 1),
            str_contains($normalizedField, 'align') => 'left',
            str_contains($normalizedField, 'company') || str_contains($normalizedField, 'brand') => $person[2],
            str_contains($normalizedField, 'name') || str_contains($normalizedField, 'author') => $person[0],
            str_contains($normalizedField, 'email') => 'hello@example.com',
            str_contains($normalizedField, 'phone') || str_contains($normalizedField, 'tel') => '+43 1 555 010' . ($index + 1),
            str_contains($normalizedField, 'address') || str_contains($normalizedField, 'location') => 'Mariahilfer Strasse 42, 1070 Vienna',
            str_contains($normalizedField, 'date') => 'May ' . min(28, $index + 8) . ', 2026',
            str_contains($normalizedField, 'year') => '2026',
            str_contains($normalizedField, 'trend') => ['positive', 'stable', 'up'][$index % 3],
            $this->fieldIdentifierContainsAnyWord($field, ['count', 'counter', 'total', 'quantity', 'qty']) => ['128', '2.4K', '86', '12'][$index % 4],
            str_contains($normalizedField, 'value') || str_contains($normalizedField, 'metric') => ['98%', '24K', '4.9', '12 ms', 'AA'][$index % 5],
            str_contains($normalizedField, 'label') => $this->pickDemoString($this->demoFeatures(), $name . '-' . $field, $index),
            str_contains($normalizedField, 'price') => '$' . [19, 49, 99, 249][$index % 4],
            str_contains($normalizedField, 'period') || str_contains($normalizedField, 'billing') => '/month',
            str_contains($normalizedField, 'size') => '2.4 MB',
            str_contains($normalizedField, 'icon') => $this->pickDemoString(IconRegistry::demoKeys(), $name . '-' . $field, $index),
            str_contains($normalizedField, 'gradientto') => 'accent',
            str_contains($normalizedField, 'gradient') => 'primary',
            str_contains($normalizedField, 'color') => 'primary',
            default => $fieldLabel . ' for ' . $subject,
        };
    }

    public function buildDefaultMetricSuffix(string $ctype, int $index): string
    {
        $normalizedCtype = $this->normalizeIdentifier($ctype);

        if (str_contains($normalizedCtype, 'statscounter')) {
            return ['', '', '%', '+'][$index % 4];
        }

        if (str_contains($normalizedCtype, 'counter')) {
            return ['', 'K', '%', '+'][$index % 4];
        }

        return '';
    }

    public function buildDefaultCodeBlockValue(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

final class ArticleTeaserRenderer
{
    public function render(array $article): string
    {
        $title = htmlspecialchars((string)($article['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $meta = htmlspecialchars((string)($article['meta'] ?? '5min read'), ENT_QUOTES, 'UTF-8');

        return sprintf('<article><p>%s</p><h2>%s</h2></article>', $meta, $title);
    }
}
PHP;
    }

    public function buildDefaultUrlTextValue(string $ctype, string $field, int $index): string
    {
        $normalizedCtype = $this->normalizeIdentifier($ctype);
        $normalizedField = $this->normalizeIdentifier($field);

        if (str_contains($normalizedField, 'video')) {
            return 'https://www.youtube-nocookie.com/embed/aqz-KE-bpKQ';
        }

        if (str_contains($normalizedCtype, 'mapembed') || str_contains($normalizedField, 'map')) {
            return $this->buildDefaultMapEmbedUrl();
        }

        return $this->buildDefaultLinkValue($field, $index);
    }

    public function buildDefaultMapEmbedUrl(): string
    {
        return 'https://www.openstreetmap.org/export/embed.html?bbox=16.3430%2C48.1940%2C16.3530%2C48.2040&layer=mapnik&marker=48.1990%2C16.3480';
    }

    public function normalizeResolvedFixtureFieldValue(string $ctype, string $field, mixed $value): mixed
    {
        if (
            is_string($value)
            && $this->isMapEmbedUrlField($ctype, $field)
            && $this->isShadcnDocumentationUrl($value)
        ) {
            return $this->buildDefaultMapEmbedUrl();
        }

        if (
            $this->normalizeIdentifier($ctype) === 'desideriotabs'
            && $this->normalizeIdentifier($field) === 'defaulttab'
        ) {
            return $this->normalizeTabsDefaultTab($value);
        }

        return $value;
    }

    public function normalizeTabsDefaultTab(mixed $value): int
    {
        if (is_int($value) && $value >= 0) {
            return $value;
        }

        if (is_string($value) && preg_match('/^\d+$/', trim($value)) === 1) {
            return (int)trim($value);
        }

        return 0;
    }

    public function isMapEmbedUrlField(string $ctype, string $field): bool
    {
        $normalizedCtype = $this->normalizeIdentifier($ctype);
        $normalizedField = $this->normalizeIdentifier($field);

        return str_contains($normalizedCtype, 'mapembed')
            && (str_contains($normalizedField, 'embed') || str_contains($normalizedField, 'url'));
    }

    public function isShadcnDocumentationUrl(string $value): bool
    {
        $host = parse_url($value, PHP_URL_HOST);

        return is_string($host) && strtolower($host) === 'ui.shadcn.com';
    }

    public function buildDefaultDemoCopy(string $elementLabel, string $fieldLabel, int $index): string
    {
        $copy = $this->demoCopy();
        $line = $copy[$index % count($copy)];

        return sprintf('%s Built for the %s pattern.', $line, strtolower($elementLabel));
    }

    public function buildDefaultTabPanelCopy(int $index): string
    {
        $copy = $this->demoTabPanelCopy();

        return $copy[$index % count($copy)];
    }

    public function buildDefaultQuote(string $elementLabel): string
    {
        return sprintf(
            'The %s element gives editors a polished pattern they can publish without rewriting the layout around the content.',
            strtolower($elementLabel)
        );
    }

    public function buildDefaultButtonText(string $normalizedField, string $elementLabel): string
    {
        if (str_contains($normalizedField, 'submit')) {
            return 'Send request';
        }
        if (str_contains($normalizedField, 'secondary')) {
            return 'View details';
        }

        return $this->pickDemoString($this->demoButtonLabels(), $elementLabel . '-' . $normalizedField, 0);
    }

    public function buildDefaultUnitLabel(string $ctype): string
    {
        return str_contains($this->normalizeIdentifier($ctype), 'pricingcalculator') ? 'users' : 'requests';
    }

    public function buildDefaultVolumeLabel(int $index): string
    {
        return ['1K', '10K', '100K', '1M'][$index % 4];
    }

    private function buildDefaultDateTimestamp(int $index): int
    {
        $timestamp = strtotime('2026-05-' . str_pad((string)min(28, $index + 1), 2, '0', STR_PAD_LEFT));
        return $timestamp !== false ? $timestamp : time();
    }

    private function buildDefaultDateTimeTimestamp(int $index): int
    {
        $timestamp = strtotime('2026-05-' . str_pad((string)min(28, $index + 1), 2, '0', STR_PAD_LEFT) . ' 09:00:00');
        return $timestamp !== false ? $timestamp : time();
    }

    public function buildDefaultLinkValue(string $field, int $index): string
    {
        $normalizedField = $this->normalizeIdentifier($field);
        if (preg_match('/^(?:link|child)(\d+)(?:link)?$/', $normalizedField, $matches) === 1) {
            return $this->buildDemoUrl($this->getDefaultLinkLabel((int)$matches[1]));
        }

        return 'https://example.com/desiderio/' . $this->buildColumnKey($field . '-' . ($index + 1));
    }

    public function getDefaultLinkLabel(int $slot): string
    {
        $labels = $this->demoLinkLabels();

        return $labels[max(0, min(count($labels) - 1, $slot - 1))];
    }

    public function buildDemoUrl(string $label): string
    {
        return match ($this->buildColumnKey($label)) {
            'overview' => 'https://ui.shadcn.com/docs',
            'components' => 'https://ui.shadcn.com/docs/components',
            'examples' => 'https://ui.shadcn.com/blocks',
            default => 'https://example.com/desiderio/' . $this->buildColumnKey($label !== '' ? $label : 'link'),
        };
    }

    public function buildDemoSubject(string $name, int $index): string
    {
        $label = $this->buildReadableLabel($name);
        $subject = $this->pickDemoString($this->demoSubjects(), $name, $index);

        if ($index > 0) {
            return $subject . ' ' . ($index + 1);
        }

        return $label . ': ' . $subject;
    }

    /**
     * @param list<string> $values
     */
    public function pickDemoString(array $values, string $seed, int $index): string
    {
        if ($values === []) {
            return '';
        }

        return $values[(abs(crc32($seed)) + $index) % count($values)];
    }

    /**
     * @param list<string> $values
     */
    public function buildDefaultList(array $values, string $seed, int $index): string
    {
        $items = [];
        for ($offset = 0; $offset < 3; $offset++) {
            $items[] = $this->pickDemoString($values, $seed, $index + $offset);
        }

        return implode("\n", array_values(array_unique($items)));
    }

    public function buildDefaultPeopleList(): string
    {
        return implode("\n", array_map(
            static fn (array $person): string => $person[0] . '|' . $person[1] . '|' . $person[2],
            array_slice($this->demoPeople(), 0, 3)
        ));
    }

    public function buildDefaultChartData(int $index): string
    {
        $quarters = [
            ['label' => 'Discover', 'value' => 42 + $index],
            ['label' => 'Evaluate', 'value' => 68 + $index],
            ['label' => 'Adopt', 'value' => 91 + $index],
            ['label' => 'Retain', 'value' => 117 + $index],
        ];
        $json = json_encode($quarters, JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '[{"label":"Discover","value":42},{"label":"Evaluate","value":68},{"label":"Adopt","value":91},{"label":"Retain","value":117}]';
    }

    public function buildReadableLabel(string $value): string
    {
        $value = preg_replace('/^desiderio[_-]?/', '', $value) ?? $value;
        $value = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value) ?? $value;
        $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value) ?? $value;
        $value = trim($value);

        return $value !== '' ? ucwords(strtolower($value)) : 'Demo';
    }
    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<mixed>
     */
    public function getSelectItemValues(array $fieldConfig): array
    {
        if ($this->usesIconItemsProcessor($fieldConfig)) {
            return IconRegistry::keys();
        }

        $values = [];

        $items = $fieldConfig['items'] ?? [];
        if (!is_array($items)) {
            return $values;
        }

        foreach ($items as $item) {
            if (!is_array($item) || !array_key_exists('value', $item)) {
                continue;
            }

            if (is_scalar($item['value'])) {
                $values[] = $item['value'];
            }
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function usesIconItemsProcessor(array $fieldConfig): bool
    {
        $processors = $fieldConfig['itemsProcessors'] ?? [];
        if (!is_array($processors)) {
            return false;
        }

        foreach ($processors as $processor) {
            if (!is_array($processor)) {
                continue;
            }
            if (($processor['class'] ?? null) === IconItemsProcessor::class) {
                return true;
            }
        }

        return false;
    }
    public function buildColumnKey(string $label): string
    {
        $key = strtolower(trim($label));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? '';
        $key = trim($key, '_');

        return $key !== '' ? $key : 'column';
    }

    public function normalizeIdentifier(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '', $value) ?? '');
    }

    public function buildReadableFileTitle(string $value): string
    {
        $title = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value) ?? '';
        $title = trim($title);

        return $title !== '' ? ucwords(strtolower($title)) : 'Styleguide image';
    }

    /**
     * @param list<string> $words
     */
    public function fieldIdentifierContainsAnyWord(string $field, array $words): bool
    {
        $separated = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $field) ?? $field;
        $parts = preg_split('/[^a-zA-Z0-9]+/', strtolower($separated), -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($parts)) {
            return false;
        }

        foreach ($words as $word) {
            if (in_array(strtolower($word), $parts, true)) {
                return true;
            }
        }

        return false;
    }
}
