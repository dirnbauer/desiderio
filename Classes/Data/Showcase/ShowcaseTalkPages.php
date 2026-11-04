<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Talk pages: the long cut of Kurt Dirnbauer's usability-slogans talk. The
 * home page shows the short cut and links here; the page stays out of every
 * menu. The film seeder places the long cut right after the header section,
 * so nothing goes between the two.
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
     * @return ShowcasePage
     */
    private static function usabilitySlogansPage(): array
    {
        return [
            'title' => 'Usability slogans: the full talk',
            'navTitle' => 'Usability slogans',
            'slug' => '/usability-slogans',
            'abstract' => 'Kurt Dirnbauer’s talk in full: ten usability slogans after Jakob Nielsen in 4½ minutes, presented by his AI avatar, with English captions.',
            'description' => 'The full talk by Kurt Dirnbauer: Jakob Nielsen’s ten usability slogans and what each one asks of a product team, in 4½ minutes.',
            'parentSlug' => null,
            'hideInNav' => true,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'From the founder',
                    'header' => 'Ten usability slogans, the full talk',
                    'subheadline' => 'Kurt Dirnbauer presents ten usability slogans in 4½ minutes. For each one he explains what it asks of a product team.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_featurenumbered', [
                    'header' => 'The ten slogans',
                    'subheadline' => 'In the order of the talk, each with one line from it.',
                    'items' => [
                        ['title' => 'Your best guess is not good enough', 'description' => '<p>Opinions are not evidence. Test early, observe carefully and let behaviour replace assumptions.</p>'],
                        ['title' => 'The user is always right', 'description' => '<p>Users are right about their experience. A user who fails at a task is not the problem.</p>'],
                        ['title' => 'The user is not always right', 'description' => '<p>Users reveal problems, designers create solutions.</p>'],
                        ['title' => 'Users are not designers', 'description' => '<p>Do not ask only what they want, watch what they do.</p>'],
                        ['title' => 'Designers are not users', 'description' => '<p>Designers know too much, so the product team is never the only test group.</p>'],
                        ['title' => 'Vice presidents are not users', 'description' => '<p>A loud voice in the room should not replace the quiet truth from usability testing.</p>'],
                        ['title' => 'Less is more', 'description' => '<p>Less is more does not mean empty. It means focused.</p>'],
                        ['title' => 'Details matter', 'description' => '<p>Details are not decoration. Details are usability.</p>'],
                        ['title' => 'Help doesn’t', 'description' => '<p>Help should support the experience. It should not be required to survive it.</p>'],
                        ['title' => 'Usability engineering is process', 'description' => '<p>It turns design from opinion into evidence: research, prototype, test, measure, improve, repeat.</p>'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Source',
                    'content' => '<p>Slogans after Jakob Nielsen, Usability Engineering, Academic Press 1993, chapter 1.3 “Usability Slogans”.</p><p>Presented by Kurt’s AI avatar (HeyGen).</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => self::NIELSEN_URL,
                    'link_text' => 'Nielsen’s book',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'The ten slogans in writing',
                    'description' => 'The blog post on webconsulting.at goes through the same ten slogans in German.',
                    'cta_text' => 'Article in German',
                    'cta_link' => self::ARTICLE_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
