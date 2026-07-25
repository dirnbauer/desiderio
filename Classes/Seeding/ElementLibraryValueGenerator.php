<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

/**
 * Neutral demo value generator for the element library seeder.
 *
 * The styleguide generator fills fields with copy that talks about Desiderio,
 * shadcn/ui and "patterns" — perfect for the styleguide, wrong for the element
 * library picker, where an editor should see a believable, generic example and
 * immediately understand what to type in each field.
 *
 * This subclass keeps every field-type heuristic of the parent and only swaps
 * the vocabulary: realistic copy for a fictional but ordinary business, with no
 * self-promotion. {@see StyleguideDemoValueGenerator} stays untouched, so the
 * styleguide seed is unaffected.
 */
final class ElementLibraryValueGenerator extends StyleguideDemoValueGenerator
{
    protected function demoBadges(): array
    {
        return [
            'New',
            'Popular',
            'Featured',
            'Now available',
            'Customer favorite',
            'Limited offer',
        ];
    }

    protected function demoButtonLabels(): array
    {
        return [
            'Learn more',
            'Get started',
            'Book a demo',
            'See pricing',
            'Contact sales',
        ];
    }

    protected function demoCopy(): array
    {
        return [
            'Bring your team, tools, and customers together in one place — and spend less time switching between tabs.',
            'Everything you need to plan, launch, and grow, backed by support that actually responds.',
            'Set up in minutes, invite your team, and see results in your first week.',
            'Clear pricing, no long-term contracts, and the freedom to change plans whenever your needs do.',
            'Built to scale with you, from your very first project to your busiest season.',
        ];
    }

    protected function demoFeatures(): array
    {
        return [
            'Unlimited projects',
            'Real-time collaboration',
            'Advanced analytics',
            'Single sign-on (SSO)',
            'Priority support',
            'Mobile and desktop apps',
            '99.9% uptime guarantee',
        ];
    }

    protected function demoLinkLabels(): array
    {
        return ['Home', 'Features', 'Pricing', 'About', 'Contact'];
    }

    /**
     * The element library's demo cast.
     *
     * ORDER AND SIZE ARE A CONTRACT. Names are picked with `$index % count()`
     * and LibraryImageAssetProvider indexes the portrait pool with the same
     * modulus, so entry N here must correspond to `lib-portrait-<N+1>-*` in
     * Resources/Public/Styleguide/Library. Adding, removing or reordering a
     * person without regenerating that portrait puts the wrong face next to
     * the name. LibraryCastPortraitsTest enforces the pairing.
     *
     * Twelve entries because that covers the largest team/testimonial
     * collection any element renders without a face repeating on one page.
     */
    protected function demoPeople(): array
    {
        return [
            ['Anna Hofer', 'Marketing Manager', 'Brightpath'],
            ['Daniel Mayer', 'Operations Lead', 'Northwind Co.'],
            ['Sophie Lambert', 'Founder', 'Atlas Studio'],
            ['Thomas Berger', 'IT Director', 'Meridian Group'],
            ['Elena Rossi', 'Product Manager', 'Lumen Labs'],
            ['Marcus Feld', 'Head of Support', 'Brightpath'],
            ['Priya Raman', 'Data Lead', 'Lumen Labs'],
            ['Jonas Weber', 'Solutions Architect', 'Meridian Group'],
            ['Clara Fontaine', 'Content Strategist', 'Atlas Studio'],
            ['Tobias Lang', 'Finance Director', 'Northwind Co.'],
            ['Nadia Ben Salah', 'Customer Success Lead', 'Brightpath'],
            ['Erik Sandberg', 'Engineering Manager', 'Lumen Labs'],
        ];
    }

    /**
     * Fallback headings for an element that ships no library.json.
     *
     * The eight entries this replaces ("Support that scales with you", "Simple,
     * powerful, reliable", …) were the whole reason the picker was unreadable:
     * a shared pool of interchangeable slogans landed on dozens of unrelated
     * elements and told the editor nothing. These are concrete section headings
     * instead — still generic enough to suit any element, but each one at least
     * describes a real kind of page section.
     *
     * This is a safety net, not the intended path. Authored demo content lives
     * in each element's library.json; anything relying on this pool will
     * eventually collide with a neighbour, which is why
     * Tests/Unit/LibraryFixtureTest requires every element to have one.
     */
    protected function demoSubjects(): array
    {
        return [
            'What changed in this release',
            'How the rollout went, month by month',
            'The numbers behind the last quarter',
            'Where teams spend their first week',
            'Answers to the questions we get most',
            'What our customers measured afterwards',
            'A closer look at how it fits together',
            'Which plan covers which use case',
        ];
    }

    protected function demoTabPanelCopy(): array
    {
        return [
            'Give each topic its own space so visitors can focus on what matters to them. Switch between tabs to compare options, features, or plans without ever leaving the page.',
            'Use tabs to organize detailed information into clear sections. Each panel can hold text, lists, or media, so you can present a lot of content without overwhelming the reader.',
            'Group related content so people find answers faster. The first tab opens by default and the rest are one click away — ideal for FAQs, specifications, or step-by-step guides.',
        ];
    }

    protected function demoTopics(): array
    {
        return ['Product Updates', 'How-To Guides', 'Company News', 'Best Practices', 'Customer Stories'];
    }

    protected function demoRowData(int $index): string
    {
        return ['Starter|Included|5 users', 'Team|Included|25 users', 'Business|Included|Unlimited'][$index % 3];
    }

    protected function demoTierValues(): string
    {
        return 'Included,Advanced,Priority';
    }

    /**
     * Plain marketing copy — no "Built for the … pattern." suffix that would
     * remind editors this is a component demo rather than their own content.
     */
    public function buildDefaultDemoCopy(string $elementLabel, string $fieldLabel, int $index): string
    {
        $copy = $this->demoCopy();

        return $copy[$index % count($copy)];
    }

    /**
     * A believable customer quote instead of a sentence about the element.
     */
    public function buildDefaultQuote(string $elementLabel): string
    {
        return 'Switching over was the best decision we made this year. Our team is faster, and our customers have noticed the difference.';
    }

    /**
     * A clean headline. The parent prefixes the element name ("Hero: …") and
     * appends an index, which reads like a demo label; the library wants copy
     * that looks like a finished, neutral headline an editor can keep or edit.
     */
    public function buildDemoSubject(string $name, int $index): string
    {
        return $this->pickDemoString($this->demoSubjects(), $name, $index);
    }

    /**
     * A fabricated `https://example.com/…` URL is worse than nothing when the
     * template pipes it straight into an `<img src>`: leaderboard's optional
     * `avatar_url` takes precedence over the uploaded portrait, so the invented
     * URL both 404s and hides the real image. Leaving these image-URL fields
     * empty lets the template fall through to the file it was given.
     *
     * Only image-shaped URL fields are suppressed. A demo link still gets a
     * placeholder href, because an empty link renders as unclickable text and
     * that hides a different thing.
     */
    public function buildDefaultLinkValue(string $field, int $index): string
    {
        $normalized = $this->normalizeIdentifier($field);
        $isImageUrl = str_contains($normalized, 'url') && (
            str_contains($normalized, 'avatar')
            || str_contains($normalized, 'image')
            || str_contains($normalized, 'photo')
            || str_contains($normalized, 'portrait')
            || str_contains($normalized, 'logo')
            || str_contains($normalized, 'thumbnail')
        );

        return $isImageUrl ? '' : parent::buildDefaultLinkValue($field, $index);
    }
}
