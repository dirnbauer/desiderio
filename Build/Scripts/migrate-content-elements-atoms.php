#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$root = dirname(__DIR__, 2);
$contentBlocksDir = $root . '/ContentBlocks/ContentElements';

$dryRun = in_array('--dry-run', $argv, true);
$write = in_array('--write', $argv, true);

if (!$dryRun && !$write) {
    fwrite(STDERR, "Usage: migrate-content-elements-atoms.php [--dry-run|--write]\n");
    exit(1);
}

if ($dryRun && $write) {
    fwrite(STDERR, "Use either --dry-run or --write, not both.\n");
    exit(1);
}

$migrator = new ContentElementAtomMigrator($contentBlocksDir, $dryRun);
$summary = $migrator->run();

echo sprintf(
    "Mode: %s\nTemplates changed: %d\nCSS files changed: %d\nRemaining f:link.typolink: %d\n",
    $dryRun ? 'dry-run' : 'write',
    $summary['templatesChanged'],
    $summary['cssChanged'],
    $summary['remainingTypolinks'],
);

if ($summary['changedSlugs'] !== []) {
    echo "Changed slugs:\n";
    foreach ($summary['changedSlugs'] as $slug) {
        echo "  - {$slug}\n";
    }
}

exit(0);

final class ContentElementAtomMigrator
{
    private const PRICING_TIER_SLUGS = [
        'pricing-three-tier',
        'pricing-four-tier',
        'pricing-two-tier',
        'pricing',
        'pricing-simple',
        'pricing-enterprise',
    ];

    private const D_NAMESPACE = 'xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"';

    /** @var list<string> */
    private array $changedSlugs = [];

    public function __construct(
        private readonly string $contentBlocksDir,
        private readonly bool $dryRun,
    ) {
    }

    /**
     * @return array{templatesChanged: int, cssChanged: int, remainingTypolinks: int, changedSlugs: list<string>}
     */
    public function run(): array
    {
        $templatesChanged = 0;
        $cssChanged = 0;

        $blocks = glob($this->contentBlocksDir . '/*', GLOB_ONLYDIR);
        if ($blocks === false) {
            throw new RuntimeException('Unable to read content blocks directory.');
        }

        sort($blocks);

        foreach ($blocks as $blockDir) {
            $slug = basename($blockDir);
            $templatePath = "{$blockDir}/templates/frontend.html";
            if (!is_file($templatePath)) {
                continue;
            }

            $original = (string) file_get_contents($templatePath);
            $migrated = $this->migrateTemplate($slug, $original, $blockDir);

            if ($migrated !== $original) {
                if (trim($migrated) === '' || !str_contains($migrated, '<html')) {
                    fwrite(STDERR, "Skipped {$slug}: migration produced invalid template.\n");
                    continue;
                }

                ++$templatesChanged;
                $this->changedSlugs[] = $slug;
                if (!$this->dryRun) {
                    file_put_contents($templatePath, $migrated);
                }
            }

            $cssPath = "{$blockDir}/assets/frontend.css";
            if (!is_file($cssPath)) {
                continue;
            }

            $originalCss = (string) file_get_contents($cssPath);
            $migratedCss = $this->migrateCss($slug, $originalCss);

            if ($migratedCss !== $originalCss) {
                ++$cssChanged;
                if (!in_array($slug, $this->changedSlugs, true)) {
                    $this->changedSlugs[] = $slug;
                }
                if (!$this->dryRun) {
                    file_put_contents($cssPath, $migratedCss);
                }
            }
        }

        return [
            'templatesChanged' => $templatesChanged,
            'cssChanged' => $cssChanged,
            'remainingTypolinks' => $this->countRemainingTypolinks(),
            'changedSlugs' => $this->changedSlugs,
        ];
    }

    private function migrateTemplate(string $slug, string $template, string $blockDir): string
    {
        if (in_array($slug, self::PRICING_TIER_SLUGS, true) && !$this->isPricingTierMigrated($template)) {
            return $this->generatePricingTierTemplate($slug);
        }

        $template = $this->unwrapTypolinkAroundAtomButton($template);
        $template = $this->convertButtonTypolinks($template);
        $template = $this->convertCtaTypolinks($template);
        $template = $this->convertNavTextTypolinks($template);
        $template = $this->convertBareTypolinks($template);
        $template = $this->convertRemainingTypolinks($template);
        $template = $this->replaceInlineCheckmarkSvgs($template);
        $template = $this->ensureDesiderioNamespace($template);
        $template = $this->cleanupTemplate($template);

        return $template;
    }

    private function isPricingTierMigrated(string $template): bool
    {
        return str_contains($template, '<d:molecule.card')
            && !str_contains($template, 'f:link.typolink')
            && !preg_match('/<svg[^>]*>.*?M20 6 9 17l-5-5/s', $template)
            && !preg_match('/each="\{data\.plans\}"\s+as="plan"/', $template);
    }

    private function generatePricingTierTemplate(string $slug): string
    {
        return match ($slug) {
            'pricing-three-tier' => $this->pricingThreeTierTemplate(),
            'pricing-four-tier' => $this->pricingFourTierTemplate(),
            'pricing-two-tier' => $this->pricingTwoTierTemplate(),
            'pricing' => $this->pricingTemplate(),
            'pricing-simple' => $this->pricingSimpleTemplate(),
            'pricing-enterprise' => $this->pricingEnterpriseTemplate(),
            default => throw new RuntimeException("Unknown pricing slug: {$slug}"),
        };
    }

