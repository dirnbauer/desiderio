<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Talk pages: Kurt Dirnbauer's five-minute talk on ten usability slogans, below
 * the Lab and resources page. The films seeder places the long cut (portrait,
 * with its transcript) right after the header section, so the header section
 * opens the page and nothing goes between the two.
 *
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseTalkPages
{
    public const string NIELSEN_URL = 'https://www.nngroup.com/books/usability-engineering/';
    public const string ARTICLE_URL = 'https://www.webconsulting.at/blog/usability-slogans-nielsen';

    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [
            self::usabilitySlogansPage(),
        ];
    }

    /**
     * The ten slogans in the order of the talk, with the line the film's
     * description gives each one.
     *
     * @return list<array{title: string, description: string}>
     */
    public static function slogans(): array
    {
        return [
            ['title' => 'Your best guess is not good enough', 'description' => '<p>Test early, and let what users do replace assumptions.</p>'],
            ['title' => 'The user is always right', 'description' => '<p>Users are always right about their experience.</p>'],
            ['title' => 'The user is not always right', 'description' => '<p>Users are not always right about the solution.</p>'],
            ['title' => 'Users are not designers', 'description' => '<p>Watch what they do, not only what they ask for.</p>'],
            ['title' => 'Designers are not users', 'description' => '<p>They know too much, so the team is never the only test group.</p>'],
            ['title' => 'Vice presidents are not users', 'description' => '<p>A loud voice in the room does not replace a usability test.</p>'],
            ['title' => 'Less is more', 'description' => '<p>Focused, not empty.</p>'],
            ['title' => 'Details matter', 'description' => '<p>Labels, error messages and loading states are usability.</p>'],
            ['title' => 'Help doesn’t', 'description' => '<p>If users constantly need help, the product is too confusing.</p>'],
            ['title' => 'Usability engineering is a process', 'description' => '<p>Research, prototype, test, measure, improve, repeat.</p>'],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function usabilitySlogansPage(): array
    {
        return [
            'title' => 'Ten usability slogans',
            'navTitle' => 'Usability slogans (talk)',
            'slug' => '/usability-slogans',
            'seoTitle' => 'Ten usability slogans, explained by Kurt Dirnbauer',
            'abstract' => 'Kurt Dirnbauer’s five-minute talk on ten usability slogans after Jakob Nielsen, with transcript, and what they mean for editors.',
            'description' => 'Jakob Nielsen’s ten usability slogans and what each one asks of a product team, in a five-minute talk by Kurt Dirnbauer, with transcript.',
            'parentSlug' => 'resources',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'From the founder',
                    'header' => 'Ten usability slogans, explained by Kurt Dirnbauer',
                    'subheadline' => 'Usability means real people can use a product and reach their goals without friction. Five minutes on ten slogans every product team should know.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_featurenumbered', [
                    'header' => 'The ten slogans',
                    'subheadline' => 'In the order of the talk, each with one line from it.',
                    'items' => self::slogans(),
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What this means for editors',
                    'shadcn_layout' => 'media-above',
                    'subheadline' => 'Three of the slogans shaped how editors work with Desiderio.',
                    'content' => '<ul>'
                        . '<li><strong>Users are not designers.</strong> Editors choose finished elements and see a backend preview of each one, so nobody has to design a layout in the page module.</li>'
                        . '<li><strong>Details matter.</strong> Every element has a plain name and a short description in the content wizard, so editors find the right one without a manual.</li>'
                        . '<li><strong>Help doesn’t.</strong> With inline editing in the Visual Editor, editors change a text where it appears on the page and need no help page to publish.</li>'
                        . '</ul>'
                        . sprintf(
                            '<p>Slogans after Jakob Nielsen, <a href="%s">Usability Engineering</a> (1993), chapter 1.3. <a href="%s">Read the article (German)</a>.</p>',
                            self::NIELSEN_URL,
                            self::ARTICLE_URL,
                        ),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'How in-house teams publish without tickets',
                    'description' => 'Campaign pages from finished elements, with backend previews and inline editing. Your developers build the site once.',
                    'cta_text' => 'See in-house details',
                    'cta_link' => '{{page:target-groups/for-inhouse-teams}}',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
