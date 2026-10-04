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
     *   content: list<array{header: string, body: string}>,
     *   comment: array{name: string, email: string, text: string},
     *   image: array{file: string, alternative: string}
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
                        'body' => '<ul><li>Every category and tag links to its own list of posts.</li><li>Author, date and reading time appear near the title.</li><li>Badges look the same in the list and on the post.</li></ul>',
                    ],
                ],
                'comment' => [
                    'name' => 'Mira Novak',
                    'email' => 'mira.novak@example.com',
                    'text' => 'We open this post after every template change to check that nothing moved. It works well as a checklist.',
                ],
                'image' => [
                    'file' => 'Resources/Public/Styleguide/Blog/blog-first-post-7b0c26bc.webp',
                    'alternative' => 'A laptop with a blog layout on a desk, next to a notebook and a cup.',
                ],
            ],
            [
                'slug' => '/writing-teasers-for-the-blog-list',
                'title' => 'Writing teasers for the blog list',
                'subtitle' => 'Title, abstract and description each have a job',
                'abstract' => 'How the title, the abstract and the description share the work of introducing a post.',
                'description' => 'A short guide to the three texts that introduce every post in the list, in search results and in social previews.',
                'date' => '2026-04-12 09:30:00',
                'categories' => ['Blog', 'Editors'],
                'tags' => ['Editing', 'SEO'],
                'content' => [
                    [
                        'header' => 'Three texts, three jobs',
                        'body' => '<p>The title names the topic. The abstract tells readers in the list why the post is worth opening. The description is the one sentence that search engines and social previews show.</p>',
                    ],
                    [
                        'header' => 'Length guide',
                        'body' => '<ul><li>Title: up to 60 characters.</li><li>Abstract: one or two sentences.</li><li>Description: up to 160 characters.</li></ul>',
                    ],
                ],
                'comment' => [
                    'name' => 'Lena Hofer',
                    'email' => 'lena.hofer@example.com',
                    'text' => 'The length guide is handy. We now check every abstract against it before a post goes live.',
                ],
                'image' => [
                    'file' => 'Resources/Public/Styleguide/Blog/blog-teasers-index-cards-192d3fa1.webp',
                    'alternative' => 'Three blank index cards of different lengths in a row on a desk, with a pencil and a cup.',
                ],
            ],
            [
                'slug' => '/a-calm-layout-for-long-posts',
                'title' => 'A calm layout for long posts',
                'subtitle' => 'Line length, spacing and headings that keep readers going',
                'abstract' => 'Why long posts read better with a narrow column, generous spacing and clear headings.',
                'description' => 'How the post template keeps long articles readable: line length, space between paragraphs and headings in order.',
                'date' => '2026-04-05 08:15:00',
                'categories' => ['Blog'],
                'tags' => ['Layout', 'Typography', 'Fluid'],
                'content' => [
                    [
                        'header' => 'Keep the column narrow',
                        'body' => '<p>Lines of 60 to 75 characters are easier to follow than lines that run across the whole screen. The post template caps the text column for that reason.</p>',
                    ],
                    [
                        'header' => 'Checklist',
                        'body' => '<ul><li>One idea per paragraph.</li><li>Headings in order, never skipping a level.</li><li>Space between sections instead of borders.</li></ul>',
                    ],
                ],
                'comment' => [
                    'name' => 'Jonas Berger',
                    'email' => 'jonas.berger@example.com',
                    'text' => 'Capping the line length made our longest how-to much easier to read on a laptop.',
                ],
                'image' => [
                    'file' => 'Resources/Public/Styleguide/Blog/blog-calm-layout-open-book-9e24e1ef.webp',
                    'alternative' => 'An open book with wide margins on a desk, with reading glasses, a plant and a cup.',
                ],
            ],
        ];
    }

    /**
     * Demo posts earlier versions seeded and the seeder now removes again:
     * two lifestyle posts (spring showers, Easter eggs) that read oddly in a
     * blog about TYPO3.
     *
     * @return list<string>
     */
    public static function retiredSlugs(): array
    {
        return ['/holding-hands-through-spring-showers', '/minimal-egg-decorating-for-a-bright-table'];
    }

    /**
     * The one example comment earlier versions put on every post. The "Recent
     * comments" widget then listed the same sentence again and again; the
     * seeder removes it wherever it is left over.
     */
    public const LEGACY_EXAMPLE_COMMENT = 'This is an example comment. Readers can comment on every post, and an editor approves each comment before it appears.';
}
