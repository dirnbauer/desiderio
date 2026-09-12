<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Components;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Components\ComponentCollection;

/**
 * Fluid 5 validates component arguments strictly: an unknown or missing
 * required argument throws at parse time. This renders every component of the
 * collection once with the minimal argument set from
 * Fixtures/component-arguments.php, so a renamed <f:argument> or a new
 * required argument fails here instead of on a live page.
 */
final class ComponentRenderingTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['form', 'workspaces'];

    protected array $testExtensionsToLoad = [
        'friendsoftypo3/content-blocks',
        'friendsoftypo3/visual-editor',
        'webconsulting/visual-editor-enhancements',
        'webconsulting/desiderio',
    ];

    /**
     * Data providers run before TYPO3 is bootstrapped, so the component list
     * comes straight from the directory layout the collection maps
     * (Layer/Name/Name.fluid.html => layer.name).
     *
     * @return iterable<string, array{string, array<string, mixed>, array<string, string>, string|null}>
     */
    public static function componentProvider(): iterable
    {
        $fixtures = require __DIR__ . '/../Fixtures/component-arguments.php';
        self::assertIsArray($fixtures);
        $components = self::componentsOnDisk();
        self::assertNotSame([], $components);
        foreach ($components as $component) {
            $fixture = $fixtures[$component] ?? [];
            self::assertIsArray($fixture);
            $arguments = $fixture['arguments'] ?? [];
            $slots = $fixture['slots'] ?? [];
            $skip = $fixture['skip'] ?? null;
            self::assertIsArray($arguments);
            self::assertIsArray($slots);
            /** @var array<string, mixed> $arguments */
            /** @var array<string, string> $slots */
            yield $component => [$component, $arguments, $slots, is_string($skip) ? $skip : null];
        }
    }

    /**
     * @return list<string>
     */
    private static function componentsOnDisk(): array
    {
        $files = glob(__DIR__ . '/../../../Resources/Private/Components/*/*/*.fluid.html');
        self::assertIsArray($files);
        $components = [];
        foreach ($files as $file) {
            $name = basename($file, '.fluid.html');
            $directory = dirname($file);
            if (basename($directory) !== $name) {
                continue;
            }
            $components[] = lcfirst(basename(dirname($directory))) . '.' . lcfirst($name);
        }
        sort($components);
        return $components;
    }

    /**
     * @param array<string, mixed> $arguments
     * @param array<string, string> $slots
     */
    #[Test]
    #[DataProvider('componentProvider')]
    public function componentRendersWithMinimalArguments(string $component, array $arguments, array $slots, ?string $skip): void
    {
        if ($skip !== null) {
            self::markTestSkipped($component . ': ' . $skip);
        }
        $arguments = array_map(static fn(mixed $value): mixed => $value instanceof \Closure ? $value() : $value, $arguments);
        $source = $this->callSite($component, $arguments, $slots);
        $renderingContext = $this->get(RenderingContextFactory::class)->create([], $this->frontendRequest());
        $view = new TemplateView($renderingContext);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->assignMultiple($arguments);

        $rendered = $view->render();

        self::assertIsString($rendered);
    }

    #[Test]
    public function fixtureOnlyDescribesExistingComponents(): void
    {
        $fixtures = require __DIR__ . '/../Fixtures/component-arguments.php';
        self::assertIsArray($fixtures);
        $components = (new ComponentCollection())->getAvailableComponents();
        sort($components);

        self::assertSame(self::componentsOnDisk(), $components, 'The collection must expose exactly the components on disk');
        self::assertSame([], array_values(array_diff(array_keys($fixtures), $components)), 'component-arguments.php lists components that do not exist');
    }

    /**
     * The link and translate ViewHelpers a component may use need a frontend
     * request with a site and language; this is the smallest one that works.
     */
    private function frontendRequest(): ServerRequest
    {
        $site = new Site('main', 1, [
            'base' => 'https://example.com/',
            'websiteTitle' => 'Desiderio',
            'languages' => [['languageId' => 0, 'title' => 'English', 'locale' => 'en_US.UTF-8', 'base' => '/']],
        ]);
        return (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getDefaultLanguage());
    }

    /**
     * Builds a template calling the component; scalar arguments are passed as
     * template variables so arrays/objects survive the round trip.
     *
     * @param array<string, mixed> $arguments
     * @param array<string, string> $slots
     */
    private function callSite(string $component, array $arguments, array $slots): string
    {
        $attributes = '';
        foreach (array_keys($arguments) as $name) {
            $attributes .= sprintf(' %s="{%s}"', $name, $name);
        }
        $body = '';
        foreach ($slots as $slot => $content) {
            $body .= $slot === 'default' ? $content : sprintf('<f:fragment name="%s">%s</f:fragment>', $slot, $content);
        }
        return sprintf(
            '<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection" data-namespace-typo3-fluid="true">'
            . '<d:%1$s%2$s>%3$s</d:%1$s></html>',
            $component,
            $attributes,
            $body,
        );
    }
}