    private function pricingThreeTierTemplate(): string
    {
        return $this->wrapTemplate('pricing-three-tier', <<<'HTML'
<d:layout.section class="pricing-three-tier">
    <d:layout.container>
        <f:if condition="{data.eyebrow} || {data.header} || {data.subheadline}">
            <header class="pricing-three-tier__intro">
                <f:if condition="{data.eyebrow}">
                    <d:atom.typography variant="small" class="pricing-three-tier__eyebrow">{data -> f:render.text(field: 'eyebrow')}</d:atom.typography>
                </f:if>
                <f:if condition="{data.header}">
                    <d:atom.typography tag="h2" variant="h2" class="pricing-three-tier__headline">{data -> f:render.text(field: 'header')}</d:atom.typography>
                </f:if>
                <f:if condition="{data.subheadline}">
                    <d:atom.typography variant="muted" class="pricing-three-tier__description">{data -> f:render.text(field: 'subheadline')}</d:atom.typography>
                </f:if>
            </header>
        </f:if>

        <ul class="pricing-three-tier__grid" role="list">
            <f:for each="{data.plans}" as="tier" iteration="iter">
                <li class="pricing-three-tier__item">
                    <d:molecule.card
                        class="pricing-three-tier__plan h-full {f:if(condition: tier.is_recommended, then: 'pricing-three-tier__plan--recommended ring-2 ring-primary')}"
                    >
                        <f:if condition="{tier.is_recommended}">
                            <div class="pricing-three-tier__ribbon">
                                <d:atom.badge>
                                    <f:translate key="LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:plan.recommended"/>
                                </d:atom.badge>
                            </div>
                        </f:if>

                        <d:molecule.cardHeader class="pricing-three-tier__header">
                            <d:atom.typography tag="h3" variant="h4" class="pricing-three-tier__name">{tier -> f:render.text(field: 'name')}</d:atom.typography>
                            <p class="pricing-three-tier__price-row">
                                <span class="pricing-three-tier__price">{tier -> f:render.text(field: 'price')}</span>
                                <f:if condition="{tier.billing_period}">
                                    <d:atom.typography tag="span" variant="muted" class="pricing-three-tier__period">{tier -> f:render.text(field: 'billing_period')}</d:atom.typography>
                                </f:if>
                            </p>
                            <f:if condition="{tier.description}">
                                <d:atom.typography variant="muted" class="pricing-three-tier__plan-desc">{tier -> f:render.text(field: 'description')}</d:atom.typography>
                            </f:if>
                        </d:molecule.cardHeader>

                        <f:if condition="{tier.features}">
                            <d:molecule.cardContent class="pricing-three-tier__content">
                                <ul class="pricing-three-tier__features" role="list">
                                    <f:for each="{tier.features}" as="feature">
                                        <f:if condition="{feature.text}">
                                            <li class="pricing-three-tier__feature">
                                                <d:atom.icon name="check" size="sm" class="pricing-three-tier__check" aria-hidden="true"/>
                                                <d:atom.typography tag="span" variant="small">{feature -> f:render.text(field: 'text')}</d:atom.typography>
                                            </li>
                                        </f:if>
                                    </f:for>
                                </ul>
                            </d:molecule.cardContent>
                        </f:if>

                        <f:if condition="{tier.button_text} && {tier.button_link.url}">
                            <d:molecule.cardFooter class="pricing-three-tier__footer">
                                <d:atom.button
                                    href="{tier.button_link.url}"
                                    target="{tier.button_link.target}"
                                    variant="{f:if(condition: tier.is_recommended, then: 'default', else: 'outline')}"
                                    class="pricing-three-tier__button w-full"
                                >
                                    <span>{tier -> f:render.text(field: 'button_text')}</span>
                                    <span class="d-sr-only"> – {tier.name}</span>
                                </d:atom.button>
                            </d:molecule.cardFooter>
                        </f:if>
                    </d:molecule.card>
                </li>
            </f:for>
        </ul>
    </d:layout.container>
</d:layout.section>
HTML);
    }

    private function pricingFourTierTemplate(): string
    {
        return $this->wrapTemplate('pricing-four-tier', <<<'HTML'
<d:layout.section class="pricing-four-tier">
    <d:layout.container>
        <f:if condition="{data.eyebrow} || {data.header} || {data.subheadline}">
            <header class="pricing-four-tier__intro">
                <f:if condition="{data.eyebrow}">
                    <d:atom.typography variant="small" class="pricing-four-tier__eyebrow">{data -> f:render.text(field: 'eyebrow')}</d:atom.typography>
                </f:if>
                <f:if condition="{data.header}">
                    <d:atom.typography tag="h2" variant="h2" class="pricing-four-tier__headline">{data -> f:render.text(field: 'header')}</d:atom.typography>
                </f:if>
                <f:if condition="{data.subheadline}">
                    <d:atom.typography variant="muted" class="pricing-four-tier__description">{data -> f:render.text(field: 'subheadline')}</d:atom.typography>
                </f:if>
            </header>
        </f:if>

        <ul class="pricing-four-tier__grid" role="list">
            <f:for each="{data.plans}" as="tier" iteration="iter">
                <li class="pricing-four-tier__item">
                    <d:molecule.card
                        class="pricing-four-tier__plan h-full {f:if(condition: tier.featured, then: 'pricing-four-tier__plan--featured ring-2 ring-primary')}"
                    >
                        <f:if condition="{tier.featured}">
                            <div class="pricing-four-tier__ribbon">
                                <d:atom.badge>
                                    <f:translate key="LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:plan.recommended"/>
                                </d:atom.badge>
                            </div>
                        </f:if>

                        <d:molecule.cardHeader class="pricing-four-tier__header">
                            <d:atom.typography tag="h3" variant="h4" class="pricing-four-tier__title">{tier -> f:render.text(field: 'title')}</d:atom.typography>
                            <p class="pricing-four-tier__price">{tier -> f:render.text(field: 'price')}</p>
                            <f:if condition="{tier.description}">
                                <d:atom.typography variant="muted" class="pricing-four-tier__plan-desc">{tier -> f:render.text(field: 'description')}</d:atom.typography>
                            </f:if>
                        </d:molecule.cardHeader>

                        <f:if condition="{tier.features}">
                            <d:molecule.cardContent class="pricing-four-tier__content">
                                <ul class="pricing-four-tier__features" role="list">
                                    <f:for each="{tier.features}" as="feature">
                                        <f:if condition="{feature.text}">
                                            <li class="pricing-four-tier__feature">
                                                <d:atom.icon name="check" size="sm" class="pricing-four-tier__check" aria-hidden="true"/>
                                                <d:atom.typography tag="span" variant="small">{feature -> f:render.text(field: 'text')}</d:atom.typography>
                                            </li>
                                        </f:if>
                                    </f:for>
                                </ul>
                            </d:molecule.cardContent>
                        </f:if>

                        <f:if condition="{tier.button_text} && {tier.button_link.url}">
                            <d:molecule.cardFooter class="pricing-four-tier__footer">
                                <d:atom.button
                                    href="{tier.button_link.url}"
                                    target="{tier.button_link.target}"
                                    variant="{f:if(condition: tier.featured, then: 'default', else: 'outline')}"
                                    class="pricing-four-tier__button w-full"
                                >
                                    <span>{tier -> f:render.text(field: 'button_text')}</span>
                                    <span class="d-sr-only"> – {tier.title}</span>
                                </d:atom.button>
                            </d:molecule.cardFooter>
                        </f:if>
                    </d:molecule.card>
                </li>
            </f:for>
        </ul>
    </d:layout.container>
</d:layout.section>
HTML);
    }

