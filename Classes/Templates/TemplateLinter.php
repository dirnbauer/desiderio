<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\TemplateLocation;
use TYPO3Fluid\Fluid\Core\Parser\UnknownNamespaceException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\TemplateLocationException;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;
use TYPO3Fluid\Fluid\Core\ViewHelper\UndeclaredArgumentException;

/**
 * Static gate for the Fluid 5 templates shipped by an extension.
 *
 * Fluid 5 validates ViewHelper and component arguments at parse time and
 * throws on unknown ViewHelpers, so a template that renders in the browser
 * today can break silently when a ViewHelper argument is renamed or a
 * component gains a required argument. The linter parses every template with
 * the same RenderingContext TYPO3 uses at runtime and reports what the parser
 * would throw, plus a handful of static checks the parser cannot do — see
 * LintRule for the list.
 *
 * Third-party ViewHelper namespaces (news, powermail, solr, …) whose extension
 * is not installed are ignored and reported as "skipped"; --strict makes them
 * errors.
 */
final class TemplateLinter
{
    private const string CANONICAL_FLUID_URI = 'http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers';

    /** Directory names never scanned when a directory is given. */
    private const array SKIPPED_DIRECTORIES = [
        '.git', '.ddev', '.idea', '.phpunit.cache', 'node_modules', 'vendor', 'var', 'public', 'Public',
        'Build', 'Tests', 'Documentation',
    ];

    /**
     * Parser errors that are a consequence of an earlier error: an
     * unresolvable opening tag leaves its closing tag orphaned.
     */
    private const array CASCADE_MESSAGES = [
        'You closed a templating tag which you never opened',
        'Not all tags were closed',
        'Templating tags not properly nested',
    ];

    private const string CASCADE_MARKER = '[cascade] ';

    private const array DEPRECATED_CONSTRUCTS = [
        '/(?<!\\\\)\{namespace\s/' => 'The {namespace} declaration is deprecated; declare ViewHelper namespaces with xmlns: attributes on the root tag',
        '/(?:<\/?|\{|->\s*)f:widget\./' => 'f:widget.* ViewHelpers were removed with TYPO3 v12; use pagination partials or components',
        '/(?:<\/?|\{|->\s*)f:be\./' => 'f:be.* ViewHelpers are backend-only and removed in TYPO3 v13; use core:* or plain markup',
        '/(?:<\/?|\{|->\s*)f:security\./' => 'f:security.* ViewHelpers are deprecated; use the request/user objects or conditions',
        '/(?:<\/?|\{|->\s*)f:base(?![A-Za-z])/' => 'f:base was removed with TYPO3 v12; set the base tag in page TypoScript',
    ];

    /** @var array<string, bool> */
    private array $namespaceAvailability = [];

    /** @var array<string, list<string>> */
    private array $availableComponents = [];

    public function __construct(
        private readonly RenderingContextFactory $renderingContextFactory,
        private readonly PackageManager $packageManager,
        private readonly FluidCallParser $callParser = new FluidCallParser(),
        private readonly PartialRootResolver $partialRoots = new PartialRootResolver(),
    ) {}

    public function lint(LintOptions $options): LintReport
    {
        $report = new LintReport();
        foreach ($options->paths as $path) {
            foreach ($this->collectTemplateFiles($path) as $displayPath => $file) {
                $report->countFile();
                $this->lintFile($file, $displayPath, $options, $report);
            }
        }
        return $report;
    }

