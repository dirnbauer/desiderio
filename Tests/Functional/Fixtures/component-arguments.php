<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Minimal argument set (and optional slot content) per Fluid component, used
 * by ComponentRenderingTest to prove every component renders with Fluid 5's
 * strict argument validation. Components not listed here render with no
 * arguments; add an entry when a component gains a required argument.
 *
 * Argument values may be closures; they are invoked inside the booted test
 * so TYPO3 objects can be constructed. `skip` documents components that
 * need infrastructure the render smoke test does not provide (FAL files, a
 * frontend request) — those are still parsed by ShippedTemplatesLintTest.
 *
 * @return array<string, array{arguments?: array<string, mixed>, slots?: array<string, string>, skip?: string}>
 */
$site = static fn(): Site => new Site('main', 1, [
    'base' => '/',
    'websiteTitle' => 'Desiderio',
    'languages' => [['languageId' => 0, 'title' => 'English', 'locale' => 'en_US.UTF-8', 'base' => '/']],
    'settings' => ['desiderio' => ['brand' => ['wordmark' => 'Desiderio'], 'theme' => ['darkModeToggle' => true, 'darkModeDefault' => 'system'], 'search' => ['enabled' => false], 'footer' => ['copyrightText' => '']]],
]);

return [
    'atom.icon' => ['arguments' => ['name' => 'check']],
    'atom.image' => ['arguments' => ['src' => '/typo3conf/ext/desiderio/Resources/Public/Icons/Extension.svg', 'alt' => 'Desiderio']],
    'atom.button' => ['slots' => ['default' => 'Save']],
    'atom.link' => ['arguments' => ['href' => '/'], 'slots' => ['default' => 'Home']],
    'atom.badge' => ['slots' => ['default' => 'New']],
    'atom.typography' => ['slots' => ['default' => 'Text']],
    'atom.label' => ['slots' => ['default' => 'Label']],
    'atom.controlClass' => ['arguments' => ['slot' => 'input']],
    'molecule.accordionItem' => ['arguments' => ['trigger' => 'Question'], 'slots' => ['default' => 'Answer']],
    'molecule.tabsTrigger' => ['arguments' => ['value' => 'tab-1', 'id' => 'tab-1-trigger', 'controls' => 'tab-1-panel'], 'slots' => ['default' => 'Tab']],
    'molecule.tabsContent' => ['arguments' => ['value' => 'tab-1', 'id' => 'tab-1-panel', 'labelledBy' => 'tab-1-trigger'], 'slots' => ['default' => 'Panel']],
    'molecule.fieldLabel' => ['arguments' => ['for' => 'field-1'], 'slots' => ['default' => 'Label']],
    'molecule.checkedListItem' => ['slots' => ['default' => 'Included']],
    'molecule.facetCheckbox' => ['arguments' => ['id' => 'd-facet-type-pages', 'name' => 'facet-type', 'value' => 'pages', 'label' => 'Pages', 'targetUrl' => '/search?q=typo3', 'count' => 69, 'checked' => true, 'countLabel' => '69 results']],
    'molecule.sectionIntro' => ['arguments' => ['eyebrow' => 'Eyebrow', 'heading' => 'Heading', 'lead' => 'Lead']],
    'molecule.actionGroup' => ['slots' => ['default' => '<a href="/">Go</a>']],
    'molecule.featureItem' => ['arguments' => ['icon' => 'check', 'title' => 'Title', 'text' => 'Text']],
    'molecule.stat' => ['arguments' => ['value' => '42', 'label' => 'Answers', 'trend' => 'up', 'delta' => '+3']],
    'molecule.figure' => ['skip' => 'needs a FAL FileReference; parsed by ShippedTemplatesLintTest'],
    'molecule.pagination' => ['skip' => 'f:uri.action needs an Extbase request; rendered only inside the News and Blog plugins'],
    'molecule.formRenderer' => ['skip' => 'f:cObject needs a frontend request; parsed by ShippedTemplatesLintTest'],
    'organism.breadcrumb' => ['arguments' => ['breadcrumb' => []]],
    'organism.pageHeader' => ['arguments' => ['page' => static fn(): object => (object)['pageRecord' => ['uid' => 1, 'title' => 'Home', 'subtitle' => '', 'tx_desiderio_h1_sronly' => 0]]]],
    'organism.siteHeader' => ['arguments' => ['site' => $site, 'breadcrumb' => []]],
    'organism.siteFooter' => ['arguments' => ['site' => $site, 'breadcrumb' => []]],
];