    private function pricingTwoTierTemplate(): string
    {
        return $this->wrapTemplate('pricing-two-tier', <<<'HTML'
<d:layout.section class="pricing-two-tier">
    <d:layout.container>
        <f:if condition="{data.eyebrow} || {data.header} || {data.subheadline}">
            <header class="pricing-two-tier__intro">
                <f:if condition="{data.eyebrow}">
                    <d:atom.typography variant="small" class="pricing-two-tier__eyebrow">{data -> f:render.text(field: 'eyebrow')}</d:atom.typography>
                </f:if>
                <f:if condition="{data.header}">
                    <d:atom.typography tag="h2" variant="h2" class="pricing-two-tier__headline">{data -> f:render.text(field: 'header')}</d:atom.typography>
                </f:if>
                <f:if condition="{data.subheadline}">
                    <d:atom.typography variant="muted" class="pricing-two-tier__description">{data -> f:render.text(field: 'subheadline')}</d:atom.typography>
                </f:if>
            </header>
        </f:if>

        <ul class="pricing-two-tier__grid" role="list">
            <f:for each="{data.plans}" as="tier" iteration="iter">
                <li class="pricing-two-tier__item">
                    <d:molecule.card
                        class="pricing-two-tier__plan h-full {f:if(condition: tier.featured, then: 'pricing-two-tier__plan--featured ring-2 ring-primary')}"
                    >
                        <f:if condition="{tier.featured}">
                            <div class="pricing-two-tier__ribbon">
                                <d:atom.badge>
                                    <f:translate key="LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:plan.recommended"/>
                                </d:atom.badge>
                            </div>
                        </f:if>

                        <d:molecule.cardHeader class="pricing-two-tier__header">
                            <d:atom.typography
                                tag="h3"
                                variant="h4"
                                id="pricing-two-tier-{data.uid}-{iter.cycle}"
                                class="pricing-two-tier__title"
                            >
                                {tier -> f:render.text(field: 'title')}
                            </d:atom.typography>
                            <p class="pricing-two-tier__price">{tier -> f:render.text(field: 'price')}</p>
                            <f:if condition="{tier.description}">
                                <d:atom.typography variant="muted" class="pricing-two-tier__plan-desc">{tier -> f:render.text(field: 'description')}</d:atom.typography>
                            </f:if>
                        </d:molecule.cardHeader>

                        <f:if condition="{tier.features}">
                            <d:molecule.cardContent class="pricing-two-tier__content">
                                <ul class="pricing-two-tier__features" role="list">
                                    <f:for each="{tier.features}" as="feature">
                                        <f:if condition="{feature.text}">
                                            <li class="pricing-two-tier__feature">
                                                <d:atom.icon name="check" size="sm" class="pricing-two-tier__check" aria-hidden="true"/>
                                                <d:atom.typography tag="span" variant="small">{feature -> f:render.text(field: 'text')}</d:atom.typography>
                                            </li>
                                        </f:if>
                                    </f:for>
                                </ul>
                            </d:molecule.cardContent>
                        </f:if>

                        <f:if condition="{tier.button_text} && {tier.button_link.url}">
                            <d:molecule.cardFooter class="pricing-two-tier__footer">
                                <d:atom.button
                                    href="{tier.button_link.url}"
                                    target="{tier.button_link.target}"
                                    variant="{f:if(condition: tier.featured, then: 'default', else: 'outline')}"
                                    class="pricing-two-tier__button w-full"
                                >
                                    <span>{tier -> f:render.text(field: 'button_text')}</span>
                                    <span class="d-sr-only"> – {tier.title}</span>
                                </d:atom.button>
                            </d:molecule.cardFooter>
                        </f:if>
                    </d:molecule.card>
                </li>
            </f:for>
        </ul>
    </d:layout.container>
</d:layout.section>
HTML);
    }

