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
                'subtitle' => 'A post with every metadata field filled in',
                'abstract' => 'A short demo post that shows author, category, tag, date and comments in one list item.',
                'description' => 'Use this post to check the detail page, metadata badges, comments, body text, lists and links.',
                'date' => '2026-04-04 10:00:00',
                'categories' => ['Blog', 'TYPO3'],
                'tags' => ['TYPO3', 'shadcn UI', 'Fluid'],
                'content' => [
                    [
                        'header' => 'Template coverage',
                        'body' => '<p>This post has every field filled in on purpose. It has authors, categories, tags, a teaser, a comment and body text. You can check the blog list and detail templates in one place.</p>',
                    ],
                    [
                        'header' => 'What to verify',
                        'body' => '<ul><li>Category and tag badges appear once, near the title.</li><li>Date, authors and comments share one metadata row.</li><li>Badges look the same in the list and in the detail view.</li></ul>',
                    ],
                ],
            ],
            [
                'slug' => '/holding-hands-through-spring-showers',
                'title' => 'Holding hands through spring showers',
                'subtitle' => 'Family, care and Easter when the weather turns',
                'abstract' => 'An Easter story about care and staying close.',
                'description' => 'A post about family and staying close, even when the spring weather turns wet.',
                'date' => '2026-04-12 09:30:00',
                'categories' => ['Blog'],
                'tags' => ['Easter', 'Spring', 'Connection', 'Family'],
                'content' => [
                    [
                        'header' => 'A quieter seasonal story',
                        'body' => '<p>Not every seasonal article has to be bright and busy. This one keeps a calm tone, so you can review typography, spacing and badges with realistic text.</p>',
                    ],
                    [
                        'header' => 'Editorial notes',
                        'body' => '<p>The abstract is short enough for a card or a list teaser. The description gives search engines and social previews one clear sentence.</p>',
                    ],
                ],
            ],
            [
                'slug' => '/minimal-egg-decorating-for-a-bright-table',
                'title' => 'Minimal egg decorating for a bright table',
                'subtitle' => 'One colour, clean lines and simple Easter styling',
                'abstract' => 'Simple Easter decorating ideas that still feel warm and festive.',
                'description' => 'An Easter post about simple egg designs and light seasonal decoration.',
                'date' => '2026-04-05 08:15:00',
                'categories' => ['Blog'],
                'tags' => ['Easter', 'Decor', 'Spring'],
                'content' => [
                    [
                        'header' => 'Keep the layout breathable',
                        'body' => '<p>Minimal decoration suits this template. It tests long lines, short paragraphs and small groups of badges, and the page never looks crowded.</p>',
                    ],
                    [
                        'header' => 'Checklist',
                        'body' => '<ul><li>Use one main colour.</li><li>Keep decorative details few and clear.</li><li>Let space do more work than borders.</li></ul>',
                    ],
                ],
            ],
        ];
    }
}
