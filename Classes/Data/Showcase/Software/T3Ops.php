<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase\Software;

use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;

/**
 * /software/t3-ops: T3 Ops, the control plane for a fleet of TYPO3 sites.
 *
 * Plan: docs/plans/2026-11-04-software-ai-features-plan.md in the lab, section
 * 4.2 (page copy), 3.2 (title, nav title, abstract), 1.4 (what the page may
 * claim) and 5.2 (screenshots). The page follows the shared feature outline;
 * product-specific blocks (security problems, Coolify and Mini-Mi, Cloudflare,
 * monitoring, backups) go into 'sections'.
 *
 * @phpstan-import-type FeatureDefinition from \Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions
 */
final class T3Ops
{
    /**
     * @return FeatureDefinition
     */
    public static function definition(): array
    {
        return [
            'slug' => 't3-ops',
            'product' => 'T3 Ops',
            'badge' => 'T3 Ops',
            'title' => 'T3 Ops: run many TYPO3 sites safely',
            'navTitle' => 'T3 Ops',
            'description' => 'T3 Ops checks TYPO3 fleets for security problems four times a day, proves updates on staging before release and watches monitoring and backups.',
            'abstract' => 'Security checks four times a day, updates proven on staging before release, monitoring and verified backups, for every site of a fleet.',
            'hero' => [
                'header' => 'Run many TYPO3 sites without the fear',
                'subheadline' => 'Security checks four times a day, updates proven on a staging copy before release, monitoring and verified backups: one screen for the whole fleet.',
                'primaryButton' => ['text' => 'Book a demo', 'link' => '{{page:desiderio-powermail/callback}}'],
                'secondaryButton' => ['text' => 'Read the strategy', 'link' => '{{page:typo3-v14-strategy}}'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-t3ops-overview-94f125f6.webp', 'Decisions first', 'T3 Ops overview with open decisions, security findings and recent update runs for a fleet of fictional TYPO3 sites.', 'Screenshot of the T3 Ops demo with fictional sites.'),
            ],
            'problem' => ['header' => 'Sixteen sites, one advisory, every update by hand', 'content' => '<p>A security advisory arrives. Each site has its own core version, extensions and hosting, so every update is done by hand. Afterwards nobody can prove that the site still looks the same.</p>'],
            'tour' => [
                'header' => 'One screen for the whole fleet',
                'subheadline' => 'Screens from the T3 Ops demo with fictional sites.',
                'shots' => [
                    [
                        'tab' => 'Security',
                        'title' => 'Core, extensions and advisories',
                        'description' => 'Findings for core and extensions, with the advisory and its CVE id, checked four times a day.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-t3ops-security-8841b7c3.webp', 'Core, extensions and advisories', 'T3 Ops security view listing TYPO3 core and extension findings with advisory and CVE ids for fictional sites.', 'Screenshot of the T3 Ops demo with fictional sites.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What T3 Ops does for a fleet',
                'items' => [
                    ['icon' => 'shield-check', 'title' => 'Security problems found the same day', 'description' => 'Four checks a day against TER flags, Packagist advisories, the TYPO3 security feed and ELTS. Findings clear within five minutes after the update.'],
                    ['icon' => 'git-branch', 'title' => 'Updates proven on staging', 'description' => 'A snapshot, baseline screenshots and a staging copy with mail switched off. Composer runs on a branch, then visual and click-path checks run.'],
                    ['icon' => 'file-text', 'title' => 'Evidence you can show the client', 'description' => 'Each run keeps before, after and difference screenshots for six months. The ticket is booked only after a verified release.'],
                    ['icon' => 'list-checks', 'title' => 'Hardening with a checklist', 'description' => '64 items in seven groups, from TYPO3 configuration to operations, with levels from Report only to Strict.'],
                ],
            ],
            'sections' => [],
            'faq' => [
                'header' => 'Questions about T3 Ops',
                'subheadline' => 'Hosting, releases, findings and versions.',
                'items' => [
                    ['question' => 'Do my sites have to move servers?', 'answer' => 'No. T3 Ops deploys over SSH to the hosting you already have.'],
                    ['question' => 'Does T3 Ops release to production alone?', 'answer' => 'No. Staging comes first and a person releases. The production gate backs up the database and deploys the exact tested commit.'],
                    ['question' => 'What does a security finding look like?', 'answer' => 'A digest mail with the versions from and to, the advisory and CVE ids, and a single-use link that prepares a merge request.'],
                    ['question' => 'Which TYPO3 and PHP versions does it handle?', 'answer' => 'Runner images cover PHP 8.2 to 8.5. Minor updates run in batches; a major upgrade produces a plan and a work order for the upgrade skill.'],
                ],
            ],
            'install' => null,
            'cta' => [
                'header' => 'Put your fleet on one screen',
                'description' => 'See T3 Ops on a demo fleet. Like everything we build, it follows the five points of our strategy.',
                'text' => 'Book a demo',
                'link' => '{{page:desiderio-powermail/callback}}',
            ],
        ];
    }
}