    private function pricingTemplate(): string
    {
        return $this->wrapTemplate('pricing', <<<'HTML'
<d:layout.section class="pricing">
    <d:layout.container>
        <f:if condition="{data.eyebrow} || {data.header} || {data.subheadline}">
            <header class="pricing__intro">
                <f:if condition="{data.eyebrow}">
                    <d:atom.typography variant="small" class="pricing__eyebrow">{data -> f:render.text(field: 'eyebrow')}</d:atom.typography>
                </f:if>
                <f:if condition="{data.header}">
                    <d:atom.typography tag="h2" variant="h2" class="pricing__headline">{data -> f:render.text(field: 'header')}</d:atom.typography>
                </f:if>
                <f:if condition="{data.subheadline}">
                    <d:atom.typography variant="muted" class="pricing__description">{data -> f:render.text(field: 'subheadline')}</d:atom.typography>
                </f:if>
            </header>
        </f:if>

        <ul class="pricing__grid pricing__grid--cols-{data.columns -> f:or(alternative: '3')}" role="list">
            <f:for each="{data.plans}" as="tier" iteration="iter">
                <li class="pricing__item">
                    <d:molecule.card
                        class="pricing__plan h-full {f:if(condition: tier.is_featured, then: 'pricing__plan--featured ring-2 ring-primary')}"
                    >
                        <f:if condition="{tier.is_featured}">
                            <div class="pricing__ribbon">
                                <d:atom.badge>
                                    <f:if condition="{tier.badge_text}">
                                        <f:then>{tier -> f:render.text(field: 'badge_text')}</f:then>
                                        <f:else><f:translate key="LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:plan.recommended"/></f:else>
                                    </f:if>
                                </d:atom.badge>
                            </div>
                        </f:if>

                        <d:molecule.cardHeader class="pricing__header">
                            <d:atom.typography
                                tag="h3"
                                variant="h4"
                                id="pricing-{data.uid}-name-{iter.cycle}"
                                class="pricing__plan-name"
                            >
                                {tier -> f:render.text(field: 'name')}
                            </d:atom.typography>
                            <p class="pricing__price-row">
                                <span class="pricing__plan-price">{tier -> f:render.text(field: 'price')}</span>
                                <f:if condition="{tier.billing_period}">
                                    <d:atom.typography tag="span" variant="muted" class="pricing__billing-period">{tier -> f:render.text(field: 'billing_period')}</d:atom.typography>
                                </f:if>
                            </p>
                            <f:if condition="{tier.description}">
                                <d:atom.typography variant="muted" class="pricing__plan-description">{tier -> f:render.text(field: 'description')}</d:atom.typography>
                            </f:if>
                        </d:molecule.cardHeader>

                        <f:if condition="{tier.features}">
                            <d:molecule.cardContent class="pricing__content">
                                <ul class="pricing__features" role="list">
                                    <f:for each="{tier.features}" as="feature">
                                        <f:if condition="{feature.text}">
                                            <li class="pricing__feature">
                                                <d:atom.icon name="check" size="sm" class="pricing__check" aria-hidden="true"/>
                                                <d:atom.typography tag="span" variant="small">{feature -> f:render.text(field: 'text')}</d:atom.typography>
                                            </li>
                                        </f:if>
                                    </f:for>
                                </ul>
                            </d:molecule.cardContent>
                        </f:if>

                        <f:if condition="{tier.button_text} && {tier.button_link.url}">
                            <d:molecule.cardFooter class="pricing__footer">
                                <d:atom.button
                                    href="{tier.button_link.url}"
                                    target="{tier.button_link.target}"
                                    variant="{f:if(condition: tier.is_featured, then: 'default', else: 'outline')}"
                                    class="pricing__button w-full"
                                >
                                    <span>{tier -> f:render.text(field: 'button_text')}</span>
                                    <span class="d-sr-only"> – {tier.name}</span>
                                </d:atom.button>
                            </d:molecule.cardFooter>
                        </f:if>
                    </d:molecule.card>
                </li>
            </f:for>
        </ul>
    </d:layout.container>
</d:layout.section>
HTML);
    }

    private function pricingSimpleTemplate(): string
    {
        return $this->wrapTemplate('pricing-simple', <<<'HTML'
<d:layout.section class="pricing-simple">
    <d:layout.container size="sm">
        <d:molecule.card class="pricing-simple__card">
            <d:molecule.cardHeader class="pricing-simple__header">
                <f:if condition="{data.header}">
                    <d:atom.typography tag="h2" variant="h2" class="pricing-simple__headline">{data -> f:render.text(field: 'header')}</d:atom.typography>
                </f:if>
                <p class="pricing-simple__price-row">
                    <span class="pricing-simple__price">{data -> f:render.text(field: 'price')}</span>
                    <f:if condition="{data.billing_period}">
                        <d:atom.typography tag="span" variant="muted" class="pricing-simple__period">{data -> f:render.text(field: 'billing_period')}</d:atom.typography>
                    </f:if>
                </p>
                <f:if condition="{data.description}">
                    <d:atom.typography variant="muted" class="pricing-simple__description">{data -> f:render.text(field: 'description')}</d:atom.typography>
                </f:if>
            </d:molecule.cardHeader>

            <f:if condition="{data.features}">
                <d:molecule.cardContent class="pricing-simple__content">
                    <ul class="pricing-simple__features" role="list">
                        <f:for each="{data.features}" as="feature">
                            <f:if condition="{feature.text}">
                                <li class="pricing-simple__feature">
                                    <d:atom.icon name="check" size="sm" class="pricing-simple__check" aria-hidden="true"/>
                                    <d:atom.typography tag="span" variant="small">{feature -> f:render.text(field: 'text')}</d:atom.typography>
                                </li>
                            </f:if>
                        </f:for>
                    </ul>
                </d:molecule.cardContent>
            </f:if>

            <f:if condition="{data.button_text} && {data.button_link.url}">
                <d:molecule.cardFooter class="pricing-simple__footer">
                    <d:atom.button
                        href="{data.button_link.url}"
                        target="{data.button_link.target}"
                        class="pricing-simple__button w-full"
                    >
                        {data -> f:render.text(field: 'button_text')}
                    </d:atom.button>
                </d:molecule.cardFooter>
            </f:if>
        </d:molecule.card>
    </d:layout.container>
</d:layout.section>
HTML);
    }

