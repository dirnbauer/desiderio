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

    protected function demoPeople(): array
    {
        return [
            ['Anna Hofer', 'Marketing Manager', 'Brightpath'],
            ['Daniel Mayer', 'Operations Lead', 'Northwind Co.'],
            ['Sophie Lambert', 'Founder', 'Atlas Studio'],
            ['Thomas Berger', 'IT Director', 'Meridian Group'],
            ['Elena Rossi', 'Product Manager', 'Lumen Labs'],
        ];
    }

    protected function demoSubjects(): array
    {
        return [
            'Everything in one place',
            'Built for growing teams',
            'Work smarter, not harder',
            'Designed around your workflow',
            'Results you can measure',
            'Support that scales with you',
            'Simple, powerful, reliable',
            'Made for modern teams',
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
}
