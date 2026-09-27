<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Copy and screenshots of the /features section: one definition per feature
 * built in the lab, grouped into the categories the hub and the menu follow.
 * ShowcaseFeaturePages turns them into pages. The screenshots come from
 * Build/Scripts/capture-feature-screenshots.mjs (Build/Data/feature-screenshots.json);
 * its --rewrite option keeps the file names below current.
 *
 * @phpstan-import-type ShowcaseMedia from ShowcaseBlocks
 * @phpstan-type FeatureButton array{text: string, link: string}
 * @phpstan-type FeatureShot array{tab: string, title: string, description: string, image: ShowcaseMedia}
 * @phpstan-type FeatureDefinition array{slug: string, product: string, badge: string, title: string, navTitle: string, description: string, abstract: string, hero: array{header: string, subheadline: string, primaryButton: FeatureButton, secondaryButton: FeatureButton, image: ShowcaseMedia}, problem: array{header: string, content: string}, tour: array{header: string, subheadline: string, shots: list<FeatureShot>}, benefits: array{header: string, items: list<array{icon: string, title: string, description: string}>}, faq: array{header: string, subheadline: string, items: list<array{question: string, answer: string}>}, install: array{header: string, code: string}|null, cta: array{header: string, description: string, text: string, link: string}}
 * @phpstan-type FeatureCategory array{label: string, header: string, subheadline: string, features: list<FeatureDefinition>}
 * @phpstan-type FeatureHub array{title: string, navTitle: string, seoTitle: string, description: string, abstract: string, badge: string, header: string, subheadline: string, primaryButton: FeatureButton, secondaryButton: FeatureButton, image: ShowcaseMedia, cta: array{header: string, description: string, text: string, link: string}}
 */
final class ShowcaseFeatureDefinitions
{
    /**
     * The Features hub: the 13 tools for websites and editors.
     *
     * @return FeatureHub
     */
    public static function hub(): array
    {
        return [
            'title' => 'Features',
            'navTitle' => 'Features',
            'seoTitle' => 'Desiderio features: website tools for TYPO3 v14',
            'description' => '13 tools for TYPO3 v14 websites: design systems, editing and publishing, search and forms, sign-in, payments and a REST API, with live screenshots.',
            'abstract' => 'The website tools built in this lab, with live screenshots: design systems, editing and publishing, search and forms, sign-in, payments and APIs.',
            'badge' => '13 website tools',
            'header' => 'Tools that save your editors clicks',
            'subheadline' => 'Design systems, editing, publishing, search, forms, sign-in and payments, each shown working in this TYPO3 v14 lab.',
            'primaryButton' => ['text' => 'Start with Desiderio', 'link' => '{{page:features/desiderio}}'],
            'secondaryButton' => ['text' => 'See AI tools', 'link' => '{{page:ai}}'],
            'image' => ShowcaseHeroPhotos::for('features'),
            'cta' => [
                'header' => 'Start with the free design system',
                'description' => 'Desiderio is free under GPL-2.0. Add the other tools when a project needs them.',
                'text' => 'Get Desiderio free',
                'link' => 'https://github.com/dirnbauer/desiderio',
            ],
        ];
    }

    /**
     * The AI hub: the 10 AI and agent tools.
     *
     * @return FeatureHub
     */
    public static function aiHub(): array
    {
        return [
            'title' => 'AI',
            'navTitle' => 'AI',
            'seoTitle' => 'AI for TYPO3 v14, with people in charge',
            'description' => '10 AI tools for TYPO3 v14: an assistant that asks before it writes, model setup, AI form logic, agent skills, MCP and agent protocols.',
            'abstract' => 'The AI tools built in this lab, with live screenshots: an assistant, model setup, AI form logic, skills, MCP, agent protocols and llms.txt.',
            'badge' => '10 AI tools',
            'header' => 'AI in TYPO3, with people in charge',
            'subheadline' => 'An assistant, one model setup, AI form logic, agent skills and open agent protocols. The assistant asks before every write.',
            'primaryButton' => ['text' => 'Meet the assistant', 'link' => '{{page:ai/ai-assistant}}'],
            'secondaryButton' => ['text' => 'Read our strategy', 'link' => '{{page:typo3-v14-strategy}}'],
            'image' => ShowcaseHeroPhotos::for('ai'),
            'cta' => [
                'header' => 'Try the AI tools on your own copy',
                'description' => 'Download the demo site, add your own provider key and see every AI tool working. There is no AI fee from us.',
                'text' => 'Download the demo',
                'link' => '{{page:downloads}}',
            ],
        ];
    }

    /**
     * The website tools, in the order the Features hub and menu list them.
     *
     * @return list<FeatureCategory>
     */
    public static function categories(): array
    {
        return [
            ['label' => 'Design systems', 'header' => 'Design systems for finished pages', 'subheadline' => 'Two component libraries and a blog template set, all themed at runtime.', 'features' => [self::desiderio(), self::astryx(), self::blog()]],
            ['label' => 'Editing and publishing', 'header' => 'Editing that saves editors clicks', 'subheadline' => 'Work on the page, in the Records module, in Word files and on images, then publish in one click.', 'features' => [self::visualEditor(), self::recordsList(), self::easyWorkspace(), self::docxEditor(), self::imageWorkbench()]],
            ['label' => 'Search and forms', 'header' => 'Search and forms that fit your site', 'subheadline' => 'Styled Solr search and Powermail forms your editors build themselves.', 'features' => [self::solr(), self::powermail()]],
            ['label' => 'Sign-in, payments and APIs', 'header' => 'Sign-in, payments and APIs without custom code', 'subheadline' => 'Single sign-on for frontend and backend, a paywall for people and AI agents, and a REST API.', 'features' => [self::workos(), self::x402Paywall(), self::sgApicore()]],
        ];
    }