    private function pricingEnterpriseTemplate(): string
    {
        return $this->wrapTemplate('pricing-enterprise', <<<'HTML'
<d:layout.section class="pricing-enterprise">
    <d:layout.container>
        <d:molecule.card class="pricing-enterprise__card">
            <div class="pricing-enterprise__layout">
                <div class="pricing-enterprise__content">
                    <d:molecule.cardHeader class="pricing-enterprise__header">
                        <f:if condition="{data.eyebrow}">
                            <d:atom.typography variant="small" class="pricing-enterprise__eyebrow">{data -> f:render.text(field: 'eyebrow')}</d:atom.typography>
                        </f:if>
                        <f:if condition="{data.header}">
                            <d:atom.typography tag="h2" variant="h2" class="pricing-enterprise__headline">{data -> f:render.text(field: 'header')}</d:atom.typography>
                        </f:if>
                        <f:if condition="{data.description}">
                            <d:atom.typography variant="muted" class="pricing-enterprise__description">{data -> f:render.text(field: 'description')}</d:atom.typography>
                        </f:if>
                    </d:molecule.cardHeader>

                    <f:if condition="{data.features}">
                        <d:molecule.cardContent class="pricing-enterprise__content-body">
                            <ul class="pricing-enterprise__features" role="list">
                                <f:for each="{data.features}" as="feature">
                                    <f:if condition="{feature.text}">
                                        <li class="pricing-enterprise__feature">
                                            <d:atom.icon name="check" size="sm" class="pricing-enterprise__check" aria-hidden="true"/>
                                            <d:atom.typography tag="span" variant="small">{feature -> f:render.text(field: 'text')}</d:atom.typography>
                                        </li>
                                    </f:if>
                                </f:for>
                            </ul>
                        </d:molecule.cardContent>
                    </f:if>

                    <f:if condition="{data.contact_text} && {data.contact_link.url}">
                        <d:molecule.cardFooter class="pricing-enterprise__footer">
                            <d:atom.button
                                href="{data.contact_link.url}"
                                target="{data.contact_link.target}"
                                class="pricing-enterprise__button"
                            >
                                {data -> f:render.text(field: 'contact_text')}
                            </d:atom.button>
                        </d:molecule.cardFooter>
                    </f:if>
                </div>

                <f:if condition="{data.image}">
                    <div class="pricing-enterprise__media">
                        <f:for each="{data.image}" as="fileReference">
                            <f:image image="{fileReference}" maxWidth="480" alt="" class="pricing-enterprise__image"/>
                        </f:for>
                    </div>
                </f:if>
            </div>
        </d:molecule.card>
    </d:layout.container>
</d:layout.section>
HTML);
    }

    private function wrapTemplate(string $slug, string $body): string
    {
        $identifier = 'd-' . $slug;

        return <<<HTML
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
      xmlns:cb="http://typo3.org/ns/TYPO3/CMS/ContentBlocks/ViewHelpers"
      xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"
      data-namespace-typo3-fluid="true">

<f:asset.css identifier="{$identifier}" href="{cb:assetPath()}/frontend.css"/>

{$body}

</html>
HTML;
    }

    private function unwrapTypolinkAroundAtomButton(string $template): string
    {
        return FluidTagWalker::replaceTags($template, 'f:link.typolink', function (string $attrs, string $inner): ?string {
            $trimmedInner = trim($inner);
            $buttonTag = FluidTagWalker::readComponentTag($trimmedInner, 'd:atom.button');
            if ($buttonTag === null) {
                return null;
            }

            $parameter = FluidTagWalker::extractAttribute($attrs, 'parameter');
            if ($parameter === null) {
                return null;
            }

            return $this->buildAtomButton(
                $this->linkFieldExpression($parameter),
                $parameter,
                $buttonTag['attrs'],
                $buttonTag['inner'],
            );
        });
    }

    private function convertButtonTypolinks(string $template): string
    {
        return $this->convertTypolinksByPredicate(
            $template,
            static fn(string $attrs): bool => self::classContains($attrs, '__button'),
            'button',
        );
    }

    private function convertCtaTypolinks(string $template): string
    {
        return $this->convertTypolinksByPredicate(
            $template,
            static fn(string $attrs): bool => self::classContains($attrs, '__cta') && !self::classContains($attrs, '__cta-link'),
            'button',
        );
    }

    private function convertNavTextTypolinks(string $template): string
    {
        return $this->convertTypolinksByPredicate(
            $template,
            static function (string $attrs): bool {
                if (!str_contains($attrs, 'class=')) {
                    return false;
                }

                $class = FluidTagWalker::extractAttribute($attrs, 'class') ?? '';

                return str_contains($class, '__link')
                    || str_contains($class, '__brand-link')
                    || str_contains($class, '__brand--link')
                    || str_contains($class, '__nav')
                    || str_contains($class, 'breadcrumb__')
                    || str_contains($class, 'social-links__')
                    || str_contains($class, '__sublink')
                    || str_contains($class, '__tab')
                    || str_contains($class, '__action')
                    || str_contains($class, '__page')
                    || str_contains($class, '__edge')
                    || str_contains($class, '__category')
                    || str_contains($class, '__icon')
                    || str_contains($class, '__number')
                    || preg_match('/(?:^|[\s"\'-])[\w-]*-link(?:[\s"\'-]|$)/', $class) === 1;
            },
            'link',
        );
    }

    private function convertRemainingTypolinks(string $template): string
    {
        return $this->convertTypolinksByPredicate(
            $template,
            static fn(string $attrs): bool => true,
            'link',
        );
    }

    private function convertBareTypolinks(string $template): string
    {
        return $this->convertTypolinksByPredicate(
            $template,
            static fn(string $attrs): bool => !str_contains($attrs, 'class='),
            'link',
        );
    }

    private function convertTypolinksByPredicate(string $template, callable $predicate, string $atom): string
    {
        return FluidTagWalker::replaceTags($template, 'f:link.typolink', function (string $attrs, string $inner) use ($predicate, $atom): ?string {
            if (!$predicate($attrs)) {
                return null;
            }

            $parameter = FluidTagWalker::extractAttribute($attrs, 'parameter');
            if ($parameter === null) {
                return null;
            }

            $href = $this->linkFieldExpression($parameter);
            $class = FluidTagWalker::extractAttribute($attrs, 'class') ?? '';
            $size = FluidTagWalker::extractAttribute($attrs, 'size');
            $targetAttr = FluidTagWalker::extractAttribute($attrs, 'target');

            if ($atom === 'button') {
                [$variant, $cleanClass] = $this->resolveButtonVariantAndClass($class);
                $extraAttrs = [];
                if ($variant !== null) {
                    $extraAttrs[] = 'variant="' . $variant . '"';
                }
                if ($size !== null) {
                    $extraAttrs[] = 'size="' . $size . '"';
                }
                if ($targetAttr !== null && $targetAttr !== '') {
                    $extraAttrs[] = 'target="' . $targetAttr . '"';
                } elseif (!str_contains($href, '.target')) {
                    $extraAttrs[] = 'target="{' . $this->stripBraces($parameter) . '.target}"';
                }

                return $this->buildAtomButton(
                    $href,
                    $parameter,
                    implode(' ', $extraAttrs) . ($cleanClass !== '' ? ' class="' . $cleanClass . '"' : ''),
                    $inner,
                );
            }

            $variant = self::classContains($attrs, '__brand-link') ? 'default' : 'muted';
            $linkAttrs = ['href="' . $href . '"', 'target="' . $this->linkTargetExpression($parameter) . '"', 'variant="' . $variant . '"'];
            if ($class !== '') {
                $linkAttrs[] = 'class="' . $class . '"';
            }
            if ($targetAttr !== null && $targetAttr !== '') {
                $linkAttrs = array_values(array_filter(
                    $linkAttrs,
                    static fn(string $attr): bool => !str_starts_with($attr, 'target='),
                ));
                $linkAttrs[] = 'target="' . $targetAttr . '"';
            }

            return '<d:atom.link ' . implode(' ', $linkAttrs) . '>' . $inner . '</d:atom.link>';
        });
    }

