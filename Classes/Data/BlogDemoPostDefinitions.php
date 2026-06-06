<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

/**
 * Demo Blog posts seeded by {@see \Webconsulting\Desiderio\Seeding\BlogPageTreeSeeder}.
 */
final class BlogDemoPostDefinitions
{
    /**
     * @return list<array{
     *   slug: string,
     *   title: string,
     *   subtitle: string,
     *   abstract: string,
     *   description: string,
     *   date: string,
     *   categories: list<string>,
     *   tags: list<string>,
     *   content: list<array{header: string, body: string}>
     * }>
     */
    public static function demoPosts(): array
    {
        return [
            [
                'slug' => '/first-blog-post',
                'title' => 'First blog post',
                'subtitle' => 'Complete metadata for the shadcn Blog template',
                'abstract' => 'A compact demo entry showing author, category, tag, date, and comments in one list item.',
                'description' => 'Use this post to check the detail page, metadata badges, comments, body copy, lists, and links.',
                'date' => '2026-04-04 10:00:00',
                'categories' => ['Blog', 'TYPO3'],
                'tags' => ['TYPO3', 'shadcn UI', 'Fluid'],
                'content' => [
                    [
                        'header' => 'Template coverage',
                        'body' => '<p>This seeded post is intentionally complete. It has authors, categories, tags, a readable teaser, a comment, and body content so the Blog list and detail templates can be checked in one place.</p>',
                    ],
                    [
                        'header' => 'What to verify',
                        'body' => '<ul><li>Category and tag badges appear once near the title.</li><li>Dates, authors, and comments stay in one flat metadata row.</li><li>The same tokenized badge treatment is used across list and detail views.</li></ul>',
                    ],
                ],
            ],
            [
                'slug' => '/holding-hands-through-spring-showers',
                'title' => 'Holding Hands Through Spring Showers',
                'subtitle' => 'Connection, care, and Easter in uncertain weather',
                'abstract' => 'A more human Easter story built around care and connection.',
                'description' => 'A post about connection, family, and staying close even when spring weather turns wet.',
                'date' => '2026-04-12 09:30:00',
                'categories' => ['Blog'],
                'tags' => ['Easter', 'Spring', 'Connection', 'Family'],
                'content' => [
                    [
                        'header' => 'A quieter seasonal story',
                        'body' => '<p>Not every seasonal article needs to be bright and busy. This example keeps the tone calm so typography, spacing, and badge treatment can be reviewed with realistic editorial copy.</p>',
                    ],
                    [
                        'header' => 'Editorial notes',
                        'body' => '<p>The abstract is short enough for a card or list teaser, while the description gives search engines and social previews a clearer sentence.</p>',
                    ],
                ],
            ],
            [
                'slug' => '/minimal-egg-decorating-for-a-bright-table',
                'title' => 'Minimal Egg Decorating for a Bright Table',
                'subtitle' => 'Simple color, clean layouts, and easy Easter styling',
                'abstract' => 'Minimal Easter decorating ideas that still feel warm and festive.',
                'description' => 'An Easter post focused on clean egg styling and light seasonal decoration.',
                'date' => '2026-04-05 08:15:00',
                'categories' => ['Blog'],
                'tags' => ['Easter', 'Decor', 'Spring'],
                'content' => [
                    [
                        'header' => 'Keep the layout breathable',
                        'body' => '<p>Minimal decoration works well for this template because it tests long lines, short paragraphs, and compact badge groups without turning the page into a dense block.</p>',
                    ],
                    [
                        'header' => 'Checklist',
                        'body' => '<ul><li>Use one main surface color.</li><li>Keep decorative detail low and readable.</li><li>Let spacing do more work than borders.</li></ul>',
                    ],
                ],
            ],
        ];
    }
}
