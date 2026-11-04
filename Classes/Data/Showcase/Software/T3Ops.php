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
 * product-specific blocks (security problems, the Mini-Mi hosting offer,
 * Cloudflare, monitoring, backups) go into 'sections'.
 *
 * Every screen comes from the T3 Ops demo with fictional sites on .example
 * hosts (Build/Data/feature-screenshots.software-t3ops.json). The numbers the
 * copy states are the product's own: 4 security checks a day, findings cleared
 * within 5 minutes, 64 hardening items in 7 groups, 6 months of evidence,
 * batches of up to 25 projects. Mini-Mi is webconsulting's hosting offer
 * around T3 Ops, not a feature of the product: customer sites stay on their
 * own hosting unless they take the offer.
 *
 * @phpstan-import-type FeatureDefinition from \Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions
 * @phpstan-import-type ShowcaseMedia from \Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks
 */
final class T3Ops
{
    private const string DEMO = 'Screenshot of the T3 Ops demo with fictional sites.';
    private const string ILLUSTRATION = 'Illustration, AI-generated.';

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
                'image' => self::shot('frontend-feature-t3ops-overview-94f125f6.webp', 'Decisions first', 'T3 Ops overview: 15 of 22 demo sites at risk, a health ring and an inbox of security updates, most urgent first.'),
            ],
            'problem' => [
                'header' => 'One advisory, many sites, each update by hand',
                'content' => '<p>An advisory arrives. Each site differs in core, extensions and hosting, so updates happen by hand. Nobody can prove the site still looks the same.</p>',
            ],
            'tour' => [
                'header' => 'One screen for the whole fleet',
                'subheadline' => 'Screens from the T3 Ops demo with fictional sites. Nothing on them is a real client.',
                'shots' => [
                    [
                        'tab' => 'Security',
                        'title' => 'Core, extensions and advisories',
                        'description' => 'Which sites need a security update, how many can update on their own, and the seven sources of the last check.',
                        'image' => self::shot('frontend-feature-t3ops-security-8841b7c3.webp', 'Core, extensions and advisories', 'T3 Ops security page: 14 sites need security updates, seven sources of the last check and the schedule of four checks a day.'),
                    ],
                    [
                        'tab' => 'Update assistant',
                        'title' => 'Staging first, release second',
                        'description' => 'Pick the update and the projects, then review before anything starts. Each page is captured on desktop, tablet and phone.',
                        'image' => self::shot('frontend-feature-t3ops-update-assistant-0c6c3071.webp', 'Staging first, release second', 'Update assistant at the review step: four demo projects move from 14.3.6 or 14.3.7 to 14.3.9, with a screenshot sample.'),
                    ],
                    [
                        'tab' => 'Runs',
                        'title' => 'Every run with its evidence',
                        'description' => 'Readiness checks, update merge requests and releases, each with its staging and production state, result and report.',
                        'image' => self::shot('frontend-feature-t3ops-runs-8b9279bc.webp', 'Every run with its evidence', 'T3 Ops runs table: readiness checks, update merge requests and a release, each with its staging, production and status.'),
                    ],
                    [
                        'tab' => 'Sites',
                        'title' => 'Every site with its versions',
                        'description' => 'TYPO3 and PHP versions, insecure packages and open updates per site, with the problems first.',
                        'image' => self::shot('frontend-feature-t3ops-sites-1bcbd6bd.webp', 'Every site with its versions', 'T3 Ops sites list of 22 demo sites with TYPO3 and PHP versions, at-risk badges, open issues and project.'),
                    ],
                    [
                        'tab' => 'Hardening',
                        'title' => 'A hardening checklist per site',
                        'description' => '64 items in seven groups. This demo site fulfils 53 of them, and its release level stays at Report only.',
                        'image' => self::shot('frontend-feature-t3ops-hardening-d563196e.webp', 'A hardening checklist per site', 'Hardening checklist of a demo site: 83 %, 53 of 64 items fulfilled, release level Report only, first configuration items.'),
                    ],
                    [
                        'tab' => 'Dark mode',
                        'title' => 'The same overview in dark mode',
                        'description' => 'Every screen follows the light or dark setting of your system.',
                        'image' => self::shot('frontend-feature-t3ops-dark-9da3d01c.webp', 'The same overview in dark mode', 'T3 Ops overview in dark mode with the health ring, the security inbox and the TYPO3 versions in use.'),
                    ],
                    [
                        'tab' => 'Release flow',
                        'title' => 'From staging to production',
                        'description' => 'Check readiness, capture production, update and verify staging. Production is released as a separate step.',
                        'image' => self::illustration('frontend-feature-t3ops-illustration-release-flow-d3b7ab8e.webp', 'From staging to production', 'Diagram of five steps: check readiness, capture production, update staging, verify staging, then release the verified commit.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What T3 Ops does for a fleet',
                'items' => [
                    ['icon' => 'shield-check', 'title' => 'Security problems found the same day', 'description' => 'Four checks a day against TER, Packagist advisories with CVE ids, the TYPO3 security feed and ELTS. Findings clear within five minutes of the update.'],
                    ['icon' => 'git-branch', 'title' => 'Updates proven on staging', 'description' => 'A snapshot, baseline screenshots and a staging copy with outgoing mail switched off. Composer runs on a branch, then visual, smoke and click-path checks run.'],
                    ['icon' => 'file-text', 'title' => 'Evidence you can show the client', 'description' => 'Each run keeps before, after and difference screenshots for six months. The ticket is booked only after a verified production release.'],
                    ['icon' => 'list-checks', 'title' => 'Hardening with a checklist', 'description' => '64 items in seven groups, from TYPO3 configuration and backend accounts to HTTPS headers and operations. Release levels go from Report only to Strict.'],
                ],
            ],
            'sections' => [
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Security problems',
                    'header' => 'What goes wrong on TYPO3 sites',
                    'subheadline' => 'Four problems we see on sites again and again, and how T3 Ops checks for each one.',
                    'items' => [
                        ['title' => 'Unpatched core and extensions', 'description' => 'A known hole stays open until someone updates. T3 Ops compares every site with TER flags, Packagist advisories, the TYPO3 security feed and ELTS.'],
                        ['title' => 'An exposed Install Tool', 'description' => 'A reachable Install Tool or a stray file in the web root invites attacks. The hardening checklist covers both, next to backend accounts and configuration.'],
                        ['title' => 'Missing headers, expiring certificates', 'description' => 'The HTTPS check reports HSTS, CSP, frame, referrer and permissions headers. A certificate is flagged 14 days before it expires.'],
                        ['title' => 'Vulnerable Composer packages', 'description' => 'A library in the vendor folder can carry a known hole. Each update runs composer audit before and after; the last must be clean.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_featuresplit', [
                    'header' => 'Mini-Mi: one Coolify per customer',
                    'description' => '<p>Mini-Mi, said like mini me, is our hosting offer: your own Coolify on your own VPS, like a very small Mittwald.</p>',
                    'image' => self::illustration('frontend-feature-t3ops-illustration-workspace-7e9a5058.webp', 'T3 Ops at a glance', 'Diagram: a project with one GitLab repository feeds staging and production, with monitoring and runs with evidence below.'),
                    'items' => [
                        ['title' => 'T3 Ops as the control plane', 'description_text' => 'T3 Ops runs the updates, checks and backups. Nothing on the VPS is shared with another customer.'],
                        ['title' => 'Built from a Dockerfile', 'description_text' => 'Each app has a persistent volume and a health check. Coolify manages its HTTPS certificate.'],
                        ['title' => 'Secrets only at runtime', 'description_text' => 'Keys live in Coolify’s environment variables and never end up in a build.'],
                        ['title' => 'Releases pinned to the tested commit', 'description_text' => 'GitLab deploys through the Coolify API. A rollback deploys a commit that worked before.'],
                        ['title' => 'Housekeeping and limits', 'description_text' => 'Old images are cleaned up at 75 % disk use, and resource limits keep one app from starving the others.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Cloudflare',
                    'header' => 'Connected to Cloudflare',
                    'subheadline' => 'For sites behind Cloudflare, T3 Ops reads the zone and keeps its own checks out of the way.',
                    'items' => [
                        ['title' => 'Zone settings at a glance', 'description' => 'Plan, SSL mode, Always Use HTTPS, minimum TLS version, bot protection and seven days of traffic, with a warning for Flexible SSL.'],
                        ['title' => 'Cache purged after a deployment', 'description' => 'After each deployment, T3 Ops purges the cache by hostname, and the purge is logged.'],
                        ['title' => 'Checks pass the firewall', 'description' => 'A WAF skip rule lets the monitoring and regression checks through, keyed on a secret header.'],
                        ['title' => 'Challenge pages reported as blocked', 'description' => 'When Cloudflare shows a challenge instead of the page, the check says blocked, not visual change.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Monitoring',
                    'header' => 'Prometheus, Grafana, Loki and Uptime Kuma',
                    'subheadline' => 'Open-source monitoring on our own servers, linked from T3 Ops.',
                    'items' => [
                        ['title' => 'Prometheus metrics', 'description' => 'Sites with the TYPO3 Prometheus exporter report their figures, and T3 Ops shows them on its Metrics page.'],
                        ['title' => 'A Grafana operations dashboard', 'description' => 'CPU, memory and storage over time, which TYPO3 measurement endpoints respond, and messages by service.'],
                        ['title' => 'Logs kept for 60 days', 'description' => 'Loki stores the container logs. Fixed rules, not AI, explain common messages in plain language.'],
                        ['title' => 'Uptime Kuma per site', 'description' => 'A frontend check every 60 seconds and one that bypasses the cache, plus SQL checks, a Solr check and maintenance windows.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_featurechecklist', [
                    'eyebrow' => 'Backups',
                    'header' => 'Backups that are read back every day',
                    'items' => [
                        ['title' => 'Every night, encrypted', 'description_text' => 'Restic backs up the servers T3 Ops runs on in full, encrypted, in a window from 23:00.'],
                        ['title' => 'Stored in two places', 'description_text' => 'Each backup goes to Hetzner object storage and to Google Drive.'],
                        ['title' => 'Read back in full', 'description_text' => 'After writing, the whole backup is read back, and a check at 09:00 every day raises an alert when one fails.'],
                        ['title' => 'No update without a backup', 'description_text' => 'When the backup fails, the nightly updates of those servers wait.'],
                    ],
                ]),
            ],
            'faq' => [
                'header' => 'Questions about T3 Ops',
                'subheadline' => 'Hosting, releases, findings and versions.',
                'items' => [
                    ['question' => 'Do my sites have to move servers?', 'answer' => 'No. T3 Ops deploys over SSH to the hosting your sites already use. Mini-Mi is our offer for teams who want a platform of their own.'],
                    ['question' => 'Does T3 Ops release to production alone?', 'answer' => 'No. Every update goes to a staging copy first, and a person releases it. The production gate backs up the database and deploys the exact commit that passed on staging.'],
                    ['question' => 'What does a security finding look like?', 'answer' => 'One digest email per check, with the versions from and to, the advisory and the CVE ids. Its button opens a single-use link, valid for 24 hours, that only prepares a merge request.'],
                    ['question' => 'Which TYPO3 and PHP versions does it handle?', 'answer' => 'Runner images cover PHP 8.2 to 8.5. Minor updates run in batches of up to 25 projects. A major upgrade gets a plan and a work order for our upgrade skill, not an automatic upgrade.'],
                ],
            ],
            'install' => null,
            'cta' => [
                'header' => 'Put your fleet on one screen',
                'description' => 'See T3 Ops on a demo fleet. Like everything we build, it follows our strategy’s five points: governed tools, human approval, one registry, your choice of model, governance you can buy.',
                'text' => 'Book a demo',
                'link' => '{{page:desiderio-powermail/callback}}',
            ],
        ];
    }

    /**
     * A screen of the T3 Ops demo. The file names stay literal in the calls, so
     * Build/Scripts/capture-feature-screenshots.mjs --relink can rewrite them.
     *
     * @return ShowcaseMedia
     */
    private static function shot(string $file, string $title, string $alternative): array
    {
        return ShowcaseBlocks::screenshot($file, $title, $alternative, self::DEMO);
    }

    /**
     * One of the AI-generated infographics T3 Ops ships in its help pages.
     *
     * @return ShowcaseMedia
     */
    private static function illustration(string $file, string $title, string $alternative): array
    {
        return ShowcaseBlocks::screenshot($file, $title, $alternative, self::ILLUSTRATION);
    }
}