    private function buildAtomButton(string $href, string $parameter, string $buttonAttrs, string $inner): string
    {
        $buttonAttrs = trim($buttonAttrs);
        $target = $this->linkTargetExpression($parameter);

        if ($buttonAttrs !== '' && !str_contains($buttonAttrs, 'href=')) {
            $buttonAttrs = preg_match('/\btarget="/', $buttonAttrs)
                ? 'href="' . $href . '" ' . $buttonAttrs
                : 'href="' . $href . '" target="' . $target . '" ' . $buttonAttrs;
        } elseif ($buttonAttrs === '') {
            $buttonAttrs = 'href="' . $href . '" target="' . $target . '"';
        } else {
            $buttonAttrs = (string) preg_replace('/href="[^"]*"/', 'href="' . $href . '"', $buttonAttrs);
            if (!preg_match('/\btarget="/', $buttonAttrs)) {
                $buttonAttrs .= ' target="' . $target . '"';
            }
        }

        return '<d:atom.button ' . trim((string) $buttonAttrs) . '>' . $inner . '</d:atom.button>';
    }

    private function cleanupTemplate(string $template): string
    {
        $template = (string) preg_replace_callback(
            '/<d:atom\\.button\\b([^>]*?)>/',
            static function (array $matches): string {
                $attrs = trim($matches[1]);
                if (!preg_match_all('/\btarget="([^"]*)"/', $attrs, $targetMatches) || count($targetMatches[0]) <= 1) {
                    return $matches[0];
                }

                $firstTarget = $targetMatches[1][0];
                $attrs = (string) preg_replace('/\btarget="[^"]*"/', '', $attrs);
                $attrs = trim((string) preg_replace('/\s+/', ' ', $attrs));

                return $attrs === ''
                    ? '<d:atom.button target="' . $firstTarget . '">'
                    : '<d:atom.button ' . $attrs . ' target="' . $firstTarget . '">';
            },
            $template,
        );

        return (string) preg_replace("/\\n{3,}/", "\n\n", $template);
    }