    /**
     * @return array<string, string> display path => absolute path
     */
    private function collectTemplateFiles(string $path): array
    {
        $resolved = $this->resolvePath($path);
        if ($resolved === null) {
            throw new \InvalidArgumentException('Path does not exist: ' . $path, 1757600001);
        }
        $display = rtrim($path, '/');
        if (is_file($resolved)) {
            return [$display => $resolved];
        }
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($resolved, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS),
                static fn(\SplFileInfo $current): bool => !$current->isDir() || !in_array($current->getFilename(), self::SKIPPED_DIRECTORIES, true),
            ),
        );
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo instanceof \SplFileInfo || !$fileInfo->isFile() || !$this->isTemplateFile($fileInfo->getFilename())) {
                continue;
            }
            $absolute = $fileInfo->getPathname();
            $files[$display . '/' . ltrim(substr($absolute, strlen($resolved)), '/')] = $absolute;
        }
        ksort($files);
        return $files;
    }

    private function isTemplateFile(string $fileName): bool
    {
        return str_ends_with($fileName, '.html') || str_ends_with($fileName, '.fluid.txt');
    }

    private function resolvePath(string $path): ?string
    {
        if (str_starts_with($path, 'EXT:')) {
            [$extensionKey, $relative] = explode('/', substr($path, 4), 2) + [1 => ''];
            if (!$this->packageManager->isPackageActive($extensionKey)) {
                return null;
            }
            $absolute = $this->packageManager->getPackage($extensionKey)->getPackagePath() . $relative;
        } elseif (str_starts_with($path, '/')) {
            $absolute = $path;
        } else {
            $absolute = getcwd() . '/' . $path;
        }
        $absolute = rtrim($absolute, '/');
        if ($absolute === '' || !file_exists($absolute)) {
            return null;
        }
        $real = realpath($absolute);
        return $real === false ? $absolute : $real;
    }

    private function lintFile(string $file, string $displayPath, LintOptions $options, LintReport $report): void
    {
        $source = (string)file_get_contents($file);
        $scanSource = $this->callParser->stripComments($source);
        /** @var list<LintFinding> $findings */
        $findings = [];

        if ($options->isRuleEnabled(LintRule::DeprecatedConstructs)) {
            $findings = [...$findings, ...$this->checkDeprecatedConstructs($displayPath, $scanSource)];
        }

        $renderingContext = $this->renderingContextFactory->create();
        $resolver = $renderingContext->getViewHelperResolver();
        $globalPrefixes = array_keys($resolver->getNamespaces());
        $declared = $this->callParser->declaredNamespaces($source);
        $used = $this->callParser->usedPrefixes($scanSource);

        if ($options->isRuleEnabled(LintRule::NamespaceUsage)) {
            $findings = [...$findings, ...$this->checkNamespaceUsage($displayPath, $declared, $used, $globalPrefixes)];
        }

        // Third-party namespaces whose extension is not installed cannot be
        // resolved. Outside --strict they are ignored for this file (Fluid keeps
        // their tags as text), so every other namespace is still fully checked.
        $unavailable = [];
        $parseSource = $source;
        foreach ($declared as $prefix => $declaration) {
            if ($declaration['php'] === null || in_array($prefix, $globalPrefixes, true) || $this->isPhpNamespaceAvailable($declaration['php'])) {
                continue;
            }
            $unavailable[$prefix] = $declaration['php'];
            $findings[] = new LintFinding(
                $displayPath,
                LintRule::Parse,
                $options->strict ? LintSeverity::Error : LintSeverity::Skipped,
                sprintf(
                    'ViewHelper namespace "%s" (%s) is not installed; %s',
                    $prefix,
                    $declaration['php'],
                    $options->strict ? 'its ViewHelpers cannot be resolved' : 'calls of this prefix were not checked',
                ),
                $declaration['line'],
            );
            if (!$options->strict) {
                $parseSource = (string)preg_replace('/\s+xmlns:' . preg_quote($prefix, '/') . '\s*=\s*(?:"[^"]*"|\'[^\']*\')/', '', $parseSource, 1);
                $resolver->addNamespace($prefix, null);
            }
        }

        if ($options->isRuleEnabled(LintRule::ComponentArguments)) {
            $findings = [...$findings, ...$this->checkComponentArguments($displayPath, $scanSource, $declared, $renderingContext)];
        }

        if ($options->isRuleEnabled(LintRule::Parse)) {
            $findings = [...$findings, ...$this->parseTemplate($displayPath, $file, $parseSource, $renderingContext, $unavailable, $options->strict, $options->isRuleEnabled(LintRule::ComponentArguments))];
        }

        if ($options->isRuleEnabled(LintRule::PartialResolves)) {
            $findings = [...$findings, ...$this->checkPartials($displayPath, $file, $scanSource, $options->strict)];
        }

        $this->report($findings, $report);
    }

    /**
     * Orphaned closing tags follow from an earlier unresolvable opening tag;
     * report them only when nothing else explains them, and never report the
     * same rule/message twice for one file.
     *
     * @param list<LintFinding> $findings
     */
    private function report(array $findings, LintReport $report): void
    {
        $hasPrimaryError = array_any(
            $findings,
            static fn(LintFinding $finding): bool => $finding->severity === LintSeverity::Error && !str_starts_with($finding->message, self::CASCADE_MARKER),
        );
        $seen = [];
        foreach ($findings as $finding) {
            if (str_starts_with($finding->message, self::CASCADE_MARKER)) {
                if ($hasPrimaryError) {
                    continue;
                }
                $finding = new LintFinding($finding->file, $finding->rule, $finding->severity, substr($finding->message, strlen(self::CASCADE_MARKER)), $finding->line);
            }
            $key = $finding->rule->value . '|' . $finding->message;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $report->addFinding($finding);
        }
    }

    // ---------------------------------------------------------------- parse

    /**
     * @param array<string, string> $unavailable prefix => PHP namespace
     * @return list<LintFinding>
     */
    private function parseTemplate(string $displayPath, string $file, string $source, RenderingContextInterface $renderingContext, array $unavailable, bool $strict, bool $componentArgumentsChecked): array
    {
        $errorHandler = new CollectingErrorHandler();
        $renderingContext->setErrorHandler($errorHandler);
        $collected = [];
        try {
            $renderingContext->getTemplateParser()->parse($source, 'lint_' . sha1($file), $file);
        } catch (\Throwable $throwable) {
            $collected[] = $throwable;
        }
        $collected = [...$errorHandler->getErrors(), ...$collected];

        $findings = [];
        foreach ($collected as $throwable) {
            $finding = $this->classifyParserError($displayPath, $throwable, $unavailable, $strict, $componentArgumentsChecked);
            if ($finding !== null) {
                $findings[] = $finding;
            }
        }
        return $findings;
    }

    /**
     * @param array<string, string> $unavailable
     */
    private function classifyParserError(string $displayPath, \Throwable $throwable, array $unavailable, bool $strict, bool $componentArgumentsChecked): ?LintFinding
    {
        $line = $throwable instanceof TemplateLocationException ? $this->lineOf($throwable->getTemplateLocation()) : null;
        // TemplateParser::parse() rethrows the original exception wrapped in a
        // "Fluid parse error in template …" message of the same class and code.
        $previous = $throwable->getPrevious();
        if ($previous !== null && str_starts_with($throwable->getMessage(), 'Fluid parse error in template')) {
            $throwable = $previous;
        }
        $message = $this->firstLine($throwable->getMessage());
        $code = $throwable->getCode();

        if ($throwable instanceof UnknownNamespaceException) {
            $prefix = trim((string)preg_replace('/^Unknown Namespace:\s*/i', '', $message));
            return new LintFinding($displayPath, LintRule::NamespaceUsage, LintSeverity::Error, sprintf('Prefix "%s" is used but neither declared with xmlns:%s nor registered globally', $prefix, $prefix), $line);
        }
        if ($throwable instanceof InvalidArgumentValueException && $code === 1748903732) {
            // The static component-arguments rule already reported this call site with its line.
            return $componentArgumentsChecked ? null : new LintFinding($displayPath, LintRule::ComponentArguments, LintSeverity::Error, $message, $line);
        }
        if ($throwable instanceof UndeclaredArgumentException) {
            return new LintFinding($displayPath, LintRule::Parse, LintSeverity::Error, 'Undeclared ViewHelper argument: ' . (string)preg_replace('/^Undeclared arguments passed to ViewHelper\s*/', '', $message), $line);
        }
        if ($throwable instanceof ParserException && $code === 1407060572 && preg_match('/<([A-Za-z0-9.]+):([A-Za-z0-9.]+)>/', $message, $match) === 1) {
            [, $prefix, $name] = $match;
            if (isset($unavailable[$prefix])) {
                // Strict mode keeps the namespace registered, so every call resolves to this error.
                return $strict
                    ? new LintFinding($displayPath, LintRule::Parse, LintSeverity::Error, sprintf('Unknown ViewHelper <%s:%s> (namespace %s is not installed)', $prefix, $name, $unavailable[$prefix]), $line)
                    : null;
            }
            if (str_contains($throwable->getMessage(), 'component template')) {
                return $componentArgumentsChecked ? null : new LintFinding($displayPath, LintRule::ComponentArguments, LintSeverity::Error, sprintf('Unknown component <%s:%s>: no component template found in the collection', $prefix, $name), $line);
            }
            return new LintFinding($displayPath, LintRule::Parse, LintSeverity::Error, sprintf('Unknown ViewHelper <%s:%s>', $prefix, $name), $line);
        }
        foreach (self::CASCADE_MESSAGES as $cascade) {
            if (str_contains($message, $cascade)) {
                return new LintFinding($displayPath, LintRule::Parse, LintSeverity::Error, self::CASCADE_MARKER . $message, $line);
            }
        }
        return new LintFinding(
            $displayPath,
            LintRule::Parse,
            LintSeverity::Error,
            sprintf('%s: %s', new \ReflectionClass($throwable)->getShortName(), $message),
            $line,
        );
    }

    private function lineOf(TemplateLocation $location): ?int
    {
        return $location->line > 1 || $location->identifierOrPath !== '' ? $location->line : null;
    }

    // ------------------------------------------------------ namespace-usage

    /**
     * @param array<string, array{uri: string, php: string|null, line: int}> $declared
     * @param array<string, int> $used prefix => first line
     * @param list<string> $globalPrefixes
     * @return list<LintFinding>
     */
    private function checkNamespaceUsage(string $displayPath, array $declared, array $used, array $globalPrefixes): array
    {
        $findings = [];
        foreach ($used as $prefix => $line) {
            if (isset($declared[$prefix]) || in_array($prefix, $globalPrefixes, true)) {
                continue;
            }
            $findings[] = new LintFinding($displayPath, LintRule::NamespaceUsage, LintSeverity::Error, sprintf('Prefix "%s" is used but neither declared with xmlns:%s nor registered globally', $prefix, $prefix), $line);
        }
        foreach ($declared as $prefix => $declaration) {
            if (!isset($used[$prefix])) {
                $findings[] = new LintFinding($displayPath, LintRule::NamespaceUsage, LintSeverity::Warning, sprintf('Namespace prefix "%s" is declared but never used', $prefix), $declaration['line']);
            }
            if ($prefix === 'f' && $declaration['uri'] !== self::CANONICAL_FLUID_URI) {
                $findings[] = new LintFinding($displayPath, LintRule::NamespaceUsage, LintSeverity::Warning, sprintf('xmlns:f declares "%s"; the canonical TYPO3 URI is %s', $declaration['uri'], self::CANONICAL_FLUID_URI), $declaration['line']);
            }
        }
        return $findings;
    }

    /**
     * A declared ViewHelper namespace is available when a class of that name
     * exists (component collections) or an active package autoloads it.
     */
    private function isPhpNamespaceAvailable(string $phpNamespace): bool
    {
        if (isset($this->namespaceAvailability[$phpNamespace])) {
            return $this->namespaceAvailability[$phpNamespace];
        }
        $probe = rtrim($phpNamespace, '\\') . '\\';
        $available = class_exists($phpNamespace);
        if (!$available) {
            foreach ($this->packageManager->getActivePackages() as $package) {
                $autoload = $package->getValueFromComposerManifest('autoload');
                $psr4 = is_object($autoload) && isset($autoload->{'psr-4'}) && (is_array($autoload->{'psr-4'}) || is_object($autoload->{'psr-4'}))
                    ? (array)$autoload->{'psr-4'}
                    : [];
                foreach (array_keys($psr4) as $prefix) {
                    if (is_string($prefix) && str_starts_with($probe, $prefix)) {
                        $available = true;
                        break 2;
                    }
                }
            }
        }
        $this->namespaceAvailability[$phpNamespace] = $available;
        return $available;
    }

    // ------------------------------------------------- component-arguments

    /**
     * @param array<string, array{uri: string, php: string|null, line: int}> $declared
     * @return list<LintFinding>
     */
    private function checkComponentArguments(string $displayPath, string $source, array $declared, RenderingContextInterface $renderingContext): array
    {
        $findings = [];
        foreach ($declared as $prefix => $declaration) {
            $className = $declaration['php'];
            if ($className === null || !class_exists($className) || !is_subclass_of($className, AbstractComponentCollection::class)) {
                continue;
            }
            $collection = $renderingContext->getViewHelperResolver()->getResolverDelegate($className);
            if (!$collection instanceof AbstractComponentCollection) {
                continue;
            }
            $this->availableComponents[$className] ??= array_values($collection->getAvailableComponents());
            foreach ($this->callParser->callsForPrefix($source, $prefix) as $call) {
                $findings = [...$findings, ...$this->validateComponentCall($displayPath, $collection, $className, $prefix, $call)];
            }
        }
        return $findings;
    }

    /**
     * @param array{name: string, arguments: list<string>, line: int} $call
     * @return list<LintFinding>
     */
    private function validateComponentCall(string $displayPath, AbstractComponentCollection $collection, string $className, string $prefix, array $call): array
    {
        $tag = sprintf('<%s:%s>', $prefix, $call['name']);
        if (!in_array($call['name'], $this->availableComponents[$className] ?? [], true)) {
            return [new LintFinding($displayPath, LintRule::ComponentArguments, LintSeverity::Error, sprintf('Unknown component %s: no template %s.fluid.html in the collection', $tag, $collection->resolveTemplateName($call['name'])), $call['line'])];
        }
        $definitions = $collection->getComponentDefinition($call['name'])->getArgumentDefinitions();
        $findings = [];
        $unknown = array_diff($call['arguments'], array_keys($definitions));
        if ($unknown !== []) {
            $findings[] = new LintFinding($displayPath, LintRule::ComponentArguments, LintSeverity::Error, sprintf('%s does not declare the argument(s) %s (declared: %s)', $tag, implode(', ', $unknown), implode(', ', array_keys($definitions))), $call['line']);
        }
        $missing = [];
        foreach ($definitions as $name => $definition) {
            if ($definition->isRequired() && !in_array($name, $call['arguments'], true)) {
                $missing[] = $name;
            }
        }
        if ($missing !== []) {
            $findings[] = new LintFinding($displayPath, LintRule::ComponentArguments, LintSeverity::Error, sprintf('%s is called without the required argument(s) %s', $tag, implode(', ', $missing)), $call['line']);
        }
        return $findings;
    }

    // ------------------------------------------------------ partial-resolves

    /**
     * @return list<LintFinding>
     */
    private function checkPartials(string $displayPath, string $file, string $source, bool $strict): array
    {
        $partials = $this->callParser->renderedPartials($source);
        if ($partials === []) {
            return [];
        }
        ['roots' => $roots, 'missingExtensions' => $missingExtensions] = $this->partialRoots->resolve($file);
        $findings = [];
        foreach ($partials as $partial) {
            if ($this->partialRoots->partialExists($partial['name'], $roots)) {
                continue;
            }
            $rootList = $roots === [] ? 'no partial root path applies to this template' : 'searched: ' . implode(', ', array_map($this->partialRoots->shortenPath(...), $roots));
            if ($missingExtensions !== [] && !$strict) {
                $findings[] = new LintFinding($displayPath, LintRule::PartialResolves, LintSeverity::Skipped, sprintf('Partial "%s" not found locally; EXT:%s is not installed, its partials were not checked (%s)', $partial['name'], implode(', EXT:', $missingExtensions), $rootList), $partial['line']);
                continue;
            }
            $findings[] = new LintFinding($displayPath, LintRule::PartialResolves, LintSeverity::Error, sprintf('Partial "%s" does not resolve (%s)', $partial['name'], $rootList), $partial['line']);
        }
        return $findings;
    }

    // ---------------------------------------------------- deprecated-constructs

    /**
     * @return list<LintFinding>
     */
    private function checkDeprecatedConstructs(string $displayPath, string $source): array
    {
        $findings = [];
        foreach (self::DEPRECATED_CONSTRUCTS as $pattern => $message) {
            if (preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE) !== 1) {
                continue;
            }
            $findings[] = new LintFinding($displayPath, LintRule::DeprecatedConstructs, LintSeverity::Error, $message, $this->callParser->lineAt($source, $match[0][1]));
        }
        return $findings;
    }

    private function firstLine(string $message): string
    {
        $lines = explode("\n", $message, 2);
        return trim($lines[0]);
    }
}
