<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

use Webconsulting\Desiderio\Data\Showcase\Software\CapexOne;
use Webconsulting\Desiderio\Data\Showcase\Software\Ledger;
use Webconsulting\Desiderio\Data\Showcase\Software\MedAtlas;
use Webconsulting\Desiderio\Data\Showcase\Software\T3Ops;

/**
 * The /software section: the products webconsulting builds and runs on the
 * same stack as this lab. ShowcaseFeaturePages turns the hub and the one
 * category into pages; each product keeps its copy in its own class below
 * Software/, so the four pages can be written side by side.
 *
 * @phpstan-import-type FeatureCategory from ShowcaseFeatureDefinitions
 * @phpstan-import-type FeatureHub from ShowcaseFeatureDefinitions
 */
final class ShowcaseSoftwareDefinitions
{
    /**
     * The Software hub: four products, the stack they share, and how they
     * open up to agents.
     *
     * @return FeatureHub
     */
    public static function hub(): array
    {
        return [
            'title' => 'Software by webconsulting',
            'navTitle' => 'Software',
            'seoTitle' => 'Software by webconsulting: four products we run',
            'description' => 'Four products built on this lab’s stack: T3 Ops for TYPO3 fleets, Ledger for the month-end, MedAtlas Austria for medicine shortages and CapexOne Plus.',
            'abstract' => 'Four products on this lab’s stack: T3 Ops runs TYPO3 fleets, Ledger closes the month, MedAtlas tracks shortages, CapexOne Plus governs capital projects.',
            'badge' => 'Four products',
            'header' => 'Software we build and run',
            'subheadline' => 'Four products from the same stack as this lab: Next.js, PostgreSQL, Coolify on EU servers, AI with people in charge.',
            'primaryButton' => ['text' => 'Book a call', 'link' => '{{page:desiderio-powermail/callback}}'],
            'secondaryButton' => ['text' => 'See T3 Ops', 'link' => '{{page:software/t3-ops}}'],
            'image' => ShowcaseHeroPhotos::for('software'),
            'sections' => [
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'How we build',
                    'header' => 'The same stack for every product',
                    'subheadline' => 'What the four products share, from the database to the people who approve.',
                    'items' => [
                        ['title' => 'Next.js and PostgreSQL', 'description' => 'Each product is a Next.js app on a PostgreSQL database, written in TypeScript.'],
                        ['title' => 'Coolify on Hetzner, in the EU', 'description' => 'Our platform runs on Coolify and Hetzner Cloud servers in the EU, with health checks and rollback to a known commit.'],
                        ['title' => 'AI reads and suggests, people decide', 'description' => 'AI reads receipts and invoices, drafts summaries and proposes changes. A person confirms before anything is saved.'],
                        ['title' => 'Monitored, backed up, audited', 'description' => 'Ledger and CapexOne Plus log every change. T3 Ops watches uptime, logs and the nightly backups.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Built for people and for agents',
                    'content' => '<p>T3 Ops, MedAtlas Austria and CapexOne Plus have documented REST APIs, so agents work under the same rules as people.</p>'
                        . '<p>' . ShowcaseStrategyPages::standardLine('each product') . '</p>',
                    'variant' => 'bordered',
                    'alignment' => 'center',
                    'link' => '{{page:typo3-v14-strategy}}',
                    'link_text' => 'Read our strategy',
                ]),
            ],
            'cta' => [
                'header' => 'Talk to us about one of these products',
                'description' => 'Tell us which product fits your work. We show it to you on your own questions.',
                'text' => 'Book a call',
                'link' => '{{page:desiderio-powermail/callback}}',
            ],
        ];
    }

    /**
     * One category with the four products, in a 2 x 2 grid on the hub.
     *
     * @return list<FeatureCategory>
     */
    public static function categories(): array
    {
        return [
            [
                'label' => 'Software',
                'header' => 'Four products, each one running',
                'subheadline' => 'Each page shows the running product in screenshots and a short film.',
                'columns' => '2',
                'features' => [T3Ops::definition(), Ledger::definition(), MedAtlas::definition(), CapexOne::definition()],
            ],
        ];
    }
}