    private function linkTargetExpression(string $parameter): string
    {
        return '{' . $this->stripBraces($parameter) . '.target}';
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private function resolveButtonVariantAndClass(string $class): array
    {
        $variant = null;
        $cleanClass = $class;

        if (preg_match('/\{f:if\(condition:\s*([^,]+),\s*then:\s*\'([^\']*)\',\s*else:\s*\'([^\']*)\'\)\}/', $class, $match)) {
            $condition = trim($match[1]);
            $thenVariant = $this->modifierToVariant($match[2]);
            $elseVariant = $this->modifierToVariant($match[3]);
            $variant = '{f:if(condition: ' . $condition . ', then: \'' . $thenVariant . '\', else: \'' . $elseVariant . '\')}';
            $cleanClass = (string) preg_replace('/\s*\{f:if\(condition:[^}]+\}\s*/', '', $class);
        } elseif (preg_match('/__button--(primary|outline|secondary|ghost)/', $class, $match)) {
            $variant = $this->modifierToVariant('__button--' . $match[1]);
            $cleanClass = (string) preg_replace('/\s*__button--(?:primary|outline|secondary|ghost)/', '', $class);
        }

        $cleanClass = trim((string) preg_replace('/\s+/', ' ', $cleanClass));

        return [$variant, $cleanClass];
    }

    private function modifierToVariant(string $modifier): string
    {
        return match (true) {
            str_contains($modifier, '--outline') => 'outline',
            str_contains($modifier, '--secondary') => 'secondary',
            str_contains($modifier, '--ghost') => 'ghost',
            default => 'default',
        };
    }

    private function linkFieldExpression(string $parameter): string
    {
        $field = $this->stripBraces($parameter);

        return '{' . $field . '.url}';
    }

    private function stripBraces(string $value): string
    {
        return trim($value, '{}');
    }

    private static function classContains(string $attrs, string $needle): bool
    {
        $class = FluidTagWalker::extractAttribute($attrs, 'class');
        if ($class === null) {
            return false;
        }

        return str_contains($class, $needle);
    }

    private function replaceInlineCheckmarkSvgs(string $template): string
    {
        $pattern = '~<svg\b[^>]*>.*?<path\s+d="M20 6 9 17l-5-5"[^>]*/>.*?</svg>~s';
        $result = preg_replace_callback(
            $pattern,
            function (array $matches): string {
                $svg = $matches[0];
                $class = '';
                if (preg_match('~\bclass="([^"]*)"~', $svg, $classMatch)) {
                    $class = ' class="' . $classMatch[1] . '"';
                }

                return '<d:atom.icon name="check" size="sm"' . $class . ' aria-hidden="true"/>';
            },
            $template,
        );

        return is_string($result) ? $result : $template;
    }

    private function ensureDesiderioNamespace(string $template): string
    {
        if (!str_contains($template, '<d:')) {
            return $template;
        }

        if (str_contains($template, self::D_NAMESPACE)) {
            return $template;
        }

        return (string) preg_replace(
            '/<html\b/',
            '<html ' . self::D_NAMESPACE,
            $template,
            1,
        );
    }

    private function migrateCss(string $slug, string $css): string
    {
        if (in_array($slug, self::PRICING_TIER_SLUGS, true)) {
            return $this->migratePricingTierCss($slug);
        }

        return $this->stripButtonVisualRules($css);
    }

    private function migratePricingTierCss(string $slug): string
    {
        $prefix = str_replace('-', '-', $slug);
        $prefix = $slug;

        return match ($slug) {
            'pricing' => $this->pricingGridCss('pricing'),
            'pricing-three-tier' => $this->pricingGridCss('pricing-three-tier', 3),
            'pricing-four-tier' => $this->pricingGridCss('pricing-four-tier', 4),
            'pricing-two-tier' => $this->pricingGridCss('pricing-two-tier', 2),
            'pricing-simple' => $this->pricingSimpleCss(),
            'pricing-enterprise' => $this->pricingEnterpriseCss(),
            default => $this->stripButtonVisualRules($prefix),
        };
    }

    private function pricingGridCss(string $prefix, ?int $fixedColumns = null): string
    {
        $gridColumns = $fixedColumns !== null
            ? "    grid-template-columns: repeat({$fixedColumns}, minmax(0, 1fr));\n"
            : "    grid-template-columns: repeat(auto-fit, minmax(min(100%, 16rem), 1fr));\n";

        $columnModifiers = '';
        if ($prefix === 'pricing') {
            $columnModifiers = <<<CSS

.{$prefix}__grid--cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.{$prefix}__grid--cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.{$prefix}__grid--cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
CSS;
        }

        $mediaQuery = $fixedColumns !== null
            ? "@media (max-width: 768px) { .{$prefix}__grid { grid-template-columns: 1fr; max-width: 24rem; margin-inline: auto; } }"
            : "@media (max-width: 768px) { .{$prefix}__grid { grid-template-columns: 1fr; } }";

        return <<<CSS
.{$prefix}__intro {
    text-align: center;
    margin-block-end: var(--d-spacing-xl);
}

.{$prefix}__eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--primary);
    font-weight: 600;
}

.{$prefix}__headline {
    margin-block-start: var(--d-spacing-xs);
    text-wrap: balance;
}

.{$prefix}__description {
    max-width: 40rem;
    margin-inline: auto;
    margin-block-start: var(--d-spacing-sm);
    text-wrap: pretty;
}

.{$prefix}__grid {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
{$gridColumns}    gap: var(--d-spacing-lg);
    align-items: stretch;
}
{$columnModifiers}

.{$prefix}__item {
    display: flex;
    min-width: 0;
}

.{$prefix}__plan {
    position: relative;
    flex: 1;
    min-width: 0;
}

.{$prefix}__plan--featured,
.{$prefix}__plan--recommended {
    z-index: 1;
}

.{$prefix}__ribbon {
    position: absolute;
    inset-block-start: 0;
    inset-inline-end: var(--d-spacing-lg);
    transform: translateY(-50%);
}

.{$prefix}__price-row {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
    flex-wrap: wrap;
    margin: 0;
    margin-block-start: var(--d-spacing-sm);
}

.{$prefix}__price,
.{$prefix}__plan-price {
    font-size: var(--d-text-3xl);
    font-weight: 800;
    line-height: 1.1;
}

.{$prefix}__content {
    flex: 1 1 auto;
}

.{$prefix}__features {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: var(--d-spacing-xs);
}

.{$prefix}__feature {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    min-width: 0;
}

.{$prefix}__check {
    flex-shrink: 0;
    color: var(--primary);
    margin-block-start: 0.125rem;
}

.{$prefix}__footer {
    margin-block-start: auto;
}

.{$prefix}__button {
    justify-content: center;
}

{$mediaQuery}
CSS;
    }

    private function pricingSimpleCss(): string
    {
        return <<<'CSS'
.pricing-simple__card {
    max-width: 28rem;
    margin-inline: auto;
    text-align: center;
}

.pricing-simple__header {
    text-align: center;
}

.pricing-simple__price-row {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.25rem;
    margin-block-start: var(--d-spacing-md);
    margin: 0;
}

.pricing-simple__price {
    font-size: var(--d-text-4xl);
    font-weight: 800;
    line-height: 1.1;
}

.pricing-simple__content {
    text-align: start;
}

.pricing-simple__features {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: var(--d-spacing-xs);
}

.pricing-simple__feature {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    min-width: 0;
}

.pricing-simple__check {
    flex-shrink: 0;
    color: var(--primary);
    margin-block-start: 0.125rem;
}

.pricing-simple__footer {
    margin-block-start: auto;
}

.pricing-simple__button {
    justify-content: center;
}
CSS;
    }

    private function pricingEnterpriseCss(): string
    {
        return <<<'CSS'
.pricing-enterprise__card {
    overflow: hidden;
}

.pricing-enterprise__layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--d-spacing-xl);
    min-width: 0;
}

@media (min-width: 768px) {
    .pricing-enterprise__layout {
        grid-template-columns: 1fr 1fr;
        align-items: center;
    }
}

.pricing-enterprise__content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.pricing-enterprise__eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--primary);
    font-weight: 600;
}

.pricing-enterprise__features {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: var(--d-spacing-xs);
}

.pricing-enterprise__feature {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    min-width: 0;
}

.pricing-enterprise__check {
    flex-shrink: 0;
    color: var(--primary);
    margin-block-start: 0.125rem;
}

.pricing-enterprise__footer {
    margin-block-start: auto;
}

.pricing-enterprise__media {
    min-width: 0;
}