    /**
     * The AI tools, in the order the AI hub and menu list them.
     *
     * @return list<FeatureCategory>
     */
    public static function aiCategories(): array
    {
        return [
            ['label' => 'AI for editors', 'header' => 'AI your editors can control', 'subheadline' => 'An assistant that asks before it writes, one model setup for every extension, and forms that understand what people write.', 'features' => [self::aiAssistant(), self::nrLlmManual(), self::jev()]],
            ['label' => 'Skills and safety', 'header' => 'Reusable skills, checked before they run', 'subheadline' => 'Agent skills your team shares, and security checks for every skill.', 'features' => [self::skillflow(), self::skillspector()]],
            ['label' => 'Agents and the open web', 'header' => 'Open TYPO3 to agents, on your terms', 'subheadline' => 'MCP, a permission registry, agent protocols, visual feedback and llms.txt.', 'features' => [self::mcpServer(), self::typo3Abilities(), self::agentNexus(), self::agentation(), self::llmsTxt()]],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function desiderio(): array
    {
        return [
            'slug' => 'desiderio',
            'product' => 'Desiderio + Innesto',
            'badge' => 'Desiderio + Innesto',
            'title' => 'Desiderio + Innesto: pages without template work',
            'navTitle' => 'Design system',
            'description' => '244 ready-made content elements for TYPO3 v14.3, 15 theme presets you switch in the site settings, and Innesto for new elements from shadcn registries.',
            'abstract' => 'Desiderio gives editors 244 ready-made content elements in ten groups, themed from the site settings. Innesto turns shadcn registry components into new elements with one command.',
            'hero' => [
                'header' => 'Build finished TYPO3 pages from 244 elements',
                'subheadline' => 'Editors pick heroes, pricing tables, charts and forms from ten groups. Change the theme in the site settings, and every page follows without a rebuild.',
                'primaryButton' => ['text' => 'Get Desiderio', 'link' => 'https://github.com/dirnbauer/desiderio'],
                'secondaryButton' => ['text' => 'See every element', 'link' => '/content-types'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-desiderio-home-hero-cad71b20.webp', 'The home page, built from its own elements', 'Desiderio home page with the headline 244 ready-made content elements for TYPO3 and a preview of theme preset cards.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Every relaunch rebuilds the same sections', 'content' => '<p>You build heroes, pricing tables and footers again for each client, and editors wait for a developer to add a section. A new brand colour means CSS changes and another deployment. The budget goes into rework instead of content.</p>'],
            'tour' => [
                'header' => 'From content wizard to finished page',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Themes',
                        'title' => 'Six of the 15 theme presets, rendered live',
                        'description' => 'Each card renders the same markup in the preset it names. <strong>Colours, fonts, corners and control sizes</strong> change; the content does not.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-desiderio-theme-presets-f63d31b0.webp', 'Six of the 15 theme presets, rendered live', 'Six Desiderio preset cards in teal, blue, violet, blue, green and orange, each with buttons, badges and font details.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Chapter page',
                        'title' => 'A chapter page for each group',
                        'description' => 'Plans & Pricing shows all <strong>25 pricing elements</strong> live, in the Midnight preset set for this page tree only.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-desiderio-chapter-pricing-88d57811.webp', 'A chapter page for each group', 'Plans & Pricing chapter page in an indigo theme with the intro 25 pricing elements and three pricing plan cards.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Content wizard',
                        'title' => 'Ten Desiderio groups in the content wizard',
                        'description' => 'The wizard lists <strong>ten Desiderio groups</strong> next to the core types. Each element has its own icon and a description.',
                        'image' => ShowcaseBlocks::screenshot('feature-desiderio-content-wizard-e6136a19.webp', 'Ten Desiderio groups in the content wizard', 'TYPO3 New Page Content wizard with the Plans & Pricing group open, listing pricing elements with icons and descriptions.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Page module',
                        'title' => 'Preview cards in the page module',
                        'description' => 'Each element shows a <strong>preview card</strong> with its type, headline and key fields, so editors find content without opening forms.',
                        'image' => ShowcaseBlocks::screenshot('feature-desiderio-page-module-77441b8c.webp', 'Preview cards in the page module', 'TYPO3 page module for Hero & Landing Intros with preview cards listing each hero element\'s type, headline and fields.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Desiderio + Innesto',
                'items' => [
                    ['icon' => 'rocket', 'title' => '244 elements, ready to use', 'description' => 'Heroes, pricing tables, charts, forms and footers come finished, with English and German demo content. Install the package, enable the site sets and start building.'],
                    ['icon' => 'sparkles', 'title' => 'One setting changes the look', 'description' => 'Pick one of 15 presets in the site settings, or one per page tree. Colours, fonts, corners and icons change together.'],
                    ['icon' => 'file-text', 'title' => 'Blog, news, search and forms match', 'description' => 'Site sets restyle t3g/blog, georgringer/news, Solr and Powermail with the same components, so every page and form follows the active preset.'],
                    ['icon' => 'zap', 'title' => 'New elements in one command', 'description' => 'Innesto turns a shadcn registry component into a content element that follows every preset and dark mode. It ships with 19 finished elements.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'How much work is the setup?', 'answer' => 'Add the GitHub repositories to Composer, require the package and run extension:setup. Then enable the Desiderio site sets for your site. One command seeds a demo page tree with every element.'],
                    ['question' => 'Which TYPO3 and PHP versions does it need?', 'answer' => 'TYPO3 v14.3.6 or newer on the 14.3 LTS branch, with PHP 8.4 or 8.5. Innesto needs TYPO3 v14.3.7 and Content Blocks 2.4. Neither installs on TYPO3 v13.'],
                    ['question' => 'What does it cost?', 'answer' => 'Nothing. Desiderio and Innesto are free under GPL-2.0-or-later, with no licence key and no locked features. The bundled icon fonts keep their own licences.'],
                    ['question' => 'What happens when I switch the theme?', 'answer' => 'Nothing. Records store field values and semantic icon keys, not colours or CSS classes. A preset switch only swaps design tokens, so the same content renders in the new look.'],
                ],
            ],
            'install' => ['header' => 'Install Desiderio + Innesto', 'code' => implode("\n", ['# Desiderio and the Visual Editor enhancements it requires are not on Packagist', 'composer config repositories.desiderio vcs https://github.com/dirnbauer/desiderio.git', 'composer config repositories.visual-editor-enhancements vcs https://github.com/dirnbauer/typo3-visual-editor-enhancements.git', 'composer require webconsulting/desiderio', 'vendor/bin/typo3 extension:setup', 'vendor/bin/typo3 cache:flush', '', '# Optional: a demo page tree with every element below page 1', 'vendor/bin/typo3 desiderio:styleguide:seed --parent=1', '', '# Optional: Innesto, then graft a registry component as a new element', 'composer config repositories.innesto vcs https://github.com/dirnbauer/innesto.git', 'composer require webconsulting/innesto:^2.3', 'vendor/bin/typo3 extension:setup', 'vendor/bin/typo3 innesto:add magicui/marquee --key partner-marquee'])],
            'cta' => ['header' => 'Start your next TYPO3 site with 244 elements', 'description' => 'Install Desiderio, enable its site sets and seed the demo page tree. Then pick a preset and start building pages.', 'text' => 'Get Desiderio', 'link' => 'https://github.com/dirnbauer/desiderio'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function astryx(): array
    {
        return [
            'slug' => 'astryx',
            'product' => 'Astryx for TYPO3',
            'badge' => 'Astryx for TYPO3',
            'title' => 'Astryx for TYPO3: 25 themes, one content set',
            'navTitle' => 'Astryx design system',
            'description' => 'Meta\'s Astryx design system for TYPO3 v14: 250 server-rendered content elements and 25 themes you switch per site or per page, without React.',
            'abstract' => 'Astryx for TYPO3 brings Meta\'s open-source design system to TYPO3 as 250 content elements and 25 themes. Fluid renders everything on the server, without React.',
            'hero' => [
                'header' => 'Run Meta\'s design system on TYPO3, without React',
                'subheadline' => '250 content elements and 25 themes you switch per site or per page. Fluid renders everything on the server, with no build step after you save.',
                'primaryButton' => ['text' => 'Get Astryx', 'link' => 'https://github.com/dirnbauer/astryx-typo3'],
                'secondaryButton' => ['text' => 'See it live', 'link' => '/astryx-typo3/'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-astryx-home-7eb2ba52.webp', 'The Astryx lab site, rendered by TYPO3', 'Astryx lab start page with a team photo beside the headline Meta\'s design system, rendered by TYPO3.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Design systems usually come with a JavaScript stack', 'content' => '<p>Most current design systems ship as React components. Bringing one to TYPO3 means a Node build, a JavaScript runtime on every page and a rebuild whenever the look changes.</p>'],
            'tour' => [
                'header' => 'One set of content, 25 themes',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Themes',
                        'title' => 'Six of the 25 themes, side by side',
                        'description' => 'Each card renders the same markup in its theme: <strong>fonts, colours and corners</strong> change. Matcha even sets headings in handwriting.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-astryx-themes-ec012da4.webp', 'Six of the 25 themes, side by side', 'Six Astryx theme cards: Neutral, Butter, Chocolate, Matcha with handwritten headings, Stone and the dark Gothic theme.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Chapter page',
                        'title' => 'A pricing element in the Matcha theme',
                        'description' => 'The Plans & Pricing chapter uses <strong>Matcha</strong>, chosen in its page properties. Every element on the page follows.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-astryx-pricing-matcha-5abf1522.webp', 'A pricing element in the Matcha theme', 'Astryx featured plan section in the green Matcha theme with a handwritten headline, a price, two buttons and a rooftop photo.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Dark mode',
                        'title' => 'Every theme also works in dark mode',
                        'description' => 'Colour tokens are <strong>light and dark pairs</strong>, so the colour scheme and the theme switch independently.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-astryx-dark-e5307387.webp', 'Every theme also works in dark mode', 'The same six Astryx theme cards in dark mode, each keeping its own accent colour and fonts.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Page theme',
                        'title' => 'A theme for a page and its subpages',
                        'description' => 'The <strong>Astryx theme</strong> field overrides the site theme for this page and everything below it. Empty inherits.',
                        'image' => ShowcaseBlocks::screenshot('feature-astryx-page-theme-field-f07df322.webp', 'A theme for a page and its subpages', 'TYPO3 page properties for Plans & Pricing on the Appearance tab, with the Astryx theme field set to Matcha.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Astryx for TYPO3',
                'items' => [
                    ['icon' => 'layout-sidebar-right', 'title' => '250 elements in ten groups', 'description' => 'Heroes, pricing, data, team and footers: 25 elements per group, each with demo content in English and German and a backend preview.'],
                    ['icon' => 'sparkles', 'title' => '25 themes, switched per page', 'description' => 'Seven themes come from Meta\'s release and 18 more follow the same tokens. Set one per site, or per page and its subpages.'],
                    ['icon' => 'zap', 'title' => 'No React, no build step', 'description' => '189 Fluid components render every element on the server. Native HTML handles menus, dialogs and carousels, and one small script covers the rest.'],
                    ['icon' => 'shield-check', 'title' => 'Contrast checked in every theme', 'description' => 'A build gate measures 1,600 colour pairs against WCAG 2.2 AA, in light and dark. A palette change that fails it breaks the build.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'How much work is the setup?', 'answer' => 'Composer installs it together with the rendering engine it runs on. TYPO3\'s own setup cannot add this many columns, so run the included schema script once; it currently expects DDEV. Then add two site sets and seed the demo.'],
                    ['question' => 'Which TYPO3 and PHP versions does it need?', 'answer' => 'TYPO3 v14.3.7 or newer and PHP 8.4 or newer, with Content Blocks 2.2 or newer. The Visual Editor is optional: it adds inline editing and the element picker.'],
                    ['question' => 'Can we use it for client work?', 'answer' => 'Yes. The extension is GPL-2.0-or-later. Astryx itself is MIT-licensed by Meta, and the bundled fonts use the SIL Open Font License.'],
                    ['question' => 'Does anything load from Meta or a CDN?', 'answer' => 'No. The fonts are self-hosted, and no React or StyleX runtime ships with it. Only the design tokens and the component inventory come from Astryx release 0.6.2.'],
                ],
            ],
            'install' => ['header' => 'Install Astryx for TYPO3', 'code' => implode("\n", ['# astryx-typo3 and the packages it builds on are not on Packagist', 'composer config repositories.astryx-typo3 vcs https://github.com/dirnbauer/astryx-typo3.git', 'composer config repositories.desiderio vcs https://github.com/dirnbauer/desiderio.git', 'composer config repositories.visual-editor-enhancements vcs https://github.com/dirnbauer/typo3-visual-editor-enhancements.git', 'composer require webconsulting/astryx-typo3', '', '# extension:setup cannot add the tt_content columns once the table is this wide:', '# dry run first, then --apply (the script expects the DDEV docroot /var/www/html)', 'ddev exec php vendor/webconsulting/astryx-typo3/Build/Scripts/apply-schema.php', 'ddev exec php vendor/webconsulting/astryx-typo3/Build/Scripts/apply-schema.php --apply', '', '# Add webconsulting/astryx-typo3 and webconsulting/astryx-typo3-content-elements', '# to config/sites/<site>/config.yaml, then seed the showcase:', 'ddev exec vendor/bin/typo3 astryx-typo3:site:seed --content'])],
            'cta' => ['header' => 'See all 250 elements on the live site', 'description' => 'Browse the ten chapters and the 25 theme pages of the Astryx lab site. Then install it from GitHub.', 'text' => 'See the elements', 'link' => '/astryx-typo3/components'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function blog(): array
    {
        return [
            'slug' => 'blog',
            'product' => 'TYPO3 Blog',
            'badge' => 'TYPO3 Blog',
            'title' => 'TYPO3 Blog: blogging with the tools you know',
            'navTitle' => 'Blog',
            'description' => 'The TYPO3 Blog extension with Desiderio templates: posts are pages, 20 plugins, moderated comments, RSS feeds and a theme that follows your site.',
            'abstract' => 'The TYPO3 Blog extension turns pages into posts, so editors write with the content elements they know. Desiderio\'s templates give lists, posts, widgets and comments your site\'s theme.',
            'hero' => [
                'header' => 'Run your blog with the pages you know',
                'subheadline' => 'Posts are TYPO3 pages built from ordinary content elements. Desiderio\'s templates style lists, posts, widgets and comments in your theme, in light and dark mode.',
                'primaryButton' => ['text' => 'Get the extension', 'link' => 'https://github.com/TYPO3GmbH/blog'],
                'secondaryButton' => ['text' => 'See the blog', 'link' => '/14/'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-blog-list-43957885.webp', 'Blog – Modern: newest post first, then cards', 'The TYPO3 v14 blog in Blog – Modern: topic tabs, the newest post as a large card and the next posts below.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Blog add-ons pull editors out of TYPO3', 'content' => '<p>Many blog setups keep posts in their own tables, with their own editor and rules. Editors learn a second tool, and staging a post needs a workaround. The blog rarely looks like the rest of the site.</p>'],
            'tour' => [
                'header' => 'A blog built from pages',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Post',
                        'title' => 'Posts open with metadata and a featured image',
                        'description' => 'Blog – Classic opens a post with a <strong>date leaf</strong>, the title, the teaser and a byline with the reading time. Content elements follow.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-blog-post-6e34a9cb.webp', 'Posts open with metadata and a featured image', 'Blog post What\'s new for editors in TYPO3 v14 in Blog – Classic, with a date leaf, byline, laptop photo and a sidebar.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Comments',
                        'title' => 'Comments with moderation and spam protection',
                        'description' => 'Readers comment under the post, and editors can <strong>approve each comment</strong>. Friendly Captcha guards the form in production.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-blog-comments-4ff964de.webp', 'Comments with moderation and spam protection', 'Blog comments with an example comment and the Write comment form on a soft panel, with its spam protection box.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Page module',
                        'title' => 'Posts are pages in the page module',
                        'description' => 'Editors write posts in the <strong>page module</strong>, with the same content elements as every other page.',
                        'image' => ShowcaseBlocks::screenshot('feature-blog-page-module-7d57906f.webp', 'Posts are pages in the page module', 'TYPO3 page module showing the blog post What\'s new for editors in TYPO3 v14 with its text elements.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Posts module',
                        'title' => 'All posts of a blog in one list',
                        'description' => 'The <strong>Posts</strong> module lists each post with author, categories, tags and date, filtered per blog.',
                        'image' => ShowcaseBlocks::screenshot('feature-blog-posts-module-62a80ef2.webp', 'All posts of a blog in one list', 'TYPO3 Blog Posts module listing eight posts of The TYPO3 blog with authors, categories, tags and dates.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with TYPO3 Blog',
                'items' => [
                    ['icon' => 'file-text', 'title' => 'Posts are pages', 'description' => 'Editors create posts in the page tree and fill them with the content elements they already use. Access rights and workspaces apply as usual.'],
                    ['icon' => 'layout-sidebar-right', 'title' => '20 plugins for every blog block', 'description' => 'Post lists, archives, category, tag and author pages, related posts, sidebar widgets and RSS feeds, placed like any content element.'],
                    ['icon' => 'message-circle', 'title' => 'Comments you control', 'description' => 'Turn on moderation and each comment waits for approval; the author and an admin can get an email. Friendly Captcha guards the form if installed.'],
                    ['icon' => 'layout-grid', 'title' => 'Two templates, your theme', 'description' => 'Blog – Classic reads like a journal, Blog – Modern like a magazine. Both follow the active preset, light and dark.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'How much work is the setup?', 'answer' => 'Require t3g/blog and Desiderio with Composer, then add the blog site sets to your site. For an existing blog, one command applies the Desiderio page layouts to its page tree, with a dry run first.'],
                    ['question' => 'Which TYPO3 and PHP versions does it need?', 'answer' => 'Version 14 of the Blog extension runs on TYPO3 13.4 and 14.3 with PHP 8.2 or newer. The Desiderio templates need TYPO3 v14.3.6 or newer and PHP 8.4.'],
                    ['question' => 'What does it cost?', 'answer' => 'Nothing. The Blog extension by TYPO3 GmbH and the Desiderio templates are both free under GPL-2.0-or-later. Thank you to TYPO3 GmbH for maintaining the extension.'],
                    ['question' => 'Where do comments and reader data go?', 'answer' => 'Comments are stored in your TYPO3 database, unless you switch to Disqus. Friendly Captcha stops bots by proof of work instead of tracking, and Google reCAPTCHA stays optional.'],
                ],
            ],
            'install' => ['header' => 'Install TYPO3 Blog', 'code' => implode("\n", ['composer require t3g/blog', '', '# Desiderio and the Visual Editor enhancements it requires are not on Packagist', 'composer config repositories.desiderio vcs https://github.com/dirnbauer/desiderio.git', 'composer config repositories.visual-editor-enhancements vcs https://github.com/dirnbauer/typo3-visual-editor-enhancements.git', 'composer require webconsulting/desiderio', 'vendor/bin/typo3 extension:setup', '', '# In config/sites/<site>/config.yaml add blog/integration and webconsulting/desiderio-blog', '# (or webconsulting/desiderio-blog-standalone for a site that is only a blog), then:', 'vendor/bin/typo3 desiderio:blog:seed-pages --root=<blog-root-uid> --dry-run', 'vendor/bin/typo3 cache:flush'])],
            'cta' => ['header' => 'Start a blog your editors can run', 'description' => 'Install the Blog extension and Desiderio, add the blog sets and publish your first post from the page tree. Thank you to TYPO3 GmbH for the extension.', 'text' => 'Get the extension', 'link' => 'https://github.com/TYPO3GmbH/blog'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function visualEditor(): array
    {
        return [
            'slug' => 'visual-editor',
            'product' => 'Visual Editor Enhancements',
            'badge' => 'Visual Editor add-on',
            'title' => 'Visual Editor Enhancements: edit more on the page',
            'navTitle' => 'Visual editing',
            'description' => 'An element library with live previews and typo-tolerant search, a field chooser, link editing and a one-row text toolbar for the TYPO3 Visual Editor.',
            'abstract' => 'Visual Editor Enhancements adds what editors miss on the page: an element library with live previews, a popover for layout options and link editing. Everything saves through the Visual Editor.',
            'hero' => [
                'header' => 'Add and style content right on the page',
                'subheadline' => 'Drag finished elements from a library with live previews, change layout options in a popover and edit links in place. Everything saves through the Visual Editor.',
                'primaryButton' => ['text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-visual-editor-enhancements'],
                'secondaryButton' => ['text' => 'See the manual', 'link' => 'https://github.com/dirnbauer/typo3-visual-editor-enhancements/tree/main/Documentation'],
                'image' => ShowcaseBlocks::screenshot('feature-visual-editor-element-library-6456fd81.webp', 'Pick elements from a library with live previews', 'TYPO3 Visual Editor with the Add content panel open, showing element cards with rendered previews and keyword chips.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Editing on the page stops at the text', 'content' => '<p>The Visual Editor lets editors type straight into the page. Adding an element still means a list without previews, and layout options, links and colours live in the backend form. Editors switch screens for every small change.</p>'],
            'tour' => [
                'header' => 'More editing, right on the page',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Search',
                        'title' => 'Search that forgives typos',
                        'description' => 'A typo like <strong>testimonal</strong> still finds the testimonial elements and offers a \'Did you mean\' link.',
                        'image' => ShowcaseBlocks::screenshot('feature-visual-editor-search-4e3a64e9.webp', 'Search that forgives typos', 'Element library search for the misspelt word testimonal, listing testimonial elements and a Did you mean suggestion.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Field settings',
                        'title' => 'Change layout options without the backend form',
                        'description' => 'The <strong>Field settings</strong> popover lists the element\'s choice fields in the form\'s tabs. Changes wait for the next save.',
                        'image' => ShowcaseBlocks::screenshot('feature-visual-editor-field-chooser-9a6feecb.webp', 'Change layout options without the backend form', 'Visual Editor with the Field settings popover open on a hero, showing background and spacing options in tabs.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Rich text',
                        'title' => 'A rich-text toolbar that stays on screen',
                        'description' => 'The toolbar sits in <strong>one row</strong> above the text; buttons that do not fit move into \'Show more items\'.',
                        'image' => ShowcaseBlocks::screenshot('feature-visual-editor-rte-toolbar-78c188cd.webp', 'A rich-text toolbar that stays on screen', 'Visual Editor with a one-row text toolbar above a card\'s description and the Show more items menu open.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Links',
                        'title' => 'Edit a button\'s link where it sits',
                        'description' => 'Hover a button and a <strong>chain icon</strong> opens the TYPO3 link browser for its link field.',
                        'image' => ShowcaseBlocks::screenshot('feature-visual-editor-link-helper-091fe426.webp', 'Edit a button\'s link where it sits', 'Hero section in the Visual Editor with a chain icon next to the Get started free button for editing its link.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Visual Editor Enhancements',
                'items' => [
                    ['icon' => 'monitor', 'title' => 'See elements before you add them', 'description' => 'Each card in the library is a rendered preview with demo content. Dropped elements arrive pre-filled, so a new section never starts empty.'],
                    ['icon' => 'search', 'title' => 'Search that forgives typos', 'description' => 'Type \'testimonal\' and the library still finds testimonials, with a \'Did you mean\' link. Elements you used recently stay at the top.'],
                    ['icon' => 'settings', 'title' => 'Layout options next to the content', 'description' => 'Selects, categories, links, checkboxes and colours open in a popover, grouped like the backend form. Nothing is written until you save.'],
                    ['icon' => 'shield-check', 'title' => 'Built on the Visual Editor', 'description' => 'Saving, permissions and workspaces stay with the Visual Editor. Each runtime patch checks for an upstream fix and switches itself off when one ships.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'Does it need a special theme?', 'answer' => 'Not for the field chooser, link editing, rich-text toolbar and toolbar switches: they work on any Visual Editor site. The element library also needs a catalogue provider that lists your elements and renders their previews.'],
                    ['question' => 'Which versions does it support?', 'answer' => 'TYPO3 v14.3.7 or newer and PHP 8.4 or newer, with Visual Editor 1.10.2 or newer. Each release is checked against a named Visual Editor version, currently 1.10.3.'],
                    ['question' => 'What does it cost to run?', 'answer' => 'It is free under GPL-2.0-or-later and ships through Composer and Git. Every feature is on by default and can be switched off per installation, per user or per page tree.'],
                    ['question' => 'Can it change content without an editor noticing?', 'answer' => 'No. Field changes wait in the Visual Editor’s pending list until you save, and a dropped element is written by the DataHandler with your permissions. Its endpoints need a backend login and a request token.'],
                ],
            ],
            'install' => ['header' => 'Install Visual Editor Enhancements', 'code' => implode("\n", ['# Not on Packagist: add the Git repository first', 'composer config repositories.visual-editor-enhancements vcs https://github.com/dirnbauer/typo3-visual-editor-enhancements.git', 'composer require webconsulting/visual-editor-enhancements', 'vendor/bin/typo3 cache:flush'])],
            'cta' => ['header' => 'Give editors more to do on the page', 'description' => 'Install the extension and flush the caches. The field settings and link buttons appear in Content > Editor straight away.', 'text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-visual-editor-enhancements'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function recordsList(): array
    {
        return [
            'slug' => 'records-list',
            'product' => 'Records List Types',
            'badge' => 'Records List Types',
            'title' => 'Records List Types: records at a glance',
            'navTitle' => 'Record views',
            'description' => 'Grid, compact, teaser and custom views for the TYPO3 v14 Records module. Editors find records faster, with filters, sorting and draft states.',
            'abstract' => 'Records List Types shows TYPO3 records as photo cards, dense tables, teasers or your own layouts. Editors pick the view that suits each table.',
            'hero' => [
                'header' => 'Find any record faster with the right view',
                'subheadline' => 'Show news as photo cards, contacts as a dense table and events on a timeline. Editors switch views in the Records module, and you add new ones in TSconfig.',
                'primaryButton' => ['text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/typo3-records-list-types'],
                'secondaryButton' => ['text' => 'See the examples', 'link' => 'https://github.com/dirnbauer/typo3-records-list-examples'],
                'image' => ShowcaseBlocks::screenshot('feature-records-list-grid-a479f78b.webp', 'Grid view: news as photo cards', 'Records module grid view: news cards with Easter-egg photos, German titles, dates and language flags.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'One table does not fit every record', 'content' => '<p>The Records module shows news, contacts and products as the same rows of text. Editors scroll sideways to find the article with the right photo. Hidden records and drafts look almost like every other row.</p>'],
            'tour' => [
                'header' => 'Five views of the same records',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'List',
                        'title' => 'The core list, with the columns you choose',
                        'description' => 'The standard <strong>List view</strong> stays one click away, with your chosen columns and every core record action.',
                        'image' => ShowcaseBlocks::screenshot('feature-records-list-list-e4a0e6b9.webp', 'The core list, with the columns you choose', 'TYPO3 Records module in list view: German news records with date, teaser and top-news columns.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Compact',
                        'title' => 'Compact view: long lists on one screen',
                        'description' => '<strong>Compact view</strong> keeps icon, ID and title fixed while the other columns scroll, with sortable column headers.',
                        'image' => ShowcaseBlocks::screenshot('feature-records-list-compact-9beea1fe.webp', 'Compact view: long lists on one screen', 'Records module compact view: a dense table of news with fixed title column and sortable headers.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Teaser',
                        'title' => 'Teaser view: title, date and summary together',
                        'description' => '<strong>Teaser view</strong> reads like a news list: title, date and a two-line excerpt for every record.',
                        'image' => ShowcaseBlocks::screenshot('feature-records-list-teaser-9508caa6.webp', 'Teaser view: title, date and summary together', 'Records module teaser view: news cards with title, date and a short excerpt for each record.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Catalogue',
                        'title' => 'Catalogue view: a custom layout from TSconfig',
                        'description' => '<strong>Catalogue</strong> is one of six example views: large image cards, built from TSconfig, a Fluid template and a stylesheet.',
                        'image' => ShowcaseBlocks::screenshot('feature-records-list-catalog-9c116153.webp', 'Catalogue view: a custom layout from TSconfig', 'Records module catalogue view: large photo cards with title and teaser for each news record.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Records List Types',
                'items' => [
                    ['icon' => 'monitor', 'title' => 'The right view for each table', 'description' => 'Photo cards for news, dense rows for long lists, teasers for articles. Each editor\'s choice is remembered for the next visit.'],
                    ['icon' => 'settings', 'title' => 'New views without PHP', 'description' => 'Register a view in Page TSconfig, then reuse a built-in template or add your own Fluid template. Six example views show how.'],
                    ['icon' => 'search', 'title' => 'Filters editors can use', 'description' => 'Filter by title, date range, visibility, category or any select field. Every table gets defaults, and TSconfig adds your own.'],
                    ['icon' => 'history', 'title' => 'Drafts stay visible', 'description' => 'In a workspace, every view shows draft rows with state markers. Search and filters also find text that only exists in a draft.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'How much work is the installation?', 'answer' => 'Two Composer packages and an extension:setup run. The views appear in the View menu of the Records module straight away. TSconfig only changes the defaults, the allowed views and which fields become title, text and image.'],
                    ['question' => 'Which TYPO3 and PHP versions does it need?', 'answer' => 'TYPO3 v14.3.7 or newer on the v14 line, PHP 8.4 or 8.5 and a Composer installation. Install Records List Types and Records List Examples in the same release, because they ship together.'],
                    ['question' => 'What does it cost?', 'answer' => 'Nothing. Both packages are open source under GPL-2.0-or-later. Records List Types ships English and German labels, plus French, Spanish and Italian drafts.'],
                    ['question' => 'Does it change records or permissions?', 'answer' => 'No. Every view reads through the same record pipeline as the core list, so permissions, translations, workspaces and record actions behave as before. It only stores each editor\'s view and filter settings.'],
                ],
            ],
            'install' => ['header' => 'Install Records List Types', 'code' => implode("\n", ['composer config repositories.records-list-types vcs https://github.com/dirnbauer/typo3-records-list-types.git', 'composer config repositories.records-list-examples vcs https://github.com/dirnbauer/typo3-records-list-examples.git', 'composer require webconsulting/records-list-types:^2.0 webconsulting/records-list-examples:^2.0', 'vendor/bin/typo3 extension:setup -e records_list_types', 'vendor/bin/typo3 extension:setup -e records_list_examples', 'vendor/bin/typo3 cache:flush'])],
            'cta' => ['header' => 'Give every table the view it needs', 'description' => 'Install both packages, open the Records module and pick a view from the View menu. Add your own views in TSconfig when you need them.', 'text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/typo3-records-list-types'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function easyWorkspace(): array
    {
        return [
            'slug' => 'easy-workspace',
            'product' => 'Easy Workspace',
            'badge' => 'Easy Workspace',
            'title' => 'Easy Workspace: review and publish in one click',
            'navTitle' => 'One-click publishing',
            'description' => 'Publish TYPO3 workspace changes from the toolbar: see what changed on the page in every language, pick what goes live and publish in one click.',
            'abstract' => 'Easy Workspace adds a publishing dropdown to the TYPO3 toolbar. Editors see the pending changes of the page they are on and publish them in one click.',
            'hero' => [
                'header' => 'Review and publish a page in one click',
                'subheadline' => 'A toolbar dropdown lists the pending changes of the page you are on, in every language. Tick what is ready and publish it with its related records.',
                'primaryButton' => ['text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/typo3-webcon-easy-workspace'],
                'secondaryButton' => ['text' => 'See the manual', 'link' => 'https://github.com/dirnbauer/typo3-webcon-easy-workspace/tree/main/Documentation'],
                'image' => ShowcaseBlocks::screenshot('feature-easy-workspace-dropdown-aa5e4b9e.webp', 'Everything pending on this page, in one list', 'Easy Workspace dropdown listing five pending changes on the People & Team page with New and Changed badges.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Publishing should not need an expert', 'content' => '<p>The Workspaces module lists changes for whole page trees, not for the page you are on. Editors hunt for their page, guess which records belong to it and leave publishing to an admin. Other languages get missed.</p>'],
            'tour' => [
                'header' => 'Review and publish from the top bar',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Badge',
                        'title' => 'The toolbar counts pending changes per page',
                        'description' => 'The <strong>Workspace publish</strong> icon shows how many changes wait on the page you are on, not in the whole site.',
                        'image' => ShowcaseBlocks::screenshot('feature-easy-workspace-toolbar-badge-9d4a0106.webp', 'The toolbar counts pending changes per page', 'TYPO3 top bar with the Workspace publish icon and a yellow badge showing 5 pending changes.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Languages',
                        'title' => 'Changes in other languages are never left behind',
                        'description' => 'Rows the current <strong>language view</strong> hides get their own section. They stay selected and publish with the rest.',
                        'image' => ShowcaseBlocks::screenshot('feature-easy-workspace-languages-3e1507a3.webp', 'Changes in other languages are never left behind', 'Dropdown with two German changes in view and three English changes under an Other languages heading.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Selection',
                        'title' => 'Publish what is ready, keep the rest staged',
                        'description' => 'Every change starts <strong>selected</strong>. Untick what is not ready, and the button says exactly how many records go live.',
                        'image' => ShowcaseBlocks::screenshot('feature-easy-workspace-selection-5f8a9766.webp', 'Publish what is ready, keep the rest staged', 'Dropdown with two of five changes selected and a Publish 2 button in the footer.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Published',
                        'title' => 'Published to live, the rest waits for later',
                        'description' => 'After <strong>Publish</strong>, TYPO3 confirms the result, the badge updates and the unselected changes stay in the workspace.',
                        'image' => ShowcaseBlocks::screenshot('feature-easy-workspace-published-824cc66a.webp', 'Published to live, the rest waits for later', 'TYPO3 notification \'Published to live, 6 items published\' next to the dropdown, where three changes stay in Staging.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Easy Workspace',
                'items' => [
                    ['icon' => 'send', 'title' => 'Publish from where you work', 'description' => 'The dropdown sits in the backend\'s top bar. Editors review and publish the page they are on without opening the Workspaces module.'],
                    ['icon' => 'globe', 'title' => 'No translation left behind', 'description' => 'Changes the current language view hides are listed under their own language. They stay selected and publish with the rest.'],
                    ['icon' => 'check-circle', 'title' => 'Choose what goes live', 'description' => 'Every change starts selected. Untick what isn\'t ready, check the diff and history, or discard one draft, then publish the rest.'],
                    ['icon' => 'shield-check', 'title' => 'Runs on TYPO3 core', 'description' => 'The list comes from the same core code as the Workspaces module. Publishing and discarding go through the DataHandler.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'How long does installation take?', 'answer' => 'Add the GitHub repository, require the package and run extension:setup. The dropdown appears for every editor who can use a workspace, and TSconfig switches single features off per user or group.'],
                    ['question' => 'Which TYPO3 and PHP versions does it need?', 'answer' => 'TYPO3 v14.3.6 or newer on the 14.3 LTS line and PHP 8.4 or 8.5, with the core Workspaces extension. EXT:news and the Visual Editor are optional.'],
                    ['question' => 'What does it cost?', 'answer' => 'Nothing. Easy Workspace is open source under GPL-2.0-or-later and is released as tags on GitHub.'],
                    ['question' => 'Does it change how TYPO3 publishes?', 'answer' => 'No. It reads the same data as the core Workspaces module and publishes through the DataHandler, so permissions, stages and history stay as they are. The dropdown is hidden in Live.'],
                ],
            ],
            'install' => ['header' => 'Install Easy Workspace', 'code' => implode("\n", ['composer config repositories.webcon-easy-workspace vcs https://github.com/dirnbauer/typo3-webcon-easy-workspace.git', 'composer require webconsulting/webcon-easy-workspace:^1.9', 'vendor/bin/typo3 extension:setup', 'vendor/bin/typo3 cache:flush'])],
            'cta' => ['header' => 'Let editors publish without a detour', 'description' => 'Install Easy Workspace, switch to a workspace and open a page. The toolbar shows what is pending and publishes it in one click.', 'text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/typo3-webcon-easy-workspace'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function docxEditor(): array
    {
        return [
            'slug' => 'docx-editor',
            'product' => 'DOCX Editor',
            'badge' => 'DOCX Editor',
            'title' => 'DOCX Editor: edit Word files inside TYPO3',
            'navTitle' => 'Word documents',
            'description' => 'Edit .docx files from the TYPO3 v14 file list with Word\'s layout. Send whole pages to Word and back, with a review before anything is saved.',
            'abstract' => 'DOCX Editor opens Word files from the file list in a full-page editor and saves them back to the same file. It also turns a page into a Word document and imports the edits after a review.',
            'hero' => [
                'header' => 'Edit Word documents without leaving TYPO3',
                'subheadline' => 'Open a .docx from the file list, edit it with Word\'s layout and save it back. Or edit a whole page in Word and review each change before import.',
                'primaryButton' => ['text' => 'Get DOCX Editor', 'link' => 'https://github.com/dirnbauer/typo3-docx'],
                'secondaryButton' => ['text' => 'See the manual', 'link' => 'https://github.com/dirnbauer/typo3-docx/tree/main/Documentation'],
                'image' => ShowcaseBlocks::screenshot('feature-docx-editor-word-menu-8e5a83a1.webp', 'A Word menu on every page', 'TYPO3 page module with the Word menu open, offering Edit in Word, Download as Word document and import as subpages.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Word files and web pages live apart', 'content' => '<p>To fix one line in a Word file, you download it, edit it and upload it again, hoping nobody changed it meanwhile. Page text travels between Word and TYPO3 by copy and paste, and the formatting breaks.</p>'],
            'tour' => [
                'header' => 'Word and TYPO3, in both directions',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Word view',
                        'title' => 'The page as a Word document',
                        'description' => 'Each content element becomes a <strong>framed block</strong>; each field is a frame inside it, labelled while you edit it.',
                        'image' => ShowcaseBlocks::screenshot('feature-docx-editor-page-in-word-dbfe56d4.webp', 'The page as a Word document', 'A TYPO3 page open as a Word document in the backend, with framed blocks for each text element and its fields.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Review',
                        'title' => 'Review every change before it reaches the page',
                        'description' => 'Before an import, a review lists each element as <strong>new, changed, conflicting or removed</strong>. Nothing is written until you confirm.',
                        'image' => ShowcaseBlocks::screenshot('feature-docx-editor-review-cb15cece.webp', 'Review every change before it reaches the page', 'Review changes before saving dialog listing a changed text element, its field marked changed in Word and an Import button.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Import',
                        'title' => 'Turn a Word document into new pages',
                        'description' => 'Split a document into <strong>one page, a page per Heading 1 or per page break</strong>. New pages start hidden for review.',
                        'image' => ShowcaseBlocks::screenshot('feature-docx-editor-import-pages-a26fa4f1.webp', 'Turn a Word document into new pages', 'TYPO3 form to import a Word document as subpages, with a file field and three options for splitting it into pages.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'File editor',
                        'title' => 'Edit .docx files from the file list',
                        'description' => '<strong>Edit DOCX</strong> opens the file full-page with Word\'s layout; Save writes it back to the same file.',
                        'image' => ShowcaseBlocks::screenshot('feature-docx-editor-file-editor-d97eb528.webp', 'Edit .docx files from the file list', 'DOCX Editor showing a Word document with a ruler and heading buttons, opened from the TYPO3 file list.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with DOCX Editor',
                'items' => [
                    ['icon' => 'file-word', 'title' => 'Edit .docx files where they live', 'description' => 'Open a Word file from the file list, edit it full-page and save it back. Save as makes a copy elsewhere.'],
                    ['icon' => 'file-text', 'title' => 'Word\'s layout, kept intact', 'description' => 'Pages, headers, tables and images look as they do in Word. Tracked changes, comments and content controls survive a save, even when not shown.'],
                    ['icon' => 'history', 'title' => 'Pages round-trip through Word', 'description' => 'Send a page to Word, edit it there or in the browser, and bring it back. A review lists every new, changed and conflicting element.'],
                    ['icon' => 'users', 'title' => 'No overwritten work', 'description' => 'A badge shows how many editors have the document open. If someone saved a newer version, your save stops and offers a reload instead.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'What do I need to install?', 'answer' => 'Composer, the PHP extensions dom, libxml and zip, and a current browser with WebAssembly. The editor bundle is committed, so you need no Node.js. Running extension:setup creates two small tables.'],
                    ['question' => 'Which TYPO3 and PHP versions does it support?', 'answer' => 'TYPO3 v14.3 LTS from 14.3.7 on, with PHP 8.4 or 8.5; CI tests both. Current Chrome, Edge, Firefox and Safari run the editor.'],
                    ['question' => 'Do I need a docx-editor.dev licence?', 'answer' => 'No. The extension is GPL-2.0-or-later and uses only the Apache-2.0 packages of docx-editor.dev. The paid Pro packages are not installed, and the editor shows no trace of them.'],
                    ['question' => 'Is it safe to import documents from outside?', 'answer' => 'Documents are read with XML entities refused, network access off and size limits against zip bombs. An import runs through the DataHandler with your permissions, workspace and language, and only after your review.'],
                ],
            ],
            'install' => ['header' => 'Install DOCX Editor', 'code' => implode("\n", ['# Not on Packagist: add the Git repository first', 'composer config repositories.docx-editor vcs https://github.com/dirnbauer/typo3-docx.git', 'composer require webconsulting/docx-editor:^2.2', 'vendor/bin/typo3 extension:setup'])],
            'cta' => ['header' => 'Edit Word files where they are stored', 'description' => 'Install DOCX Editor and run extension:setup. Edit DOCX appears in the file list, and every page gets a Word menu.', 'text' => 'Get DOCX Editor', 'link' => 'https://github.com/dirnbauer/typo3-docx'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function imageWorkbench(): array
    {
        return [
            'slug' => 'image-workbench',
            'product' => 'Image Workbench',
            'badge' => 'Image Workbench',
            'title' => 'Image Workbench: crop and create images in TYPO3',
            'navTitle' => 'Image editing',
            'description' => 'Crop, adjust, filter and annotate JPEG, PNG and WebP images in the TYPO3 v14 file list, and generate new images from a prompt through nr-llm.',
            'abstract' => 'Image Workbench opens JPEG, PNG and WebP files from the file list in a full-page editor and saves a copy next to the original. A side panel creates new images from a prompt through nr-llm.',
            'hero' => [
                'header' => 'Crop, adjust and generate images inside TYPO3',
                'subheadline' => 'Edit JPEG, PNG and WebP files from the file list and keep the original. Describe a new image, and it arrives as a PNG in the same folder.',
                'primaryButton' => ['text' => 'Get Image Workbench', 'link' => 'https://github.com/dirnbauer/typo3-image-workbench'],
                'secondaryButton' => ['text' => 'See the manual', 'link' => 'https://github.com/dirnbauer/typo3-image-workbench/tree/main/Documentation'],
                'image' => ShowcaseBlocks::screenshot('feature-image-workbench-file-list-7d739b89.webp', 'Edit image, right from the file list', 'TYPO3 file list with photo thumbnails and an open context menu that offers Edit image for cafe-morning.jpg.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'A small crop turns into a detour', 'content' => '<p>Editors download the image, open a desktop tool, crop it and upload it again. The old file may still be in use, and nobody knows which version is current. A new visual means waiting for a designer.</p>'],
            'tour' => [
                'header' => 'Crop, adjust, generate, save',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Editor',
                        'title' => 'A full-page editor with filters, crop and resize',
                        'description' => 'Tabs to <strong>adjust, fine-tune, filter, annotate and resize</strong> sit beside the photo. The original stays untouched until you save.',
                        'image' => ShowcaseBlocks::screenshot('feature-image-workbench-editor-dfb370fb.webp', 'A full-page editor with filters, crop and resize', 'Image Workbench editing a café photo with the Filters tab open and Save as copy and Overwrite original buttons.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'AI panel',
                        'title' => 'Describe a new image, get a PNG',
                        'description' => 'The prompt goes through <strong>nr-llm</strong>, with your model, budget and cost tracking. The photo itself is never sent.',
                        'image' => ShowcaseBlocks::screenshot('feature-image-workbench-ai-panel-c9e705fb.webp', 'Describe a new image, get a PNG', 'Generate with AI panel with a prompt about a café counter, a size menu set to landscape and a Generate image button.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Save copy',
                        'title' => 'Save a copy and keep the original',
                        'description' => '<strong>Save as copy</strong> writes a new file next to the original. An existing file is never replaced; a number is added.',
                        'image' => ShowcaseBlocks::screenshot('feature-image-workbench-save-copy-6396bd39.webp', 'Save a copy and keep the original', 'Save as copy dialog with a file name field and the note that an existing file is never replaced.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Image Workbench',
                'items' => [
                    ['icon' => 'file-image', 'title' => 'Edit without leaving TYPO3', 'description' => 'Crop, rotate, adjust, filter, annotate and resize in a full-page editor opened from the file list. No download, no desktop tool, no re-upload.'],
                    ['icon' => 'shield-check', 'title' => 'The original stays safe', 'description' => 'Save as copy is the default and never replaces a file. Overwriting asks first and refreshes every thumbnail and crop of the image.'],
                    ['icon' => 'sparkles', 'title' => 'New images from a prompt', 'description' => 'Describe the image, pick a size the model supports, and a new PNG lands next to the source. Generation never overwrites a file.'],
                    ['icon' => 'lock', 'title' => 'AI costs under central control', 'description' => 'nr-llm holds the API key, the model and each user\'s budget, and books each image to the user. Only the prompt leaves the server.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'What does the install involve?', 'answer' => 'Require the package with Composer and run extension:setup. There are no database tables and no site set. For AI images, store the provider key in nr-llm and set up an image configuration.'],
                    ['question' => 'Which versions does it support?', 'answer' => 'TYPO3 v14.3 LTS and PHP 8.4 or newer with the GD extension. AI generation uses nr-llm 0.34 or 0.35. It has no compatibility layers for older TYPO3 versions.'],
                    ['question' => 'What does it cost?', 'answer' => 'The extension is free under GPL-2.0-or-later, and the bundled Filerobot Image Editor is open source. Generated images cost what your provider charges, and nr-llm books the spend per user.'],
                    ['question' => 'Is my image sent to an AI provider?', 'answer' => 'No. Only the prompt is sent; the image you edit stays on your server. Every read and write goes through TYPO3\'s file permissions, and the editor calls no third-party service.'],
                ],
            ],
            'install' => ['header' => 'Install Image Workbench', 'code' => implode("\n", ['# Not on Packagist: add the Git repository first', 'composer config repositories.image-workbench vcs https://github.com/dirnbauer/typo3-image-workbench.git', 'composer require webconsulting/image-workbench', 'vendor/bin/typo3 extension:setup'])],
            'cta' => ['header' => 'Let editors fix images where they find them', 'description' => 'Install Image Workbench and open the file list: Edit image is ready for JPEG, PNG and WebP files. Add an nr-llm image configuration to switch on generation.', 'text' => 'Get Image Workbench', 'link' => 'https://github.com/dirnbauer/typo3-image-workbench'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function solr(): array
    {
        return [
            'slug' => 'solr',
            'product' => 'Apache Solr for TYPO3',
            'badge' => 'Apache Solr',
            'title' => 'Apache Solr: search results in your design',
            'navTitle' => 'Site search',
            'description' => 'Apache Solr search for TYPO3 v14 with ready templates: results, filters, sorting and page numbers follow your theme in light and dark mode.',
            'abstract' => 'Apache Solr finds your content fast, and Desiderio\'s templates make the results page match the rest of your site, from filters to page numbers.',
            'hero' => [
                'header' => 'Search results that look like your site',
                'subheadline' => 'Solr finds your pages and news. Desiderio\'s templates style results, filters and page numbers with your theme, so search needs no design work.',
                'primaryButton' => ['text' => 'Get the templates', 'link' => 'https://github.com/dirnbauer/desiderio'],
                'secondaryButton' => ['text' => 'Try a search', 'link' => '/search/?tx_solr%5Bq%5D=Desiderio'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-solr-results-facets-38390880.webp', 'Results, filters and page numbers in your theme', 'Search results page for \'Desiderio\' with 53 results, content-type filters, sorting and numbered pagination.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Good search, off-brand results', 'content' => '<p>Solr\'s default templates don\'t match your design. Someone restyles results, filters and pagination on every project, and again after each redesign. Until then, visitors land on a search page that looks like another site.</p>'],
            'tour' => [
                'header' => 'Search that looks like your site',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Filters',
                        'title' => 'One click narrows the results to news',
                        'description' => 'Active filters show as <strong>removable chips</strong>, and every option keeps its own count while you filter.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-solr-active-filter-96f86078.webp', 'One click narrows the results to news', 'Search results narrowed to 12 news articles, with an active \'Content type: News\' filter chip.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Dark mode',
                        'title' => 'The same results page in dark mode',
                        'description' => 'Search follows the <strong>theme preset</strong> and the colour scheme, with no CSS of its own to maintain.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-solr-dark-mode-bb33b7c3.webp', 'The same results page in dark mode', 'The search results page for \'Desiderio\' in dark mode, with filters, result cards and page numbers.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Suggestions',
                        'title' => 'Suggestions and top results while you type',
                        'description' => 'After three letters, <strong>suggestions</strong> and the best matching pages appear, fully usable with the keyboard.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-solr-suggest-1b23a8d6.webp', 'Suggestions and top results while you type', 'Search field with the letters \'them\' and a dropdown of suggested terms and top results.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Apache Solr for TYPO3',
                'items' => [
                    ['icon' => 'sparkles', 'title' => 'Styled from the first search', 'description' => 'Results, filters, sorting, page size and page numbers use your theme tokens. Point your results page at the templates and search looks finished.'],
                    ['icon' => 'moon', 'title' => 'Follows every theme change', 'description' => 'Switch the theme preset or turn on dark mode, and the results page changes with it. No extra CSS is needed.'],
                    ['icon' => 'search', 'title' => 'Filters people understand', 'description' => 'Each filter is a group of checkboxes with result counts. A second option widens the results, and every option keeps its own count.'],
                    ['icon' => 'check-circle', 'title' => 'Accessible markup', 'description' => 'Page numbers sit in a labelled navigation with the current page marked. Filters and remove buttons carry screen-reader text.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'What do I need besides TYPO3?', 'answer' => 'A Solr server with the configsets of Apache Solr for TYPO3, the extension itself and Desiderio for the templates. The lab runs version 14.0.2 of Apache Solr for TYPO3 against the Solr service of DDEV.'],
                    ['question' => 'Which TYPO3 and PHP versions?', 'answer' => 'The lab runs TYPO3 v14.3 on PHP 8.4 with version 14.0.2 of Apache Solr for TYPO3. Numbered page links come from a fork of solr_numbered_pagination with a branch for TYPO3 v14.'],
                    ['question' => 'What does it cost?', 'answer' => 'The software is free: Apache Solr for TYPO3 under GPL-3.0-or-later, Desiderio under GPL-2.0-or-later. You run or rent the Solr server yourself.'],
                    ['question' => 'Where do search queries go?', 'answer' => 'To your own Solr server, not to a third-party search service. Suggestions come from the same index through a JSON page type of your own site.'],
                ],
            ],
            'install' => ['header' => 'Install Apache Solr for TYPO3', 'code' => implode("\n", ['composer config repositories.desiderio vcs https://github.com/dirnbauer/desiderio.git', 'composer config repositories.solr-numbered-pagination vcs https://github.com/dirnbauer/solr_numbered_pagination.git', 'composer require apache-solr-for-typo3/solr:^14.0 webconsulting/desiderio studiomitte/solr-numbered-pagination:dev-main', 'vendor/bin/typo3 extension:setup', '# In config/sites/<site>/config.yaml: add webconsulting/solr-defaults to dependencies,', '# add the Solr connection (solr_host_read, solr_core_read, ...) and set', '# desiderio.search.targetPageId to your results page. Then fill the index queue.'])],
            'cta' => ['header' => 'Make search part of your design', 'description' => 'Install Apache Solr for TYPO3 and Desiderio, add the Solr set to your site and index your pages. Results, filters and page numbers arrive styled.', 'text' => 'Get the templates', 'link' => 'https://github.com/dirnbauer/desiderio'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function powermail(): array
    {
        return [
            'slug' => 'powermail',
            'product' => 'Powermail',
            'badge' => 'Powermail',
            'title' => 'Powermail: forms your editors build themselves',
            'navTitle' => 'Forms',
            'description' => 'Powermail forms for TYPO3 v14 in your theme: multi-step wizards, inline validation and Friendly Captcha, built by editors in the backend.',
            'abstract' => 'Powermail lets editors build contact, booking and multi-step forms in the TYPO3 backend. Desiderio styles every field with your theme, and Friendly Captcha stops bots.',
            'hero' => [
                'header' => 'Forms your editors build without a developer',
                'subheadline' => 'Editors set up fields, steps and receivers in the TYPO3 backend. Every field follows your theme, and Friendly Captcha keeps bots out.',
                'primaryButton' => ['text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/powermail'],
                'secondaryButton' => ['text' => 'Try the forms', 'link' => '/desiderio-powermail-lab'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-powermail-multistep-c566b100.webp', 'A four-step project request with a step indicator', 'Project request form, step one of four, with fields for name, email, phone, company and role.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Forms are where leads get lost', 'content' => '<p>Long forms put people off, and styling every field by hand takes days. Spam fills the inbox, and many captchas make real visitors solve puzzles.</p>'],
            'tour' => [
                'header' => 'Forms editors build and visitors finish',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Validation',
                        'title' => 'Each error shows at its field',
                        'description' => 'Required fields are checked in the <strong>browser</strong> first, and the server checks everything again when the form is sent.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-powermail-validation-6627fca3.webp', 'Each error shows at its field', 'Project request form with empty required fields outlined in red, each with the note \'Please complete this field.\'', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Final step',
                        'title' => 'Consent and spam protection on the last step',
                        'description' => 'The last step collects <strong>consent</strong>. Friendly Captcha protects it, and development sites skip the check.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-powermail-final-step-9ef1ac9c.webp', 'Consent and spam protection on the last step', 'Final step of the project request with source question, privacy consent, spam protection and send button.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Dark mode',
                        'title' => 'The same fields in dark mode',
                        'description' => 'Every field type uses the <strong>theme tokens</strong>, so forms follow the preset in light and dark mode.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-powermail-dark-mode-7effb58c.webp', 'The same fields in dark mode', 'Powermail contact form in dark mode with name, email, phone, team size, topic and message fields.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Backend',
                        'title' => 'Editors manage every form in the backend',
                        'description' => 'The <strong>form overview</strong> lists each form with the pages that use it, so editors keep track without code.',
                        'image' => ShowcaseBlocks::screenshot('feature-powermail-form-overview-7d67b20d.webp', 'Editors manage every form in the backend', 'TYPO3 backend Powermail form overview listing the demo forms of the Powermail Lab.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Powermail',
                'items' => [
                    ['icon' => 'users', 'title' => 'Editors build the forms', 'description' => 'Pages, fields, validation and receivers are set in the backend. Nobody edits a template to add a field or a step.'],
                    ['icon' => 'arrow-right', 'title' => 'Long forms in short steps', 'description' => 'Split a form into steps with a step indicator. The browser checks each step before the next opens, and the server checks everything again.'],
                    ['icon' => 'shield-check', 'title' => 'Spam protection that respects visitors', 'description' => 'Friendly Captcha is a GDPR-compliant captcha service. On development sites the check is bypassed, so testing stays quick.'],
                    ['icon' => 'sparkles', 'title' => 'Every field in your theme', 'description' => 'Desiderio ships 19 field partials, one per Powermail field type. A new theme preset restyles fields, steps and messages in light and dark mode.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'Does Powermail run on TYPO3 v14?', 'answer' => 'Yes, through a maintained fork. Because in2code has no TYPO3 v14 release yet, the fork ports the upstream 13 line to TYPO3 v14.3.6 or newer. It runs on PHP 8.3 to 8.5, and the package name stays in2code/powermail.'],
                    ['question' => 'Do we need Friendly Captcha keys for testing?', 'answer' => 'Not locally. In the Development context, for example on DDEV, the check is bypassed and a placeholder replaces the widget. In Production the real widget and check always run.'],
                    ['question' => 'What does it cost?', 'answer' => 'Powermail, the forks and the Desiderio templates are free under GPL-2.0-or-later. Friendly Captcha is an external service with its own terms.'],
                    ['question' => 'Where do submissions go?', 'answer' => 'Into your own TYPO3 database. Editors see them in the Powermail backend module and export them as CSV or Excel files.'],
                ],
            ],
            'install' => ['header' => 'Install Powermail', 'code' => implode("\n", ['composer config repositories.powermail vcs https://github.com/dirnbauer/powermail.git', 'composer config repositories.friendlycaptcha vcs https://github.com/dirnbauer/friendlycaptcha-typo3.git', 'composer require "in2code/powermail:~14.0.3.3" "studiomitte/friendlycaptcha:~2.3.0.1"', 'vendor/bin/typo3 extension:setup', '# Then add webconsulting/desiderio-powermail to dependencies in config/sites/<site>/config.yaml'])],
            'cta' => ['header' => 'Start from six working demo forms', 'description' => 'Contact, newsletter, callback, appointment, support and a four-step project request are ready to try. Each one has spam protection and its own thank-you page.', 'text' => 'Try the forms', 'link' => '/desiderio-powermail-lab'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function jev(): array
    {
        return [
            'slug' => 'jev',
            'product' => 'Jev',
            'badge' => 'Jev',
            'title' => 'Jev: forms that understand what people write',
            'navTitle' => 'AI form logic',
            'description' => 'Jev reads what visitors type into TYPO3 Powermail forms, shows only the fields that apply and routes each message to the right team.',
            'abstract' => 'Jev brings TypeSafe AI\'s typed decisions into TYPO3. Powermail forms show fields, hold back empty messages and route mail by what people write.',
            'hero' => [
                'header' => 'Forms that understand what people write',
                'subheadline' => 'Jev reads a message as the visitor fills in the form. Your forms then show the right fields and send mail to the right team.',
                'primaryButton' => ['text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/typo3-webcon-jev'],
                'secondaryButton' => ['text' => 'Try the demo', 'link' => '/desiderio-powermail-jev/support-triage'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-jev-support-triage-c0dd4571.webp', 'A bug report brings up the reproduction field', 'Support form where a described bug revealed the extra field \'How can we reproduce it?\'.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Forms can\'t read, so people sort by hand', 'content' => '<p>A form reacts to what someone clicks or types, not to what they mean. Every message lands in one inbox, and a person forwards it to sales, support or accounting. Meanwhile visitors answer questions that don\'t apply to them.</p>'],
            'tour' => [
                'header' => 'Forms that read before they route',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Spam gate',
                        'title' => 'Messages with nothing to answer can\'t be sent',
                        'description' => 'Noise hides the <strong>send button</strong> and shows a note. Borderline messages go through, because a false alarm costs a lead.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-jev-quality-gate-99e40dbb.webp', 'Messages with nothing to answer can\'t be sent', 'Contact form with the message \'asdf\', a note asking for more detail and no send button.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Playground',
                        'title' => 'Try a decision before any form uses it',
                        'description' => 'The <strong>playground</strong> runs a decision on sample text and shows each answer, its confidence and the full distribution.',
                        'image' => ShowcaseBlocks::screenshot('feature-jev-playground-2cb22dfe.webp', 'Try a decision before any form uses it', 'Jev playground result: a support message classified as a bug with confidence and severity distribution.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Run log',
                        'title' => 'Every call with its answer, latency and cost',
                        'description' => 'The <strong>run log</strong> records each call, how sure Jev was, how long it took and what it cost.',
                        'image' => ShowcaseBlocks::screenshot('feature-jev-run-log-f94168e6.webp', 'Every call with its answer, latency and cost', 'Jev run log in the TYPO3 backend listing recent calls with answers, latency, tokens and cost.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Routing',
                        'title' => 'Pick a decision, and mail finds its team',
                        'description' => 'The <strong>Jev routing</strong> tab lets a decision choose the receiver. Unsure answers fall back to the form\'s own receiver.',
                        'image' => ShowcaseBlocks::screenshot('feature-jev-routing-tab-a497efed.webp', 'Pick a decision, and mail finds its team', 'Powermail form record in TYPO3 with the Jev routing tab: decision and deciding question selected.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Jev',
                'items' => [
                    ['icon' => 'sparkles', 'title' => 'Fields that follow the message', 'description' => 'Six new powermail_cond operators let a rule ask Jev, for example to request reproduction steps only when a message reports a bug.'],
                    ['icon' => 'send', 'title' => 'Mail reaches the right team', 'description' => 'A Jev routing tab on each form picks the receiver from the message. Below the confidence threshold, the form\'s own receiver gets it.'],
                    ['icon' => 'shield-check', 'title' => 'A form never breaks', 'description' => 'No token, an outage or an unsure answer: every case falls back to the decision\'s default and writes the reason to the run log.'],
                    ['icon' => 'chart', 'title' => 'Every call on record', 'description' => 'The run log lists each call with its answers, latency, tokens and cost, and the module counts fallbacks per decision.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'Does form data leave our server?', 'answer' => 'Yes, the values a decision reads go to the Jev API of TypeSafe. Each decision\'s state template sets which fields that is. The API token stays on the server in nr-vault and never reaches a browser.'],
                    ['question' => 'What does it cost?', 'answer' => 'The TYPO3 extension is free under GPL-2.0-or-later. Jev is a managed API, so you need a key from TypeSafe. In our lab, 133 calls cost about 0.002 US cents each on average.'],
                    ['question' => 'What happens when Jev is unsure or offline?', 'answer' => 'The form keeps working. Below a decision\'s threshold, or when the API is unreachable, the default outcome applies. Fields stay as the editor built them, and mail goes to the form\'s own receiver. The run log says why.'],
                    ['question' => 'Which TYPO3 and PHP versions does it need?', 'answer' => 'TYPO3 v14.3 LTS and PHP 8.4 or newer, with netresearch/nr-vault for the token. The Powermail features need the TYPO3 v14 forks of powermail and powermail_cond.'],
                ],
            ],
            'install' => ['header' => 'Install Jev', 'code' => implode("\n", ['composer config repositories.webcon-jev vcs https://github.com/dirnbauer/typo3-webcon-jev.git', 'composer require webconsulting/webcon-jev', 'vendor/bin/typo3 extension:setup', '# Put your TypeSafe key into TYPESAFE_API_KEY, then move it into nr-vault:', 'vendor/bin/typo3 webcon-jev:token:import', 'vendor/bin/typo3 webcon-jev:ping'])],
            'cta' => ['header' => 'Let your forms read before they route', 'description' => 'Install Jev, store a TypeSafe key and try a decision in the playground before any form uses it.', 'text' => 'Try the demo', 'link' => '/desiderio-powermail-jev/support-triage'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function workos(): array
    {
        return [
            'slug' => 'workos',
            'product' => 'WorkOS Auth',
            'badge' => 'WorkOS Auth',
            'title' => 'WorkOS Auth: one login for frontend and backend',
            'navTitle' => 'Single sign-on',
            'description' => 'WorkOS sign-in for TYPO3 v14: passwords, email codes, social login and SSO for site and backend, plus self-service accounts and team invites.',
            'abstract' => 'WorkOS Auth adds WorkOS sign-in to the TYPO3 frontend and backend. Customers use their company identity, manage their account and invite their team themselves.',
            'hero' => [
                'header' => 'One login for your site and backend',
                'subheadline' => 'Visitors and editors sign in with a password, an email code or Google, Microsoft, GitHub and Apple. Business customers bring SSO and manage their own teams.',
                'primaryButton' => ['text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/workos'],
                'secondaryButton' => ['text' => 'Try the login', 'link' => '/features/workos/frontend-plugins/login'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-workos-login-c7ee547e.webp', 'A finished sign-in card, styled like your site', 'WorkOS sign-in card on the website with email, password, email code and Google, Microsoft, GitHub, Apple.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Every login you build is another project', 'content' => '<p>Customers expect to sign in with the identity their company already uses. Building SSO, MFA, email codes and team invitations yourself takes months. The backend then needs the same work again.</p>'],
            'tour' => [
                'header' => 'Sign-in screens you do not have to build',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Sign up',
                        'title' => 'Self-service registration without extra forms',
                        'description' => 'New customers <strong>create an account</strong> on your site. WorkOS stores the identity, TYPO3 links the user.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-workos-sign-up-957d9c8f.webp', 'Self-service registration without extra forms', 'WorkOS \'Create your account\' form with name, email, password and confirmation fields.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Backend',
                        'title' => 'The TYPO3 backend login with WorkOS options',
                        'description' => 'Editors use the <strong>same identity</strong> in the backend, and the classic username form stays one click away.',
                        'image' => ShowcaseBlocks::screenshot('feature-workos-backend-login-24a68a62.webp', 'The TYPO3 backend login with WorkOS options', 'TYPO3 backend login card with WorkOS email sign-in, email code, social buttons and SSO or passkey option.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Setup',
                        'title' => 'Setup lists every redirect URI to copy',
                        'description' => 'The <strong>Setup Assistant</strong> lists the redirect URIs to register in WorkOS, each with its own copy button.',
                        'image' => ShowcaseBlocks::screenshot('feature-workos-setup-assistant-42474903.webp', 'Setup lists every redirect URI to copy', 'WorkOS Setup Assistant table of redirect URIs for frontend and backend login with copy buttons.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with WorkOS Auth',
                'items' => [
                    ['icon' => 'lock', 'title' => 'One identity, two login screens', 'description' => 'The same WorkOS account signs people in to your website and the TYPO3 backend. TYPO3 still creates its own session.'],
                    ['icon' => 'mail', 'title' => 'Sign-in without passwords', 'description' => 'Email codes and Google, Microsoft, GitHub and Apple sign-in work on both screens, so fewer people need a password reset.'],
                    ['icon' => 'users', 'title' => 'Teams manage themselves', 'description' => 'Organisation admins invite colleagues, resend or revoke invitations and open Admin Portal links for SSO, Directory Sync and audit logs.'],
                    ['icon' => 'shield-check', 'title' => 'Security handled for you', 'description' => 'PKCE with a client secret, single-use state bound to a cookie, same-origin redirects and a write-only API key in the backend.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'How long does setup take?', 'answer' => 'Install the package, enter the API key and Client ID in the Setup Assistant, then copy the listed redirect URIs into the WorkOS dashboard. Sign-in methods are switched on in WorkOS, not in TYPO3.'],
                    ['question' => 'Which versions are supported?', 'answer' => 'TYPO3 v14.3.7 or newer and PHP 8.4, with the WorkOS PHP SDK 9.4 installed by Composer. You also need a WorkOS account with AuthKit enabled.'],
                    ['question' => 'What does it cost?', 'answer' => 'The extension is free under GPL-2.0-or-later. WorkOS bills its own service separately, according to its current plans.'],
                    ['question' => 'Where is user data stored?', 'answer' => 'Profiles stay in WorkOS. TYPO3 keeps a table that links each WorkOS identity to a frontend or backend user. It matches by email only when WorkOS has verified the address.'],
                ],
            ],
            'install' => ['header' => 'Install WorkOS Auth', 'code' => implode("\n", ['composer config repositories.workos-auth vcs https://github.com/dirnbauer/workos.git', 'composer require webconsulting/workos-auth', 'vendor/bin/typo3 extension:setup --extension=workos_auth'])],
            'cta' => ['header' => 'Add WorkOS sign-in to TYPO3', 'description' => 'Install the extension, run the Setup Assistant and place the login element on a page. Your backend login gets the same options.', 'text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/workos'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function x402Paywall(): array
    {
        return [
            'slug' => 'x402-paywall',
            'product' => 'x402 Paywall',
            'badge' => 'x402 Paywall',
            'title' => 'x402 Paywall: paid content for people and agents',
            'navTitle' => 'Paid content',
            'description' => 'Sell TYPO3 pages and API routes with HTTP 402. People pay from a browser wallet, AI agents pay in headers, and USDC settles in the same request.',
            'abstract' => 'x402 Paywall sells TYPO3 pages and API routes for small USDC amounts. Browsers get a wallet paywall, AI agents pay through HTTP headers, with no accounts or card forms.',
            'hero' => [
                'header' => 'Get paid for content by people and agents',
                'subheadline' => 'Set a price on a page or an API route. Visitors pay from their wallet, agents pay through HTTP 402 headers, and the content arrives in the same request.',
                'primaryButton' => ['text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/typo3-x402-paywall'],
                'secondaryButton' => ['text' => 'See the protocol', 'link' => 'https://www.x402.org'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-x402-paywall-payment-page-7adf8223.webp', 'A paywall page that works with any wallet', 'x402 payment page: \'Payment required\', price 0.01 USDC on Base Sepolia and a Pay button.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Small payments cost more than they earn', 'content' => '<p>Selling one article or one API call means accounts, card forms and fees that eat small amounts. AI agents can\'t fill in a checkout at all, so paid content stays out of their reach.</p>'],
            'tour' => [
                'header' => 'From paywall to payment in one flow',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Settings',
                        'title' => 'Price and prompt in the page properties',
                        'description' => 'Editors switch the paywall on <strong>per page</strong>, with an optional price and a prompt text for people and agents.',
                        'image' => ShowcaseBlocks::screenshot('feature-x402-paywall-page-properties-c15e604b.webp', 'Price and prompt in the page properties', 'TYPO3 page properties tab \'x402 Paywall\' with the sell toggle, price and payment prompt fields.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Simulator',
                        'title' => 'Test the payment flow without spending money',
                        'description' => 'The <strong>simulator</strong> plays the client side: it requests a paid page and decodes the HTTP 402 answer.',
                        'image' => ShowcaseBlocks::screenshot('feature-x402-paywall-simulator-ed8c6250.webp', 'Test the payment flow without spending money', 'x402 simulator output showing a 402 response and the decoded payment requirements.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Dashboard',
                        'title' => 'Revenue and a configuration check per site',
                        'description' => 'The <strong>dashboard</strong> shows settled revenue by period, settlement states and whether each site is set up correctly.',
                        'image' => ShowcaseBlocks::screenshot('feature-x402-paywall-dashboard-455adf1b.webp', 'Revenue and a configuration check per site', 'x402 Paywall dashboard with revenue cards, a site configuration table and recent settlement attempts.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with x402 Paywall',
                'items' => [
                    ['icon' => 'zap', 'title' => 'Agents can pay, too', 'description' => 'API clients and AI agents get HTTP 402 with the terms, sign a USDC authorisation and retry. No account, session or card form.'],
                    ['icon' => 'lock', 'title' => 'A paywall page for people', 'description' => 'Browsers get a payment page that connects MetaMask, Coinbase Wallet, Rabby or another EIP-1193 wallet, then loads the content.'],
                    ['icon' => 'tag', 'title' => 'A price for each page', 'description' => 'Tick \'Sell this page with x402\' in the page properties and set a price. Route patterns put whole API sections behind the paywall.'],
                    ['icon' => 'chart', 'title' => 'Revenue and a simulator', 'description' => 'The backend module shows settled revenue, settlement states and a configuration check per site. The simulator tests the flow first.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'Do we need a payment provider account?', 'answer' => 'No card processor. An x402 facilitator verifies and settles each payment: x402.org for testnets, or a mainnet facilitator such as Coinbase CDP with its API key. The USDC goes to the wallet address you configure.'],
                    ['question' => 'Which networks and tokens work?', 'answer' => 'Base, Base Sepolia, Polygon, Arbitrum, Ethereum or any eip155 chain, with USDC by default. Other tokens work once you set their contract details in the site configuration.'],
                    ['question' => 'Which TYPO3 version does it need?', 'answer' => 'TYPO3 v14.3 LTS or later in Composer mode and PHP 8.4 or newer, with outbound HTTPS to the facilitator. The extension is free under GPL-2.0-or-later.'],
                    ['question' => 'What does it store about payers?', 'answer' => 'Each settlement attempt is logged with page, amount, network, payer wallet, transaction hash, status and user agent. The IP address is stored only as a keyed hash.'],
                ],
            ],
            'install' => ['header' => 'Install x402 Paywall', 'code' => implode("\n", ['composer config repositories.x402-paywall vcs https://github.com/dirnbauer/typo3-x402-paywall.git', 'composer require webconsulting/typo3-x402-paywall', 'vendor/bin/typo3 database:updateschema'])],
            'cta' => ['header' => 'Put your first page behind the paywall', 'description' => 'Add a wallet address to your site configuration, tick one page and run the simulator. Start on Base Sepolia before real money moves.', 'text' => 'Get the code', 'link' => 'https://github.com/dirnbauer/typo3-x402-paywall'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function aiAssistant(): array
    {
        return [
            'slug' => 'ai-assistant',
            'product' => 'AI Assistant',
            'badge' => 'AI Assistant',
            'title' => 'AI Assistant: ask TYPO3, approve every change',
            'navTitle' => 'AI assistant',
            'description' => 'A chat in the TYPO3 v14 backend that answers questions about your site with its own MCP tools and asks before it changes anything.',
            'abstract' => 'A chat in the TYPO3 backend that answers questions about your installation with its own tools. Every change waits for your approval.',
            'hero' => [
                'header' => 'Ask about your site. Approve every change.',
                'subheadline' => 'A chat inside the TYPO3 backend that reads your pages and records with your permissions. When it wants to change something, it stops and asks.',
                'primaryButton' => ['text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-ai-assistant'],
                'secondaryButton' => ['text' => 'See the tools', 'link' => '/features/mcp-server'],
                'image' => ShowcaseBlocks::screenshot('feature-ai-assistant-start-fba0e7bd.webp', 'A chat that knows your installation', 'AI Assistant module: conversation list, empty chat with suggested questions, and details with model, tools and limits.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Answers hide behind too many clicks', 'content' => '<p>Which pages sit below Features, and what did the log record last night? Finding out means clicking through page trees, lists and logs. Most AI tools that could help want an API key, broad access and blind trust.</p>'],
            'tour' => [
                'header' => 'Ask, review, approve',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Answer',
                        'title' => 'Answers come from the site\'s own tools',
                        'description' => 'The assistant called <strong>GetPageTree</strong> with your permissions, shows the result it got, then answers in plain words.',
                        'image' => ShowcaseBlocks::screenshot('feature-ai-assistant-answer-21458ce7.webp', 'Answers come from the site\'s own tools', 'Chat with a question about the Features page tree, three read-only tool calls and the answer that lists every feature page.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Approval',
                        'title' => 'Every write stops and waits for you',
                        'description' => 'The proposed <strong>WriteTable</strong> call with its arguments. Nothing is written until you approve; deny, and the assistant carries on without it.',
                        'image' => ShowcaseBlocks::screenshot('feature-ai-assistant-approval-d8e9abaf.webp', 'Every write stops and waits for you', 'Approval card for a WriteTable call with its JSON arguments, Deny and Approve buttons, and a waiting notice in the details.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Changes',
                        'title' => 'See what changed, one click from the editor',
                        'description' => 'The approved change landed as a <strong>draft in the Staging workspace</strong> and is listed with the conversation, linked to the record editor.',
                        'image' => ShowcaseBlocks::screenshot('feature-ai-assistant-changes-9a912e08.webp', 'See what changed, one click from the editor', 'Chat after approval: the WriteTable call, the assistant\'s confirmation with a preview link, and every call with its effect in the details column.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Toolbar',
                        'title' => 'The chat follows you through the backend',
                        'description' => 'The toolbar dropdown keeps the conversation open across modules and sends <strong>the page you are on</strong> with your next message.',
                        'image' => ShowcaseBlocks::screenshot('feature-ai-assistant-toolbar-5b4526e6.webp', 'The chat follows you through the backend', 'AI Assistant chat in the backend toolbar dropdown, showing the conversation and a badge naming the current page.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with AI Assistant',
                'items' => [
                    ['icon' => 'message-circle', 'title' => 'Answers from your own installation', 'description' => 'It reads page trees, records, redirects and the system log through the site\'s MCP tools, with the permissions of the signed-in user.'],
                    ['icon' => 'shield-check', 'title' => 'Every write waits for you', 'description' => 'A write pauses the turn and shows the tool and its arguments. Approve and it runs; deny and nothing changes.'],
                    ['icon' => 'history', 'title' => 'See what changed, then open it', 'description' => 'Every record the assistant wrote is listed with the conversation, one click away from the record editor.'],
                    ['icon' => 'panel-top', 'title' => 'Follows you through the backend', 'description' => 'The toolbar chat stays open across modules and sends the page and workspace you are on with each message.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'What does it need to run?', 'answer' => 'TYPO3 v14.3 on PHP 8.4, nr-llm 0.35 with one working configuration, and the MCP Server fork 0.9. Composer installs the assistant and the MCP Server from GitHub and nr-llm from Packagist. Then give editors the AI Assistant module.'],
                    ['question' => 'Can it change content without asking?', 'answer' => 'No. Write tools stay off until an administrator enables them in nr-llm, and each write then waits for your approval. User TSconfig can remove tools per backend group, but it can never add any.'],
                    ['question' => 'Where does our data go?', 'answer' => 'The tools run inside TYPO3 as the signed-in user. Your question, the tool results and any extracted attachment text go to the provider of your nr-llm configuration. API keys stay encrypted in nr-vault.'],
                    ['question' => 'What does it cost?', 'answer' => 'The extension is free under GPL-2.0-or-later. You pay your AI provider for the tokens each turn uses, and nr-llm shows usage and estimated cost. By default each user may start 60 turns an hour.'],
                ],
            ],
            'install' => ['header' => 'Install AI Assistant', 'code' => implode("\n", ['composer config repositories.typo3-mcp-server vcs https://github.com/dirnbauer/typo3-mcp-server.git', 'composer config repositories.typo3-abilities vcs https://github.com/dirnbauer/typo3-abilities.git', 'composer config repositories.typo3-ai-assistant vcs https://github.com/dirnbauer/typo3-ai-assistant.git', 'composer require webconsulting/typo3-ai-assistant:^2.0', 'vendor/bin/typo3 extension:setup', '# Needs one working nr-llm configuration (default identifier: backend-assistant).', '# Then open Administration > AI Assistant > Chat.'])],
            'cta' => ['header' => 'Put an assistant into your TYPO3 backend', 'description' => 'Install it with Composer, choose an nr-llm configuration and start asking. Every change still waits for your approval.', 'text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-ai-assistant'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function nrLlmManual(): array
    {
        return [
            'slug' => 'nr-llm-manual',
            'product' => 'nr-llm',
            'badge' => 'nr-llm',
            'title' => 'nr-llm: one AI setup for every extension',
            'navTitle' => 'AI models',
            'description' => 'Set up AI providers, models and named configurations once in TYPO3 with nr-llm. Keys stay encrypted in nr-vault; usage and cost stay visible.',
            'abstract' => 'nr-llm keeps AI providers, models and configurations in one place for every extension. Keys stay encrypted in nr-vault, and each feature uses a named configuration you can test.',
            'hero' => [
                'header' => 'Set up AI once for every TYPO3 extension',
                'subheadline' => 'Connect a provider once, keep the key encrypted and give every AI feature a named configuration you can test and switch.',
                'primaryButton' => ['text' => 'Get nr-llm', 'link' => 'https://github.com/netresearch/t3x-nr-llm'],
                'secondaryButton' => ['text' => 'See the assistant', 'link' => '/features/ai-assistant'],
                'image' => ShowcaseBlocks::screenshot('feature-nr-llm-manual-overview-0d0bb65d.webp', 'Usage, cost and setup state at a glance', 'nr-llm overview with cost, request and token tiles, a requests-by-provider chart, daily requests and setup cards.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Every AI feature brings its own setup', 'content' => '<p>Each AI extension wants its own provider settings, its own place for the API key and its own bill. Switching models means touching every extension. Nobody can say what AI costs the site in total.</p>'],
            'tour' => [
                'header' => 'Providers, models and settings in one place',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Providers',
                        'title' => 'Keys stay in nr-vault, never on screen',
                        'description' => 'The provider row shows <strong>Configured</strong> instead of the key. The key itself sits encrypted in nr-vault.',
                        'image' => ShowcaseBlocks::screenshot('feature-nr-llm-manual-providers-cb3762ff.webp', 'Keys stay in nr-vault, never on screen', 'Provider list with one active OpenAI provider, a green Configured badge in the API key column and 30-day usage.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Models',
                        'title' => 'Models with their capabilities and usage',
                        'description' => 'Each model lists its <strong>capabilities</strong>, context size and 30-day usage. One default model serves calls that name none.',
                        'image' => ShowcaseBlocks::screenshot('feature-nr-llm-manual-models-e7876a31.webp', 'Models with their capabilities and usage', 'Model list with GPT-5.6 Terra as default, GPT-5.6 Luna, GPT-5 mini and two image models, with capability badges.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Configurations',
                        'title' => 'Named configurations every feature can use',
                        'description' => '<strong>content-assistant</strong> runs on GPT-5.6 Terra, <strong>backend-assistant</strong> on GPT-5 mini. Change a model here, and every feature using that name follows.',
                        'image' => ShowcaseBlocks::screenshot('feature-nr-llm-manual-configurations-0c860476.webp', 'Named configurations every feature can use', 'Configuration list: Content Assistant as default on GPT-5.6 Terra, plus translator, SEO, fast and backend assistant rows.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Test run',
                        'title' => 'Test a configuration before a task uses it',
                        'description' => 'One click sends a <strong>test prompt</strong> through the configuration and shows the answer, so you know it works before you assign it.',
                        'image' => ShowcaseBlocks::screenshot('feature-nr-llm-manual-test-3e843393.webp', 'Test a configuration before a task uses it', 'Test Result card below the configuration list with a green success message and the model\'s short reply.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with nr-llm',
                'items' => [
                    ['icon' => 'key-round', 'title' => 'Keys never leave the vault', 'description' => 'Provider records hold only an nr-vault identifier. The backend shows \'Configured\', never the key, and nr-vault logs every access.'],
                    ['icon' => 'sliders-horizontal', 'title' => 'Named configurations, not raw models', 'description' => 'Features ask for \'content-assistant\' or \'translator\'. Change the model behind a name, and every feature using it follows.'],
                    ['icon' => 'chart-column', 'title' => 'Usage and cost in one view', 'description' => 'The AI section shows cost, requests and tokens for the last 30 days. Analytics breaks them down by provider and model.'],
                    ['icon' => 'shuffle', 'title' => 'Switch providers without new code', 'description' => 'OpenAI, Anthropic, Gemini, Mistral, Ollama and OpenAI-compatible servers sit behind one interface. A switch needs no code changes.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'Which AI providers can we use?', 'answer' => 'OpenAI, Anthropic, Google Gemini, Mistral, Groq, OpenRouter, Azure OpenAI, local Ollama and any OpenAI-compatible server. This lab runs on OpenAI. Switching means a new provider record and model, not new code.'],
                    ['question' => 'Where are the API keys stored?', 'answer' => 'In nr-vault, encrypted with AES-256-GCM envelope encryption. The provider record holds only a vault identifier, and the backend shows \'Configured\' instead of the key. nr-vault logs every access to a secret.'],
                    ['question' => 'Which TYPO3 and PHP versions?', 'answer' => 'nr-llm supports TYPO3 13.4 and 14.3 on PHP 8.2 or newer, and this lab runs version 0.35 on TYPO3 v14.3 with PHP 8.4. Netresearch publishes it under GPL-2.0-or-later.'],
                    ['question' => 'How do we keep AI costs under control?', 'answer' => 'Each configuration can cap requests, tokens and cost per day, and user budgets cap spending per backend user. The overview shows 30 days of requests, tokens and estimated cost per provider.'],
                ],
            ],
            'install' => ['header' => 'Install nr-llm', 'code' => implode("\n", ['composer require netresearch/nr-llm', 'vendor/bin/typo3 extension:setup', 'vendor/bin/typo3 vault:init   # first install only: creates the nr-vault master key', '# Then open AI > Setup > Setup Wizard, add your provider and store its key in nr-vault.'])],
            'cta' => ['header' => 'Give every AI feature one foundation', 'description' => 'Install nr-llm, run the Setup Wizard and name your configurations. Keys stay encrypted, and costs stay visible.', 'text' => 'Get nr-llm', 'link' => 'https://github.com/netresearch/t3x-nr-llm'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function skillflow(): array
    {
        return [
            'slug' => 'skillflow',
            'product' => 'Skillflow',
            'badge' => 'Skillflow',
            'title' => 'Skillflow: agent skills that review your pages',
            'navTitle' => 'Agent skills',
            'description' => 'Run agent skills on TYPO3 pages and workspace stages, keep every report and publish your skill library as a searchable Solr catalogue.',
            'abstract' => 'Skillflow runs agent skills on TYPO3 pages and workspace stages and keeps every report. Your skill library becomes a searchable catalogue with a page per skill.',
            'hero' => [
                'header' => 'Every draft gets the same AI review',
                'subheadline' => 'Assign agent skills to pages, users or workspace stages. Run them in the Skills module, and TYPO3 keeps every report for your team.',
                'primaryButton' => ['text' => 'Get Skillflow', 'link' => 'https://github.com/dirnbauer/skillflow'],
                'secondaryButton' => ['text' => 'Browse the skills', 'link' => 't3://page?uid=1030'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-skillflow-catalogue-8403a331.webp', 'Search your skill library with facets', 'Skill catalogue on a TYPO3 page: a search for security with 24 results and facet filters for source, tools and licence.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Every reviewer checks something different', 'content' => '<p>Your SEO, accessibility and tone rules live in one person\'s head or an old wiki page. Drafts get whatever check the reviewer remembers that day. Prompts pasted into a chat window leave no record of what was checked.</p>'],
            'tour' => [
                'header' => 'From skill catalogue to stored report',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Skill page',
                        'title' => 'A page for every skill',
                        'description' => 'The detail element shows the <strong>skill ID, licence, version and allowed tools</strong>. It also sets the page title and meta description.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-skillflow-skill-detail-d142c5ed.webp', 'A page for every skill', 'Detail page of the security-audit skill with its description, a copyable skill ID, licence, version and allowed tools.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Run report',
                        'title' => 'A stored report for every run',
                        'description' => 'Each run keeps its <strong>status, record, workspace and engine</strong>. The report is rendered from Markdown, with raw HTML escaped.',
                        'image' => ShowcaseBlocks::screenshot('feature-skillflow-run-report-87bdaa2e.webp', 'A stored report for every run', 'Skillflow run report in the TYPO3 backend: a typo3-seo review of a page with status, run details and Markdown findings.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Stage review',
                        'title' => 'Reviews that start with your workflow',
                        'description' => 'Assign skills to a <strong>custom workspace stage</strong> and switch on auto-run. Each record sent to that stage gets reviewed.',
                        'image' => ShowcaseBlocks::screenshot('feature-skillflow-stage-review-35e8369c.webp', 'Reviews that start with your workflow', 'Workspace stage form in TYPO3 with three assigned skills and the switch that runs them on every stage change.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Skillflow',
                'items' => [
                    ['icon' => 'file-code', 'title' => 'Skills from git, reviewed first', 'description' => 'Sync SKILL.md files from a git repository. New skills arrive switched off, and a changed skill is switched off again until someone reviews it.'],
                    ['icon' => 'check-circle', 'title' => 'Reviews on workspace stages', 'description' => 'Assign skills to a custom workspace stage and switch on auto-run. Every record sent to that stage is reviewed, and the report is stored.'],
                    ['icon' => 'file-text', 'title' => 'Reports your team can check', 'description' => 'Each run keeps its status, verdict, engine and Markdown report. Reports are advice, and Skillflow applies nothing by itself.'],
                    ['icon' => 'search', 'title' => 'A catalogue visitors can search', 'description' => 'Publish active skills with Solr search, translated facets and a detail page per skill, with its own page title and meta description.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'What do I need to run it?', 'answer' => 'TYPO3 v14.3.7 or later with PHP 8.4, nr_llm 0.34 or 0.35 for skills and models, and EXT:solr 14.0.1 for the catalogue. Install it with Composer, run extension:setup and add a skill source in nr_llm.'],
                    ['question' => 'Does our content leave the server?', 'answer' => 'Only when a skill runs: the page content goes to the model provider you configured. By default, skills run only in a local DDEV installation in Development context. API keys stay in nr_llm\'s vault or in environment variables.'],
                    ['question' => 'Can a skill change our pages?', 'answer' => 'Skillflow stores reports and applies nothing itself. With the Claude Code runner, a skill may use only the tools and abilities it declares. Hidden, disabled and orphaned skills never run.'],
                    ['question' => 'How is it licensed?', 'answer' => 'Skillflow is GPL-2.0-or-later, like TYPO3. It is not on Packagist, so add the GitHub repository to Composer first. Imported skills keep their own licences, and the catalogue shows them.'],
                ],
            ],
            'install' => ['header' => 'Install Skillflow', 'code' => implode("\n", ['composer config repositories.skillflow vcs https://github.com/dirnbauer/skillflow.git', 'composer require webconsulting/skillflow:^1.9', 'vendor/bin/typo3 extension:setup', 'vendor/bin/typo3 cache:flush', '# Catalogue: add the site set webconsulting/skillflow-solr to your site'])],
            'cta' => ['header' => 'Give every draft the same review', 'description' => 'Install Skillflow, connect a skill repository in nr_llm and assign your first skill to a workspace stage. It runs on TYPO3 v14.3 with PHP 8.4.', 'text' => 'Get Skillflow', 'link' => 'https://github.com/dirnbauer/skillflow'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function skillspector(): array
    {
        return [
            'slug' => 'skillspector',
            'product' => 'Skillspector',
            'badge' => 'Skillspector',
            'title' => 'Skillspector: check skills before agents use them',
            'navTitle' => 'Skill security',
            'description' => 'Check every agent skill for prompt injection, secrets, risky commands and licence problems before your AI follows it, with advisory checks in TYPO3.',
            'abstract' => 'Skillspector reads every agent skill before your AI does. It flags prompt injection, secrets, risky commands and licence problems, and an administrator decides what to hide.',
            'hero' => [
                'header' => 'Spot risky skills before your agent runs them',
                'subheadline' => 'Skillspector checks each skill for prompt injection, secrets, dangerous commands and licence problems. You see the evidence and decide what to hide.',
                'primaryButton' => ['text' => 'Get Skillspector', 'link' => 'https://github.com/dirnbauer/typo3-skillspector'],
                'secondaryButton' => ['text' => 'Read the manual', 'link' => 'https://github.com/dirnbauer/typo3-skillspector/tree/main/Documentation'],
                'image' => ShowcaseBlocks::screenshot('feature-skillspector-overview-867fea83.webp', 'Every skill with its review level', 'Skills Inspector module in TYPO3 listing 60 skills with review-level filters, licence badges and SkillSpector results.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Skills are instructions from strangers', 'content' => '<p>A skill is text your AI agent will follow, often copied from a public repository. One hidden line can leak a key, pipe a script into a shell or skip confirmations. Nobody reads every line before switching it on.</p>'],
            'tour' => [
                'header' => 'Findings you can check',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Findings',
                        'title' => 'Evidence, not just a warning',
                        'description' => 'Each finding shows <strong>severity, location, the matched text</strong> and what to check. Built-in rules and SkillSpector results sit side by side.',
                        'image' => ShowcaseBlocks::screenshot('feature-skillspector-findings-179ea892.webp', 'Evidence, not just a warning', 'Findings for the php-modernization skill: a destructive rm -rf command, an autonomy prompt and three SkillSpector warnings.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'You decide',
                        'title' => 'Hiding a skill is always your call',
                        'description' => 'Checks never change a skill. An administrator hides it after a <strong>confirmation</strong>, and its enabled state in nr_llm stays as it is.',
                        'image' => ShowcaseBlocks::screenshot('feature-skillspector-hide-confirm-78f023f9.webp', 'Hiding a skill is always your call', 'Confirmation dialog in TYPO3 asking whether to hide the php-modernization skill, with Cancel and Hide buttons.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Skillspector',
                'items' => [
                    ['icon' => 'alert-triangle', 'title' => 'Fifteen built-in security rules', 'description' => 'Finds prompt injection, exposed keys, pipe-to-shell, fork bombs, exfiltration endpoints and destructive commands in the skill text and its code examples.'],
                    ['icon' => 'file-text', 'title' => 'Licence check for code', 'description' => 'When a skill ships code, Skillspector compares its declared licence with GPL-2.0-or-later and marks anything that needs a human decision.'],
                    ['icon' => 'search', 'title' => 'Works with NVIDIA SkillSpector', 'description' => 'If the NVIDIA SkillSpector binary is installed, its findings and risk score join the report. Without it, the built-in checks still run.'],
                    ['icon' => 'user', 'title' => 'An administrator decides', 'description' => 'Checks never enable, disable or hide a skill. An administrator reads the evidence and hides a skill only after a confirmation.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'What do I need?', 'answer' => 'TYPO3 v14.3 with PHP 8.4 and nr_llm 0.34 or 0.35, which stores the skills. The scheduler and the NVIDIA scanner are optional. Add the GitHub repository to Composer, require the package and run extension:setup.'],
                    ['question' => 'Does skill content leave our server?', 'answer' => 'Not by default. The built-in checks run locally, and a check never fetches or runs referenced scripts. Only the optional semantic analysis sends skill content to nr_llm\'s default provider, and it is off by default.'],
                    ['question' => 'Can it run on a schedule?', 'answer' => 'Yes. skillspector:check is a normal console command for cron or a scheduler task. It refreshes every report and can email action messages to the addresses you set.'],
                    ['question' => 'How is it licensed?', 'answer' => 'Skillspector is GPL-2.0-or-later, like TYPO3. NVIDIA SkillSpector is a separate program that you install yourself if you want its results.'],
                ],
            ],
            'install' => ['header' => 'Install Skillspector', 'code' => implode("\n", ['composer config repositories.skillspector vcs https://github.com/dirnbauer/typo3-skillspector.git', 'composer require webconsulting/skillspector:^1.2', 'vendor/bin/typo3 extension:setup --extension=skillspector', '# Optional: the NVIDIA scanner', 'uv tool install git+https://github.com/NVIDIA/skillspector.git'])],
            'cta' => ['header' => 'Check your skills before you trust them', 'description' => 'Install Skillspector next to nr_llm and click Check all skills. Nothing is hidden until an administrator decides.', 'text' => 'Get Skillspector', 'link' => 'https://github.com/dirnbauer/typo3-skillspector'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function mcpServer(): array
    {
        return [
            'slug' => 'mcp-server',
            'product' => 'MCP Server',
            'badge' => 'MCP Server',
            'title' => 'MCP Server: connect your AI assistant to TYPO3',
            'navTitle' => 'AI connections',
            'description' => 'Connect Claude, Cursor or Codex to TYPO3 v14. 52 built-in MCP tools for pages, records and files, with OAuth login, workspace drafts and a CLI.',
            'abstract' => 'Connect Claude, Cursor or Codex to TYPO3. They get structured tools for pages, records and files, and on production every write lands in a workspace.',
            'hero' => [
                'header' => 'Let Claude and Cursor work safely in TYPO3',
                'subheadline' => 'Claude, Cursor and Codex get structured tools for pages, records and files. Writes follow TYPO3 permissions and, on production, land in a workspace first.',
                'primaryButton' => ['text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-mcp-server'],
                'secondaryButton' => ['text' => 'See the assistant', 'link' => '/features/ai-assistant'],
                'image' => ShowcaseBlocks::screenshot('feature-mcp-server-connect-756d6fe9.webp', 'Step-by-step setup for Claude, Cursor and Codex', 'MCP Server module, Connect a client tab: server URL with copy button and numbered steps to add TYPO3 to Claude.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Copy and paste is not an AI workflow', 'content' => '<p>Your team already works in Claude or Cursor, but TYPO3 content reaches them by copy and paste. Direct database access is out of the question. So the assistant guesses, and someone retypes its answer in the backend.</p>'],
            'tour' => [
                'header' => 'Connect, check, then let it work',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Tools',
                        'title' => 'Every tool a client sees and may use',
                        'description' => 'Filter the tool list by name. Badges mark tools that <strong>only read</strong>, change data, need an administrator or run only in development.',
                        'image' => ShowcaseBlocks::screenshot('feature-mcp-server-tools-6e2c532a.webp', 'Every tool a client sees and may use', 'Tools tab filtered by workspace: ListWorkspaces, PublishWorkspace, WorkspaceReview and more with read-only or writes badges.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Checks',
                        'title' => 'Connection checks that say how to fix things',
                        'description' => 'The server checks the /mcp endpoint, OAuth metadata, workspaces and the tools. Every result comes with <strong>what to do</strong> next.',
                        'image' => ShowcaseBlocks::screenshot('feature-mcp-server-check-4dc2eef3.webp', 'Connection checks that say how to fix things', 'Connection check tab with a status summary and a table of checks, each with a status badge, the result and a fix.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Drafts',
                        'title' => 'Agent edits wait in a workspace',
                        'description' => 'A change made through MCP arrives as a <strong>draft</strong> in the Staging workspace. It reaches the live site only when an editor publishes it.',
                        'image' => ShowcaseBlocks::screenshot('feature-mcp-server-draft-00b87879.webp', 'Agent edits wait in a workspace', 'Publish module dialog for the MCP Server page in Staging, with the meta description change as a word-by-word diff.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with MCP Server',
                'items' => [
                    ['icon' => 'plug', 'title' => 'Works with the AI you use', 'description' => 'Claude signs in over OAuth with your backend login. Cursor and Codex start the server locally. The module shows the steps.'],
                    ['icon' => 'git-branch', 'title' => 'Drafts before live content', 'description' => 'On production, every record write lands in a TYPO3 workspace. Editors review and publish, and clients only see stable live IDs.'],
                    ['icon' => 'list-checks', 'title' => 'Switch off what you don\'t need', 'description' => 'A YAML manifest lists what each tool may touch. Remove database:write, and every writing tool stops. Outbound HTTP defaults to your own site.'],
                    ['icon' => 'terminal', 'title' => 'The same tools on the CLI', 'description' => 'Every tool also runs as a TYPO3 console command with JSON output, so scripts and CI pipelines use the same logic.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'What do we install?', 'answer' => 'Add the fork and the Abilities registry as Composer VCS repositories, require hn/typo3-mcp-server ^0.9 and run extension:setup. It needs TYPO3 v14.3 with workspaces and PHP 8.4. There is no TER release.'],
                    ['question' => 'Can the AI change live content?', 'answer' => 'Not on production: record writes land in a draft workspace and go live when someone publishes. On DDEV or in the Development context, writes go live by design for speed. Set localUnsafeMode to off to keep drafts everywhere.'],
                    ['question' => 'Which rights does a connected client get?', 'answer' => 'Exactly those of a TYPO3 backend user. Claude signs in through OAuth with PKCE; other clients use a personal access token. Page permissions, table rights, workspaces and the capability manifest apply to every call.'],
                    ['question' => 'Who maintains it, and under which licence?', 'answer' => 'It is a maintained fork of the TYPO3 MCP Server by Marco Pfeiffer (hauptsacheNet), under GPL-2.0-or-later. The fork adds the backend module, OAuth, the capability manifest and the Abilities bridge.'],
                ],
            ],
            'install' => ['header' => 'Install MCP Server', 'code' => implode("\n", ['composer config repositories.typo3-mcp-server vcs https://github.com/dirnbauer/typo3-mcp-server.git', 'composer config repositories.typo3-abilities vcs https://github.com/dirnbauer/typo3-abilities.git', 'composer require hn/typo3-mcp-server:^0.9', 'vendor/bin/typo3 extension:setup', '# Then open User > MCP Server in the backend.'])],
            'cta' => ['header' => 'Connect your AI assistant to TYPO3', 'description' => 'Install the fork, open User > MCP Server and follow the steps for Claude, Cursor or Codex. On production, writes stay in workspaces.', 'text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-mcp-server'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function typo3Abilities(): array
    {
        return [
            'slug' => 'typo3-abilities',
            'product' => 'Abilities Registry',
            'badge' => 'Abilities Registry',
            'title' => 'Abilities Registry: control what agents may do',
            'navTitle' => 'Agent permissions',
            'description' => 'One typed registry of what your TYPO3 site can do. MCP, CLI, REST and the backend run each ability under one policy, with scopes and a trace log.',
            'abstract' => 'One list of what your installation can do, with scopes, risk tiers and a site-wide policy. Every surface runs it the same way and logs each attempt.',
            'hero' => [
                'header' => 'Decide what AI agents may do in TYPO3',
                'subheadline' => 'Describe an action once, with its input, scopes and risk. MCP, the CLI, REST and the backend then run it through the same checks and log every attempt.',
                'primaryButton' => ['text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-abilities'],
                'secondaryButton' => ['text' => 'See MCP tools', 'link' => '/features/mcp-server'],
                'image' => ShowcaseBlocks::screenshot('feature-typo3-abilities-registry-c4b411e5.webp', 'Every ability with its scopes and risk', 'Abilities registry with 13 abilities, risk badges from low to critical, scopes, side effects, surfaces and Run buttons.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Every new protocol opens another door', 'content' => '<p>MCP, REST, webhooks and the CLI each get their own endpoints and their own checks. The rules drift apart, and nobody can say which agent did what. An audit then turns into guesswork.</p>'],
            'tour' => [
                'header' => 'Every ability, its rules and its traces',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Catalogue',
                        'title' => 'One catalogue of everything the site can do',
                        'description' => 'Abilities, MCP tools, skills and console commands in one list. Each entry says <strong>how to call it</strong> on every surface.',
                        'image' => ShowcaseBlocks::screenshot('feature-typo3-abilities-catalogue-6541f127.webp', 'One catalogue of everything the site can do', 'Catalogue filtered by publish with abilities, MCP tools, skills and CLI rows; workspace/publish shows five invocations.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Approval',
                        'title' => 'Risky actions need a person\'s approval',
                        'description' => 'The site policy marks <strong>high-risk</strong> abilities as review required. Here a person approves; tokens and agents never can.',
                        'image' => ShowcaseBlocks::screenshot('feature-typo3-abilities-review-acc94ad6.webp', 'Risky actions need a person\'s approval', 'Run tab with the Delete page ability selected, its input form, the Approve review checkbox and the Execute button.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Traces',
                        'title' => 'Every attempt is traced, denials included',
                        'description' => 'Each run records the ability, surface, outcome, duration, backend user id and input. <strong>Denied</strong> attempts stay in the log for audits.',
                        'image' => ShowcaseBlocks::screenshot('feature-typo3-abilities-traces-878eacc5.webp', 'Every attempt is traced, denials included', 'Traces table, newest first, with ok and failed runs from MCP and CLI, durations, backend user ids and JSON inputs.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Abilities Registry',
                'items' => [
                    ['icon' => 'shield-check', 'title' => 'One rulebook for every surface', 'description' => 'MCP, CLI, REST, webhooks and the backend call one executor: policy, input check, scopes, permissions, then an output check.'],
                    ['icon' => 'user-check', 'title' => 'People approve the risky actions', 'description' => 'Mark high-risk abilities as review required. They run only after a person approves, never on a token\'s or an agent\'s word.'],
                    ['icon' => 'scroll-text', 'title' => 'A trace for every attempt', 'description' => 'Allowed, denied or failed, each run records the ability, surface, input, outcome, duration and backend user.'],
                    ['icon' => 'compass', 'title' => 'One catalogue agents can read', 'description' => 'Agents find abilities, MCP tools, skills, REST endpoints and console commands in one list, with how to call each.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'What does it need?', 'answer' => 'TYPO3 v14.3 on PHP 8.4 or 8.5, installed with Composer from GitHub. Everything else is optional: the MCP Server, EXT:reactions, workspaces and skill extensions each add a surface when present.'],
                    ['question' => 'Can an agent approve its own risky action?', 'answer' => 'No. A review-required ability runs only with a human approval: the --approve-review flag on the CLI or the checkbox in the backend module. REST, MCP and webhook calls cannot approve, and REST answers 409.'],
                    ['question' => 'What do tokens and traces store?', 'answer' => 'Tokens are saved as SHA-256 hashes, and the plaintext is shown once. Traces record the ability, surface, input, outcome, duration and backend user id. They are pruned after 30 days by default.'],
                    ['question' => 'How does it compare with WordPress?', 'answer' => 'It follows the vocabulary and REST layout of the WordPress Abilities API from core 6.9. It adds scopes, risk tiers, side effects and a site-wide policy. The licence is GPL-2.0-or-later.'],
                ],
            ],
            'install' => ['header' => 'Install Abilities Registry', 'code' => implode("\n", ['composer config repositories.typo3-abilities vcs https://github.com/dirnbauer/typo3-abilities.git', 'composer require webconsulting/typo3-abilities:^1.3', 'vendor/bin/typo3 extension:setup --extension=abilities', 'vendor/bin/typo3 abilities:list'])],
            'cta' => ['header' => 'Give agents rules before you give them access', 'description' => 'Register an ability once, and MCP, the CLI, REST and the backend follow the same policy. Every attempt lands in the trace log.', 'text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-abilities'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function sgApicore(): array
    {
        return [
            'slug' => 'sg-apicore',
            'product' => 'sg_apicore',
            'badge' => 'sg_apicore',
            'title' => 'sg_apicore: a documented REST API for TYPO3',
            'navTitle' => 'REST API',
            'description' => 'Build REST APIs in TYPO3 with PHP attributes: live Swagger docs, token or user login, scopes, rate limits, Auto-CRUD for tables and MCP tools.',
            'abstract' => 'sg_apicore turns TYPO3 content into a REST API with live Swagger docs, token login, scopes and rate limits. The same endpoints also work as MCP tools.',
            'hero' => [
                'header' => 'Give apps and partners a documented TYPO3 API',
                'subheadline' => 'Add one PHP attribute per endpoint and get routing, token login, rate limits and live OpenAPI docs. Apps and partners read TYPO3 content without scraping.',
                'primaryButton' => ['text' => 'Get sg_apicore', 'link' => 'https://gitlab.sgalinski.de/typo3/sg_apicore'],
                'secondaryButton' => ['text' => 'See live docs', 'link' => '/api/public/v1/docs/ui'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-sg-apicore-swagger-825aba22.webp', 'Live API docs, generated from your code', 'Swagger UI for the public v1 API in the light theme, with pages, System, tt_content, MCP and OpenAPI endpoint groups.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Apps need data, not rendered pages', 'content' => '<p>A mobile app, a partner portal or an AI agent needs clean JSON, not HTML. Every hand-built endpoint needs its own login, limits and documentation. Partners wait for specs that are outdated when they arrive.</p>'],
            'tour' => [
                'header' => 'Documented endpoints you can try',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Try it',
                        'title' => 'Try a request in the browser',
                        'description' => 'A filtered <strong>GET /pages</strong> returns the lab\'s feature pages as JSON, with pagination and rate-limit headers.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-sg-apicore-try-it-6ae042bd.webp', 'Try a request in the browser', 'Swagger UI server response: HTTP 200 with a JSON list of five feature pages (uid, title, slug) and the response headers.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'CRUD',
                        'title' => 'Full CRUD for a table, behind a token',
                        'description' => 'One registration exposes <strong>tt_content</strong> for list, read, create, update and delete. Every call needs a partner token with the right scope.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-sg-apicore-crud-a65b3316.webp', 'Full CRUD for a table, behind a token', 'Swagger UI tt_content group of the partner API with GET, POST, PATCH and DELETE operations, each with a lock icon.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Endpoints',
                        'title' => 'Every endpoint with its login and MCP tool',
                        'description' => 'Method, path, API version, login mode and scopes per endpoint, and whether it is <strong>exposed as an MCP tool</strong>.',
                        'image' => ShowcaseBlocks::screenshot('feature-sg-apicore-endpoints-7a010ef7.webp', 'Every endpoint with its login and MCP tool', 'API Core Endpoints table with method badges, paths, API and version badges, auth modes, MCP tool names and scopes.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with sg_apicore',
                'items' => [
                    ['icon' => 'book-open', 'title' => 'Docs that stay in sync', 'description' => 'Swagger UI and OpenAPI 3.0 JSON are generated from your PHP attributes and TCA labels. Partners try calls in the browser.'],
                    ['icon' => 'key-round', 'title' => 'Access by token, login or session', 'description' => 'Public, machine tokens, user login with refresh tokens, or a backend session. Require scopes per endpoint and allow only listed origins.'],
                    ['icon' => 'database', 'title' => 'Table endpoints without controllers', 'description' => 'Register a table to get list, get, create, update and delete. Writes run through DataHandler, so hooks and history still work.'],
                    ['icon' => 'bot', 'title' => 'Endpoints double as AI tools', 'description' => 'Each API serves its endpoints as MCP tools, through the same router and login. This lab exposes 22 of them.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'How much work is the first endpoint?', 'answer' => 'Tag a controller with sg_apicore.router, add #[ApiRoute] to a method and open /api/public/v1/docs/ui. A table becomes a resource with one registerResource() call and no controller.'],
                    ['question' => 'Which TYPO3 and PHP versions?', 'answer' => 'Version 3.1.2 supports TYPO3 12.3 to 14.3 on PHP 8.2 or newer, and this lab runs it on TYPO3 v14.3 with PHP 8.4. It is published by sgalinski under GPL-2.0-or-later.'],
                    ['question' => 'How do we keep the API safe?', 'answer' => 'Pick an auth mode per API or endpoint and require scopes. CORS denies other origins unless you list them. Rate limits answer with HTTP 429, and logs mask tokens, passwords and cookies.'],
                    ['question' => 'Can AI agents use the same endpoints?', 'answer' => 'Yes. Each API serves its endpoints as MCP tools over JSON-RPC at /api/{api}/v{version}/mcp, through the same router and login. A denylist or #[ApiMcp(exclude: true)] keeps an endpoint out.'],
                ],
            ],
            'install' => ['header' => 'Install sg_apicore', 'code' => implode("\n", ['composer require sgalinski/sg-apicore', 'vendor/bin/typo3 extension:setup --extension=sg_apicore', '# Swagger UI of the public API: /api/public/v1/docs/ui'])],
            'cta' => ['header' => 'Open your content to apps and partners', 'description' => 'Install sg_apicore, register an API and share the Swagger link. Scopes, rate limits and request IDs are already in place.', 'text' => 'Get sg_apicore', 'link' => 'https://gitlab.sgalinski.de/typo3/sg_apicore'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function agentNexus(): array
    {
        return [
            'slug' => 'agent-nexus',
            'product' => 'Agent Nexus',
            'badge' => 'Agent Nexus',
            'title' => 'Agent Nexus: five agent protocols on your TYPO3',
            'navTitle' => 'Agent protocols',
            'description' => 'Run A2UI, AG-UI, A2A, UCP and AP2 on your own TYPO3 site. Public endpoints, editor widgets, a traffic log and approval before every write.',
            'abstract' => 'Agent Nexus runs five agent protocols on your TYPO3 site, with endpoints, demo widgets and a traffic log. Every write waits for a person, and every payment is simulated.',
            'hero' => [
                'header' => 'Run five agent protocols on your own TYPO3',
                'subheadline' => 'A2UI, AG-UI, A2A, UCP and AP2 each get public endpoints, a backend console and a widget editors can place. Nothing is charged, and every write waits for approval.',
                'primaryButton' => ['text' => 'Get Agent Nexus', 'link' => 'https://github.com/dirnbauer/typo3-agent-nexus'],
                'secondaryButton' => ['text' => 'Try the demos', 'link' => 't3://page?uid=1400'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-agent-nexus-hub-94abe630.webp', 'Five protocols on one landing page', 'Protocol hub on a TYPO3 page with cards for A2UI, AG-UI and A2A, their endpoints, spec versions and ready status.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Agent protocols are hard to judge from slides', 'content' => '<p>Clients ask what A2A, AG-UI or UCP would mean for their site. The specifications keep moving, and a slide cannot show a real request or an approval step. You need something that runs on TYPO3 and that you can inspect.</p>'],
            'tour' => [
                'header' => 'Five protocols to try in the browser',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'A2UI',
                        'title' => 'The agent builds the form, TYPO3 renders it',
                        'description' => 'One sentence in, a working form out. TYPO3 draws only components from its <strong>trusted catalogue</strong> and drops everything else.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-agent-nexus-a2ui-form-465cca4b.webp', 'The agent builds the form, TYPO3 renders it', 'A2UI widget on a TYPO3 page with the request for a project quote and the form the agent generated from it.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'AG-UI',
                        'title' => 'Watch the agent work, then approve',
                        'description' => 'The run arrives as a <strong>stream of typed events</strong>. Before anything is sent, the assistant stops and waits for your approval.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-agent-nexus-agui-approval-9fd08d63.webp', 'Watch the agent work, then approve', 'AG-UI assistant recommending the Team plan, with a plan comparison, an approval card and the live list of protocol events.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'UCP',
                        'title' => 'A shopping agent that waits for your OK',
                        'description' => 'The agent reads the shop profile and builds a priced cart from the <strong>real catalogue</strong>. Nothing is ordered until a person approves.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-agent-nexus-ucp-checkout-7716e2bb.webp', 'A shopping agent that waits for your OK', 'UCP checkout demo with the shopping agent\'s steps, a cart for a Pro licence and an Approve this order box.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Traffic log',
                        'title' => 'Every request, response and event on record',
                        'description' => 'The <strong>traffic log</strong> lists each exchange with caller, status, timing and events. Personal data is masked before it is stored.',
                        'image' => ShowcaseBlocks::screenshot('feature-agent-nexus-traffic-4e27c05e.webp', 'Every request, response and event on record', 'Agent Nexus traffic log in TYPO3, filtered to the frontend widgets, with the latest A2UI, AG-UI and UCP requests, status codes, durations and event counts.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Agent Nexus',
                'items' => [
                    ['icon' => 'globe', 'title' => 'Current specs, tested', 'description' => 'A2A 1.0, AG-UI 1.0, A2UI v0.9.1, UCP 2026-08-25 and AP2 v0.2.0. Every payload is tested against its official JSON Schema.'],
                    ['icon' => 'shield-check', 'title' => 'A person approves every write', 'description' => 'AG-UI stops for approval, UCP completes a checkout only after the visitor agrees, and AP2 verifies signed mandates. Payments are always simulated.'],
                    ['icon' => 'history', 'title' => 'Every request on record', 'description' => 'The traffic log keeps each request, response and streamed event. Names, email addresses, phone numbers and postal addresses are masked first.'],
                    ['icon' => 'monitor', 'title' => 'Widgets editors can place', 'description' => 'Each protocol has a content element with its live demo, plus a hub and an info element. They take neutral colours from your shadcn theme.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'Do we need a language model?', 'answer' => 'No. Without nr_llm, every protocol runs a deterministic script, the same one the tests check. With nr_llm, you switch the model on per protocol and set a daily budget.'],
                    ['question' => 'Is it safe on a public site?', 'answer' => 'The endpoints are public by design, rate-limited per client and validated. Keep the two \'really apply\' switches off, set a storage folder, limit who opens the inspector and schedule the cleanup command.'],
                    ['question' => 'What does it need?', 'answer' => 'TYPO3 v14.3.7 or later and PHP 8.4 with ext-openssl, plus fluid_styled_content. Add the GitHub repository to Composer and run extension:setup. One command, agentnexus:seed-site, builds the whole demo site.'],
                    ['question' => 'How is it licensed?', 'answer' => 'Agent Nexus is GPL-2.0-or-later. The official specification schemas used by its tests keep their own licences, Apache-2.0 and MIT, stated next to each set.'],
                ],
            ],
            'install' => ['header' => 'Install Agent Nexus', 'code' => implode("\n", ['composer config repositories.agent-nexus vcs https://github.com/dirnbauer/typo3-agent-nexus.git', 'composer require webconsulting/agent-nexus:^4.0', 'vendor/bin/typo3 extension:setup', 'vendor/bin/typo3 cache:flush', '# Optional: build the demo site', 'vendor/bin/typo3 agentnexus:seed-site --base=https://example.com/'])],
            'cta' => ['header' => 'Show your next client a working agent', 'description' => 'Install Agent Nexus, run the seed command and open the five demos. Every checkout and payment stays simulated.', 'text' => 'Get Agent Nexus', 'link' => 'https://github.com/dirnbauer/typo3-agent-nexus'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function agentation(): array
    {
        return [
            'slug' => 'agentation',
            'product' => 'Agentation',
            'badge' => 'Agentation',
            'title' => 'Agentation: show coding agents what to fix',
            'navTitle' => 'Visual feedback',
            'description' => 'Click an element on a TYPO3 page or backend screen and write a note. Your coding agent gets it over MCP with selector, page and computed styles.',
            'abstract' => 'With Agentation, backend users click an element in the TYPO3 frontend or backend and write a note. Claude Code, Cursor or another MCP agent gets it with selector and styles.',
            'hero' => [
                'header' => 'Show your coding agent exactly what to fix',
                'subheadline' => 'Click an element on a TYPO3 page or backend screen and write a note. Your coding agent receives it with the selector, page and computed styles.',
                'primaryButton' => ['text' => 'Get Agentation', 'link' => 'https://github.com/dirnbauer/typo3-agentation'],
                'secondaryButton' => ['text' => 'Read the manual', 'link' => 'https://github.com/dirnbauer/typo3-agentation/tree/main/Documentation'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-agentation-frontend-note-dbfbbbf8.webp', 'Notes pinned to the exact element', 'The home page with the Agentation toolbar, two numbered notes on the headline and main button, and a third note being written.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'Feedback gets lost before it reaches the agent', 'content' => '<p>Clients and editors describe problems in words, like the button on the right or the heading that looks off. Your coding agent has to guess the element and the file. Screenshots in a chat lose the selector and the styles.</p>'],
            'tour' => [
                'header' => 'Point, write, send it to your agent',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'Backend',
                        'title' => 'Feedback on backend screens as well',
                        'description' => 'The same toolbar runs inside <strong>backend modules</strong>, so editors can mark up the page module, record forms or your own modules.',
                        'image' => ShowcaseBlocks::screenshot('feature-agentation-backend-note-63e8d068.webp', 'Feedback on backend screens as well', 'TYPO3 page module with the Agentation toolbar and a numbered note attached to a content element preview.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Setup',
                        'title' => 'Connect your coding agent in one step',
                        'description' => 'Copy the <strong>MCP configuration</strong> or the claude mcp add command, check the status and manage stored notes in one module.',
                        'image' => ShowcaseBlocks::screenshot('feature-agentation-module-e37c0aeb.webp', 'Connect your coding agent in one step', 'System > Agentation module with the MCP server configuration, a Claude Code command and status checks.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with Agentation',
                'items' => [
                    ['icon' => 'message-circle', 'title' => 'Point instead of describe', 'description' => 'Click an element or select text and write a note. The toolbar records the selector, the page and the computed styles.'],
                    ['icon' => 'handshake', 'title' => 'Works with your coding agent', 'description' => 'Claude Code, Cursor, Windsurf, Zed, Continue or any other MCP agent. Copy the configuration, the claude mcp add command or the Cursor link.'],
                    ['icon' => 'lock', 'title' => 'Invisible to your visitors', 'description' => 'Only logged-in backend users who switch it on see the toolbar. By default it loads only in the Development context, never in production.'],
                    ['icon' => 'shield', 'title' => 'Keys stay on the server', 'description' => 'Same-origin proxies let HTTPS pages reach a local agentation-mcp server. An Agentation API key is added on the server and never reaches the browser.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'Can visitors see the toolbar?', 'answer' => 'No. It needs a backend login, a per-user switch and, on the frontend, the Admin Panel option. Pages that carry the toolbar are never cached, so no visitor receives one.'],
                    ['question' => 'What does it need?', 'answer' => 'TYPO3 v14.3 with the Admin Panel and PHP 8.4. The toolbar bundle is built and committed, so your installation needs no Node.js. Add the GitHub repository to Composer and require the package.'],
                    ['question' => 'Where are the notes stored?', 'answer' => 'On an agentation-mcp server that your coding agent starts on your computer, or in the Agentation cloud when you set an API key. System > Agentation lists stored notes and deletes them.'],
                    ['question' => 'How is it licensed?', 'answer' => 'The TYPO3 extension is GPL-2.0-or-later. It bundles the upstream Agentation toolbar, which has its own licence, PolyForm Shield 1.0.0. Check that it fits how you use it.'],
                ],
            ],
            'install' => ['header' => 'Install Agentation', 'code' => implode("\n", ['composer config repositories.agentation vcs https://github.com/dirnbauer/typo3-agentation.git', 'composer require webconsulting/agentation:^1.5', '# Register the MCP server with your coding agent, for example Claude Code:', 'claude mcp add agentation -- npx -y agentation-mcp server'])],
            'cta' => ['header' => 'Turn page feedback into precise tasks', 'description' => 'Install Agentation, register the MCP server with your agent and switch the toolbar on in User Settings. Your next note arrives with its selector.', 'text' => 'Get Agentation', 'link' => 'https://github.com/dirnbauer/typo3-agentation'],
        ];
    }

    /**
     * @return FeatureDefinition
     */
    private static function llmsTxt(): array
    {
        return [
            'slug' => 'llms-txt',
            'product' => 'llms.txt',
            'badge' => 'llms.txt',
            'title' => 'llms.txt: your site explained to AI agents',
            'navTitle' => 'AI discovery',
            'description' => 'Serve /llms.txt and /agents.md for every TYPO3 site and language. Both come from your page tree, so AI agents find your content and interfaces.',
            'abstract' => 'Every TYPO3 site and language serves an llms.txt and an agents.md, built from the page tree and site configuration. AI agents learn what the site offers and how to use it.',
            'hero' => [
                'header' => 'Tell AI agents what your site offers',
                'subheadline' => 'Every TYPO3 site and language gets an llms.txt and an agents.md, built from the page tree. Edit a page description, and the next request shows it.',
                'primaryButton' => ['text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-llms-txt'],
                'secondaryButton' => ['text' => 'Open llms.txt', 'link' => '/llms.txt'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-llms-txt-llms-05e929b5.webp', 'Your site as a map for AI agents', 'The llms.txt of the lab site: site title, summary, a pointer to agents.md and link lists with descriptions per section.', 'Live screenshot from the TYPO3 v14 lab.'),
            ],
            'problem' => ['header' => 'AI tools guess what your site is about', 'content' => '<p>AI answers and browser agents read your site without a map. They scrape menus, miss key pages and never learn about your API. A hand-written llms.txt goes stale with the next new page.</p>'],
            'tour' => [
                'header' => 'What AI agents read about your site',
                'subheadline' => 'Live screenshots from this TYPO3 v14 lab.',
                'shots' => [
                    [
                        'tab' => 'agents.md',
                        'title' => 'An operating guide for agents',
                        'description' => 'agents.md lists the <strong>machine interfaces your installation really has</strong>: MCP server, abilities with risk tiers, sitemap and paid content.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-llms-txt-agents-md-75272488.webp', 'An operating guide for agents', 'The agents.md of the lab site: MCP endpoint, abilities registry with REST and CLI access, and abilities with risk tiers.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Per language',
                        'title' => 'One file for every language',
                        'description' => 'Each language base serves its own llms.txt, with <strong>translated titles, descriptions and URLs</strong> from the site\'s router.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-llms-txt-per-language-fc6c28f6.webp', 'One file for every language', 'German llms.txt of the lab site with a German summary, German section titles and links to /de/ pages.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                    [
                        'tab' => 'Page fields',
                        'title' => 'Editors steer it through page properties',
                        'description' => 'The root page\'s <strong>description becomes the summary</strong>, and each page\'s title and description become its link. No extra fields to fill.',
                        'image' => ShowcaseBlocks::screenshot('feature-llms-txt-source-fields-d15e6c67.webp', 'Editors steer it through page properties', 'TYPO3 page properties of the root page, SEO tab, with the description that llms.txt prints as its summary.', 'Live screenshot from the TYPO3 v14 lab.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What you get with llms.txt',
                'items' => [
                    ['icon' => 'file-text', 'title' => 'Two files, no upkeep', 'description' => 'llms.txt lists your content, and agents.md explains your machine interfaces. Both are built on request from the page tree and site configuration.'],
                    ['icon' => 'globe', 'title' => 'One file per site and language', 'description' => 'Every site and language base serves its own files, with URLs from the site\'s router. Hidden, timed-out and noindex pages stay out.'],
                    ['icon' => 'settings', 'title' => 'Lists only what you have', 'description' => 'agents.md names the MCP server, the abilities registry, the sitemap and paid content only when those extensions are installed.'],
                    ['icon' => 'search', 'title' => 'Agents find it from any page', 'description' => 'Every page of the site sends a Link header that points to its llms.txt, as the proposal recommends. Both files carry noindex.'],
                ],
            ],
            'faq' => [
                'header' => 'Questions before you install',
                'subheadline' => 'Setup, requirements, licence and data.',
                'items' => [
                    ['question' => 'Do we have to configure anything?', 'answer' => 'No. After installation, every site serves both files. Two optional site settings switch a site off or add page types, and an optional site set makes them editable in the backend.'],
                    ['question' => 'Which pages appear?', 'answer' => 'Visible first-level pages become sections, with their visible children as links, up to 25 per section. Hidden, timed-out, noindex and nav-hidden pages never appear.'],
                    ['question' => 'What does it need?', 'answer' => 'TYPO3 v14.3 with PHP 8.4 and EXT:seo, which adds the SEO title and noindex fields. The MCP server and the abilities registry are optional; agents.md mentions them only when installed.'],
                    ['question' => 'What licence, and what does it store?', 'answer' => 'It is GPL-2.0-or-later, like TYPO3. It adds no database tables and writes nothing to disk, because both files are generated for each request.'],
                ],
            ],
            'install' => ['header' => 'Install llms.txt', 'code' => implode("\n", ['composer config repositories.typo3-llms-txt vcs https://github.com/dirnbauer/typo3-llms-txt.git', 'composer require webconsulting/typo3-llms-txt:^1.2', 'vendor/bin/typo3 extension:setup --extension=llms_txt', '# Preview what a site serves', 'vendor/bin/typo3 llmstxt:dump <site-identifier> llms.txt'])],
            'cta' => ['header' => 'Publish a site guide for AI agents', 'description' => 'Install the extension and open /llms.txt on your site. Your page titles and descriptions do the rest.', 'text' => 'Get the extension', 'link' => 'https://github.com/dirnbauer/typo3-llms-txt'],
        ];
    }
}