.pricing-enterprise__image {
    width: 100%;
    height: auto;
    border-radius: var(--radius);
}
CSS;
    }

    private function stripButtonVisualRules(string $css): string
    {
        $css = (string) preg_replace('~\.[a-z0-9_-]+__button--(?:primary|outline|secondary|ghost)\s*\{[^}]*\}\s*~s', '', $css);
        $css = (string) preg_replace('~\.[a-z0-9_-]+__button(?::hover|:focus-visible|:active)\s*\{[^}]*\}\s*~s', '', $css);
        $css = (string) preg_replace_callback(
            '~(\.[a-z0-9_-]+__button)\s*\{([^}]*)\}~s',
            static function (array $matches): string {
                $properties = $matches[2];
                $visualProps = [
                    'background',
                    'border',
                    'color:',
                    'padding',
                    'text-decoration',
                    'opacity',
                    'box-shadow',
                    'transform',
                    'transition',
                    'font-weight',
                    'font-size',
                    'display:',
                    'align-items',
                ];
                $hasVisual = false;
                foreach ($visualProps as $prop) {
                    if (str_contains($properties, $prop)) {
                        $hasVisual = true;
                        break;
                    }
                }

                if (!$hasVisual) {
                    return $matches[0];
                }

                $kept = [];
                foreach (preg_split('/;\s*/', trim($properties)) ?: [] as $declaration) {
                    $declaration = trim($declaration);
                    if ($declaration === '') {
                        continue;
                    }
                    $isVisual = false;
                    foreach ($visualProps as $prop) {
                        if (str_starts_with($declaration, $prop)) {
                            $isVisual = true;
                            break;
                        }
                    }
                    if (!$isVisual) {
                        $kept[] = $declaration;
                    }
                }

                if ($kept === []) {
                    return '';
                }

                return $matches[1] . ' { ' . implode('; ', $kept) . '; }';
            },
            $css,
        );

        $css = (string) preg_replace('~\.[a-z0-9_-]+__button--(?:primary|outline|secondary|ghost):[a-z-]+\s*\{[^}]*\}\s*~s', '', $css);
        $css = (string) preg_replace('~@media\s*\(prefers-reduced-motion:[^{]+\{[^}]*__button[^}]*\}\s*~s', '', $css);
        $css = trim($css) . "\n";

        return $css;
    }

    private function countRemainingTypolinks(): int
    {
        $count = 0;
        $templates = glob($this->contentBlocksDir . '/*/templates/frontend.html') ?: [];
        foreach ($templates as $templatePath) {
            $content = (string) file_get_contents($templatePath);
            $count += substr_count($content, 'f:link.typolink');
        }

        return $count;
    }
}

final class FluidTagWalker
{
    /**
     * @param callable(string $attrs, string $inner): ?string $replacer
     */
    public static function replaceTags(string $html, string $tagName, callable $replacer): string
    {
        $offset = 0;
        $result = '';

        while (true) {
            $openPos = strpos($html, '<' . $tagName, $offset);
            if ($openPos === false) {
                $result .= substr($html, $offset);
                break;
            }

            $result .= substr($html, $offset, $openPos - $offset);
            $tag = self::readTag($html, $openPos, $tagName);
            if ($tag === null) {
                $result .= substr($html, $openPos, 1);
                $offset = $openPos + 1;
                continue;
            }

            $replacement = $replacer($tag['attrs'], $tag['inner']);
            if ($replacement === null) {
                $result .= $tag['full'];
            } else {
                $result .= $replacement;
            }

            $offset = $tag['end'];
        }

        return $result;
    }

    /**
     * @return array{attrs: string, inner: string, full: string, end: int}|null
     */
    private static function readTag(string $html, int $start, string $tagName): ?array
    {
        $openEnd = self::findTagClose($html, $start);
        if ($openEnd === null) {
            return null;
        }

        $openTag = substr($html, $start, $openEnd - $start + 1);
        if (!preg_match('/^<' . preg_quote($tagName, '/') . '\b([^>]*)>$/s', $openTag, $matches)) {
            return null;
        }

        $attrs = trim($matches[1]);
        $innerStart = $openEnd + 1;
        $closeTag = '</' . $tagName . '>';
        $depth = 1;
        $pos = $innerStart;

        while ($depth > 0) {
            $nextOpen = strpos($html, '<' . $tagName, $pos);
            $nextClose = strpos($html, $closeTag, $pos);
            if ($nextClose === false) {
                return null;
            }

            if ($nextOpen !== false && $nextOpen < $nextClose) {
                ++$depth;
                $pos = $nextOpen + strlen($tagName) + 1;
                continue;
            }

            --$depth;
            if ($depth === 0) {
                $inner = substr($html, $innerStart, $nextClose - $innerStart);
                $end = $nextClose + strlen($closeTag);

                return [
                    'attrs' => $attrs,
                    'inner' => $inner,
                    'full' => substr($html, $start, $end - $start),
                    'end' => $end,
                ];
            }

            $pos = $nextClose + strlen($closeTag);
        }

        return null;
    }

    public static function extractAttribute(string $attrs, string $name): ?string
    {
        if (preg_match('/\b' . preg_quote($name, '/') . '="([^"]*)"/', $attrs, $match)) {
            return $match[1];
        }

        if (preg_match("/\\b" . preg_quote($name, '/') . "='([^']*)'/", $attrs, $match)) {
            return $match[1];
        }

        return null;
    }

    /**
     * @return array{attrs: string, inner: string}|null
     */
    public static function readComponentTag(string $html, string $tagName): ?array
    {
        if (!preg_match('/^<' . preg_quote($tagName, '/') . '\b/', $html)) {
            return null;
        }

        $openEnd = self::findTagClose($html, 0);
        if ($openEnd === null) {
            return null;
        }

        $openTag = substr($html, 0, $openEnd + 1);
        if (!preg_match('/^<' . preg_quote($tagName, '/') . '\b(.*)>$/s', $openTag, $matches)) {
            return null;
        }

        $closeTag = '</' . $tagName . '>';
        $closePos = strrpos($html, $closeTag);
        if ($closePos === false || $closePos <= $openEnd) {
            return null;
        }

        return [
            'attrs' => trim($matches[1]),
            'inner' => substr($html, $openEnd + 1, $closePos - $openEnd - 1),
        ];
    }

    private static function findTagClose(string $html, int $start): ?int
    {
        $length = strlen($html);
        $inQuote = null;

        for ($i = $start; $i < $length; ++$i) {
            $char = $html[$i];

            if ($inQuote !== null) {
                if ($char === $inQuote && ($i === 0 || $html[$i - 1] !== '\\')) {
                    $inQuote = null;
                }
                continue;
            }

            if ($char === '"' || $char === "'") {
                $inQuote = $char;
                continue;
            }

            if ($char === '>') {
                return $i;
            }
        }

        return null;
    }
}
