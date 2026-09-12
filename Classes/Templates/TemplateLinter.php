<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\UnknownNamespaceException;
use TYPO3Fluid\Fluid\Core\Parser\TemplateLocation;
use TYPO3Fluid\Fluid\Core\TemplateLocationException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
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
 * would throw, plus a handful of static checks the parser cannot do:
 *
 *  - parse: unknown ViewHelpers, namespaces, arguments, components
 *  - namespace-usage: prefixes used without declaration, declared but unused,
 *    non-canonical xmlns:f URI
 *  - partial-resolves: every f:render partial="…" exists in the partial roots
 *    of the template's rendering context
 *  - deprecated-constructs: {namespace}, f:widget.*, f:be.*, f:security.*, f:base
 *  - component-arguments: every attribute at a component call site has a
 *    matching <f:argument>, required arguments are passed, the component exists
 *
 * Third-party ViewHelper namespaces (news, powermail, solr, …) whose extension
 * is not installed are ignored and reported as "skipped"; --strict makes them
 * errors.
 */
final class TemplateLinter
{
    public const RULE_PARSE = 'parse';
    public const RULE_NAMESPACE_USAGE = 'namespace-usage';
    public const RULE_PARTIAL_RESOLVES = 'partial-resolves';
    public const RULE_DEPRECATED_CONSTRUCTS = 'deprecated-constructs';
    public const RULE_COMPONENT_ARGUMENTS = 'component-arguments';

    public const RULES = [
        self::RULE_PARSE,
        self::RULE_NAMESPACE_USAGE,
        self::RULE_PARTIAL_RESOLVES,
        self::RULE_DEPRECATED_CONSTRUCTS,
        self::RULE_COMPONENT_ARGUMENTS,
    ];

    private const NAMESPACE_URI_PREFIX = 'http://typo3.org/ns/';
    private const CANONICAL_FLUID_URI = 'http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers';

    /** Directory names never scanned when a directory is given. */
    private const SKIPPED_DIRECTORIES = [
        '.git', '.ddev', '.idea', '.phpunit.cache', 'node_modules', 'vendor', 'var', 'public', 'Public',
        'Build', 'Tests', 'Documentation',
    ];

    /**
     * Parser errors that are a consequence of an earlier error: an
     * unresolvable opening tag leaves its closing tag orphaned.
     */
    private const CASCADE_MESSAGES = [
        'You closed a templating tag which you never opened',
        'Not all tags were closed',
        'Templating tags not properly nested',
    ];

    private const CASCADE_MARKER = '[cascade] ';

    private const DEPRECATED_CONSTRUCTS = [
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
        $scanSource = $this->stripComments($source);
        /** @var list<LintFinding> $findings */
        $findings = [];

        if ($options->isRuleEnabled(self::RULE_DEPRECATED_CONSTRUCTS)) {
            $findings = [...$findings, ...$this->checkDeprecatedConstructs($displayPath, $scanSource)];
        }

        $renderingContext = $this->renderingContextFactory->create();
        $resolver = $renderingContext->getViewHelperResolver();
        $globalPrefixes = array_keys($resolver->getNamespaces());
        $declared = $this->extractDeclaredNamespaces($source);
        $used = $this->extractUsedPrefixes($scanSource);

        if ($options->isRuleEnabled(self::RULE_NAMESPACE_USAGE)) {
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
                self::RULE_PARSE,
                $options->strict ? LintFinding::SEVERITY_ERROR : LintFinding::SEVERITY_SKIPPED,
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

        if ($options->isRuleEnabled(self::RULE_COMPONENT_ARGUMENTS)) {
            $findings = [...$findings, ...$this->checkComponentArguments($displayPath, $scanSource, $declared, $renderingContext)];
        }

        if ($options->isRuleEnabled(self::RULE_PARSE)) {
            $findings = [...$findings, ...$this->parseTemplate($displayPath, $file, $parseSource, $renderingContext, $unavailable, $options->strict, $options->isRuleEnabled(self::RULE_COMPONENT_ARGUMENTS))];
        }

        if ($options->isRuleEnabled(self::RULE_PARTIAL_RESOLVES)) {
            $findings = [...$findings, ...$this->checkPartials($displayPath, $file, $scanSource, $options->strict)];
        }

        // Orphaned closing tags follow from an earlier unresolvable opening
        // tag; report them only when nothing else explains them.
        $hasPrimaryError = array_filter($findings, static fn(LintFinding $finding): bool => $finding->isError() && !str_starts_with($finding->message, self::CASCADE_MARKER)) !== [];
        $seen = [];
        foreach ($findings as $finding) {
            if (str_starts_with($finding->message, self::CASCADE_MARKER)) {
                if ($hasPrimaryError) {
                    continue;
                }
                $finding = new LintFinding($finding->file, $finding->rule, $finding->severity, substr($finding->message, strlen(self::CASCADE_MARKER)), $finding->line);
            }
            $key = $finding->rule . '|' . $finding->message;
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
            return new LintFinding($displayPath, self::RULE_NAMESPACE_USAGE, LintFinding::SEVERITY_ERROR, sprintf('Prefix "%s" is used but neither declared with xmlns:%s nor registered globally', $prefix, $prefix), $line);
        }
        if ($throwable instanceof InvalidArgumentValueException && $code === 1748903732) {
            // The static component-arguments rule already reported this call site with its line.
            return $componentArgumentsChecked ? null : new LintFinding($displayPath, self::RULE_COMPONENT_ARGUMENTS, LintFinding::SEVERITY_ERROR, $message, $line);
        }
        if ($throwable instanceof UndeclaredArgumentException) {
            return new LintFinding($displayPath, self::RULE_PARSE, LintFinding::SEVERITY_ERROR, 'Undeclared ViewHelper argument: ' . (string)preg_replace('/^Undeclared arguments passed to ViewHelper\s*/', '', $message), $line);
        }
        if ($throwable instanceof ParserException && $code === 1407060572 && preg_match('/<([A-Za-z0-9.]+):([A-Za-z0-9.]+)>/', $message, $match) === 1) {
            [, $prefix, $name] = $match;
            if (isset($unavailable[$prefix])) {
                // Strict mode keeps the namespace registered, so every call resolves to this error.
                return $strict
                    ? new LintFinding($displayPath, self::RULE_PARSE, LintFinding::SEVERITY_ERROR, sprintf('Unknown ViewHelper <%s:%s> (namespace %s is not installed)', $prefix, $name, $unavailable[$prefix]), $line)
                    : null;
            }
            if (str_contains($throwable->getMessage(), 'component template')) {
                return $componentArgumentsChecked ? null : new LintFinding($displayPath, self::RULE_COMPONENT_ARGUMENTS, LintFinding::SEVERITY_ERROR, sprintf('Unknown component <%s:%s>: no component template found in the collection', $prefix, $name), $line);
            }
            return new LintFinding($displayPath, self::RULE_PARSE, LintFinding::SEVERITY_ERROR, sprintf('Unknown ViewHelper <%s:%s>', $prefix, $name), $line);
        }
        foreach (self::CASCADE_MESSAGES as $cascade) {
            if (str_contains($message, $cascade)) {
                return new LintFinding($displayPath, self::RULE_PARSE, LintFinding::SEVERITY_ERROR, self::CASCADE_MARKER . $message, $line);
            }
        }
        return new LintFinding(
            $displayPath,
            self::RULE_PARSE,
            LintFinding::SEVERITY_ERROR,
            sprintf('%s: %s', (new \ReflectionClass($throwable))->getShortName(), $message),
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
            $findings[] = new LintFinding($displayPath, self::RULE_NAMESPACE_USAGE, LintFinding::SEVERITY_ERROR, sprintf('Prefix "%s" is used but neither declared with xmlns:%s nor registered globally', $prefix, $prefix), $line);
        }
        foreach ($declared as $prefix => $declaration) {
            if (!isset($used[$prefix])) {
                $findings[] = new LintFinding($displayPath, self::RULE_NAMESPACE_USAGE, LintFinding::SEVERITY_WARNING, sprintf('Namespace prefix "%s" is declared but never used', $prefix), $declaration['line']);
            }
            if ($prefix === 'f' && $declaration['uri'] !== self::CANONICAL_FLUID_URI) {
                $findings[] = new LintFinding($displayPath, self::RULE_NAMESPACE_USAGE, LintFinding::SEVERITY_WARNING, sprintf('xmlns:f declares "%s"; the canonical TYPO3 URI is %s', $declaration['uri'], self::CANONICAL_FLUID_URI), $declaration['line']);
            }
        }
        return $findings;
    }

    /**
     * Namespaces declared on the root tag (xmlns:) or via {namespace}.
     *
     * @return array<string, array{uri: string, php: string|null, line: int}>
     */
    private function extractDeclaredNamespaces(string $source): array
    {
        $declared = [];
        if (preg_match('/<([a-z0-9]+)(?:[^>]*?)\s+xmlns:([a-zA-Z0-9.]+)=("[^"]+"|\'[^\']+\')[^>]*>/', $source, $tagMatch, PREG_OFFSET_CAPTURE) === 1) {
            $tag = $tagMatch[0][0];
            $tagOffset = $tagMatch[0][1];
            preg_match_all('/xmlns:([a-zA-Z0-9.]+)=("[^"]+"|\'[^\']+\')/', $tag, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($matches as $match) {
                $prefix = $match[1][0];
                $uri = trim($match[2][0], '"\'');
                $php = str_starts_with($uri, self::NAMESPACE_URI_PREFIX)
                    ? str_replace('/', '\\', substr($uri, strlen(self::NAMESPACE_URI_PREFIX)))
                    : null;
                $declared[$prefix] = ['uri' => $uri, 'php' => $php, 'line' => $this->lineAt($source, $tagOffset + $match[0][1])];
            }
        }
        if (preg_match_all('/(?<!\\\\)\{namespace\s*(?P<identifier>[a-zA-Z*]+[a-zA-Z0-9.*]*)\s*(?:=\s*(?P<php>[A-Za-z0-9\\\\]+))?\s*}/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0) {
            foreach ($matches as $match) {
                $prefix = $match['identifier'][0];
                $php = isset($match['php']) ? $match['php'][0] : null;
                $declared[$prefix] = ['uri' => (string)$php, 'php' => $php, 'line' => $this->lineAt($source, $match[0][1])];
            }
        }
        return $declared;
    }

    /**
     * Prefixes that appear in tag syntax, inline syntax or inline chains.
     *
     * @return array<string, int> prefix => first line
     */
    private function extractUsedPrefixes(string $source): array
    {
        $used = [];
        $patterns = [
            '/<\/?([a-z][a-zA-Z0-9]*):[a-zA-Z][a-zA-Z0-9.]*(?=[\s\/>])/',
            '/\{\s*([a-z][a-zA-Z0-9]*):[a-zA-Z][a-zA-Z0-9.]*\s*\(/',
            '/->\s*([a-z][a-zA-Z0-9]*):[a-zA-Z][a-zA-Z0-9.]*\s*\(/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) < 1) {
                continue;
            }
            foreach ($matches as $match) {
                $prefix = $match[1][0];
                $line = $this->lineAt($source, $match[0][1]);
                $used[$prefix] = isset($used[$prefix]) ? min($used[$prefix], $line) : $line;
            }
        }
        return $used;
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
            foreach ($this->findComponentCalls($source, $prefix) as $call) {
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
            return [new LintFinding($displayPath, self::RULE_COMPONENT_ARGUMENTS, LintFinding::SEVERITY_ERROR, sprintf('Unknown component %s: no template %s.fluid.html in the collection', $tag, $collection->resolveTemplateName($call['name'])), $call['line'])];
        }
        $definitions = $collection->getComponentDefinition($call['name'])->getArgumentDefinitions();
        $findings = [];
        $unknown = array_diff($call['arguments'], array_keys($definitions));
        if ($unknown !== []) {
            $findings[] = new LintFinding($displayPath, self::RULE_COMPONENT_ARGUMENTS, LintFinding::SEVERITY_ERROR, sprintf('%s does not declare the argument(s) %s (declared: %s)', $tag, implode(', ', $unknown), implode(', ', array_keys($definitions))), $call['line']);
        }
        $missing = [];
        foreach ($definitions as $name => $definition) {
            if ($definition->isRequired() && !in_array($name, $call['arguments'], true)) {
                $missing[] = $name;
            }
        }
        if ($missing !== []) {
            $findings[] = new LintFinding($displayPath, self::RULE_COMPONENT_ARGUMENTS, LintFinding::SEVERITY_ERROR, sprintf('%s is called without the required argument(s) %s', $tag, implode(', ', $missing)), $call['line']);
        }
        return $findings;
    }

    /**
     * Tag and inline call sites of a component prefix with their argument names.
     *
     * @return list<array{name: string, arguments: list<string>, line: int}>
     */
    private function findComponentCalls(string $source, string $prefix): array
    {
        $calls = [];
        $quoted = preg_quote($prefix, '/');
        if (preg_match_all('/<' . $quoted . ':([a-zA-Z][a-zA-Z0-9.]*)(?=[\s\/>])/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0) {
            foreach ($matches as $match) {
                $offset = $match[0][1] + strlen($match[0][0]);
                $calls[] = [
                    'name' => $match[1][0],
                    'arguments' => $this->parseTagAttributeNames($source, $offset),
                    'line' => $this->lineAt($source, $match[0][1]),
                ];
            }
        }
        if (preg_match_all('/(?:\{\s*|->\s*)' . $quoted . ':([a-zA-Z][a-zA-Z0-9.]*)\s*\(/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0) {
            foreach ($matches as $match) {
                $offset = $match[0][1] + strlen($match[0][0]);
                $calls[] = [
                    'name' => $match[1][0],
                    'arguments' => $this->parseInlineArgumentNames($source, $offset),
                    'line' => $this->lineAt($source, $match[0][1]),
                ];
            }
        }
        return $calls;
    }

    /**
     * Attribute names of a tag, starting right after the tag name. Values may
     * contain ">" inside quotes, so this walks quoted strings instead of
     * matching up to the next ">".
     *
     * @return list<string>
     */
    private function parseTagAttributeNames(string $source, int $offset): array
    {
        $names = [];
        $length = strlen($source);
        while ($offset < $length) {
            while ($offset < $length && ctype_space($source[$offset])) {
                $offset++;
            }
            if ($offset >= $length || $source[$offset] === '>' || $source[$offset] === '/') {
                break;
            }
            if (preg_match('/\G([a-zA-Z_][a-zA-Z0-9_.:-]*)/', $source, $nameMatch, 0, $offset) !== 1) {
                break;
            }
            $names[] = $nameMatch[1];
            $offset += strlen($nameMatch[0]);
            while ($offset < $length && ctype_space($source[$offset])) {
                $offset++;
            }
            if ($offset < $length && $source[$offset] === '=') {
                $offset++;
                while ($offset < $length && ctype_space($source[$offset])) {
                    $offset++;
                }
                if ($offset < $length && ($source[$offset] === '"' || $source[$offset] === "'")) {
                    $offset = $this->skipQuotedString($source, $offset);
                } else {
                    while ($offset < $length && !ctype_space($source[$offset]) && $source[$offset] !== '>') {
                        $offset++;
                    }
                }
            }
        }
        return $names;
    }

    /**
     * Top-level argument keys of an inline ViewHelper call, starting right
     * after the opening parenthesis.
     *
     * @return list<string>
     */
    private function parseInlineArgumentNames(string $source, int $offset): array
    {
        $names = [];
        $depth = 0;
        $length = strlen($source);
        $expectKey = true;
        while ($offset < $length) {
            $char = $source[$offset];
            if ($char === '"' || $char === "'") {
                $offset = $this->skipQuotedString($source, $offset);
                continue;
            }
            if ($char === '(' || $char === '{' || $char === '[') {
                $depth++;
            } elseif ($char === ')' || $char === '}' || $char === ']') {
                if ($depth === 0) {
                    break;
                }
                $depth--;
            } elseif ($depth === 0 && $char === ',') {
                $expectKey = true;
            } elseif ($depth === 0 && $expectKey && preg_match('/\G\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', $source, $keyMatch, 0, $offset) === 1) {
                $names[] = $keyMatch[1];
                $expectKey = false;
                $offset += strlen($keyMatch[0]);
                continue;
            }
            $offset++;
        }
        return $names;
    }

    private function skipQuotedString(string $source, int $offset): int
    {
        $quote = $source[$offset];
        $length = strlen($source);
        $offset++;
        while ($offset < $length) {
            if ($source[$offset] === '\\') {
                $offset += 2;
                continue;
            }
            if ($source[$offset] === $quote) {
                return $offset + 1;
            }
            $offset++;
        }
        return $length;
    }

    // ------------------------------------------------------ partial-resolves

    /**
     * @return list<LintFinding>
     */
    private function checkPartials(string $displayPath, string $file, string $source, bool $strict): array
    {
        $partials = [];
        foreach (['/<f:render\b[^>]*?\bpartial\s*=\s*["\']([^"\']*)["\']/', '/f:render\s*\(([^)]*)\)/'] as $pattern) {
            if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) < 1) {
                continue;
            }
            foreach ($matches as $match) {
                $name = $match[1][0];
                if (str_starts_with($pattern, '/f:render\s*')) {
                    if (preg_match('/\bpartial\s*:\s*["\']([^"\']*)["\']/', $name, $inline) !== 1) {
                        continue;
                    }
                    $name = $inline[1];
                }
                if ($name === '' || str_contains($name, '{')) {
                    continue;
                }
                $partials[] = ['name' => $name, 'line' => $this->lineAt($source, $match[0][1])];
            }
        }
        if ($partials === []) {
            return [];
        }
        ['roots' => $roots, 'missingExtensions' => $missingExtensions] = $this->resolvePartialRoots($file);
        $findings = [];
        foreach ($partials as $partial) {
            if ($this->partialExists($partial['name'], $roots)) {
                continue;
            }
            $rootList = $roots === [] ? 'no partial root path applies to this template' : 'searched: ' . implode(', ', array_map(fn(string $root): string => $this->shortenPath($root), $roots));
            if ($missingExtensions !== [] && !$strict) {
                $findings[] = new LintFinding($displayPath, self::RULE_PARTIAL_RESOLVES, LintFinding::SEVERITY_SKIPPED, sprintf('Partial "%s" not found locally; EXT:%s is not installed, its partials were not checked (%s)', $partial['name'], implode(', EXT:', $missingExtensions), $rootList), $partial['line']);
                continue;
            }
            $findings[] = new LintFinding($displayPath, self::RULE_PARTIAL_RESOLVES, LintFinding::SEVERITY_ERROR, sprintf('Partial "%s" does not resolve (%s)', $partial['name'], $rootList), $partial['line']);
        }
        return $findings;
    }

    /**
     * @param list<string> $roots
     */
    private function partialExists(string $name, array $roots): bool
    {
        foreach ($roots as $root) {
            foreach (['.html', '.fluid.html', '.txt', '.fluid.txt', '.xml'] as $suffix) {
                if (is_file(rtrim($root, '/') . '/' . $name . $suffix)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Partial root paths that apply to a template, derived from its location.
     *
     * Generic rule: every ancestor directory that has a `Partials` sibling
     * folder (Templates/, Layouts/, Partials/ live side by side) contributes
     * it. Desiderio-specific contexts add the roots TypoScript configures on
     * top: content elements and classic content share the page partials,
     * extension template sets fall back to the partials of the extension they
     * override.
     *
     * @return array{roots: list<string>, missingExtensions: list<string>}
     */
    private function resolvePartialRoots(string $file): array
    {
        $roots = [];
        $missing = [];
        $directory = dirname($file);
        $extensionRoot = $this->findExtensionRoot($directory);
        for ($current = $directory; $current !== '' && $current !== '/' && $current !== dirname($current); $current = dirname($current)) {
            if ($extensionRoot !== null && !str_starts_with($current, $extensionRoot)) {
                break;
            }
            if (is_dir($current . '/Partials')) {
                $roots[] = $current . '/Partials';
            }
        }
        if ($extensionRoot !== null) {
            $private = $extensionRoot . '/Resources/Private';
            $relative = substr($file, strlen($extensionRoot) + 1);
            // Most specific prefix first: the first match wins.
            $contexts = [
                'Resources/Private/ClassicContent/' => [[$private . '/ClassicContent/Partials', $private . '/Templates/Partials'], []],
                'Resources/Private/Templates/' => [[$private . '/Templates/Partials'], []],
                'Resources/Private/Presets/' => [[$private . '/Templates/Partials'], []],
                'Resources/Private/ShadcnUi/' => [[$private . '/Templates/Partials'], []],
                'Resources/Private/Extensions/News/' => [[$private . '/Extensions/News/Partials'], ['news' => 'Resources/Private/Partials']],
                // Blog comment form: TYPO3 Form Framework partials (BlogCommentFormFactory).
                'Resources/Private/Extensions/Blog/Partials/Form/' => [[$private . '/Extensions/Blog/Partials/Form'], ['form' => 'Resources/Private/Frontend/Partials']],
                'Resources/Private/Extensions/Blog/' => [[$private . '/Extensions/Blog/Partials'], ['blog' => 'Resources/Private/Partials']],
                'Resources/Private/Extensions/Powermail/' => [[$private . '/Extensions/Powermail/Partials'], ['powermail' => 'Resources/Private/Partials']],
                'Resources/Private/Solr/' => [[$private . '/Solr/Partials'], ['solr' => 'Resources/Private/Partials']],
                'Resources/Private/Form/' => [[$private . '/Form/Partials', $private . '/Form/CaptchaOverride/Partials'], ['form' => 'Resources/Private/Frontend/Partials']],
                'ContentBlocks/' => [[$private . '/Templates/Partials', $private . '/ClassicContent/Partials'], []],
            ];
            foreach ($contexts as $prefix => [$localRoots, $externalRoots]) {
                if (!str_starts_with($relative, $prefix)) {
                    continue;
                }
                $roots = [...$roots, ...$localRoots];
                foreach ($externalRoots as $extensionKey => $subPath) {
                    if (ExtensionManagementUtility::isLoaded($extensionKey)) {
                        $roots[] = ExtensionManagementUtility::extPath($extensionKey) . $subPath;
                    } else {
                        $missing[] = $extensionKey;
                    }
                }
                break;
            }
        }
        return [
            'roots' => array_values(array_unique(array_filter($roots, 'is_dir'))),
            'missingExtensions' => $missing,
        ];
    }

    private function findExtensionRoot(string $directory): ?string
    {
        for ($current = $directory; $current !== '' && $current !== '/' && $current !== dirname($current); $current = dirname($current)) {
            if (is_file($current . '/ext_emconf.php') || (is_file($current . '/composer.json') && is_dir($current . '/Resources'))) {
                return $current;
            }
        }
        return null;
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
            $findings[] = new LintFinding($displayPath, self::RULE_DEPRECATED_CONSTRUCTS, LintFinding::SEVERITY_ERROR, $message, $this->lineAt($source, $match[0][1]));
        }
        return $findings;
    }

    // ----------------------------------------------------------------- utils

    /**
     * Blank out Fluid and HTML comments while keeping line numbers intact.
     */
    private function stripComments(string $source): string
    {
        return (string)preg_replace_callback(
            '/<f:comment>.*?<\/f:comment>|<!--.*?-->/s',
            static fn(array $match): string => (string)preg_replace('/[^\n]/', ' ', $match[0]),
            $source,
        );
    }

    private function lineAt(string $source, int $offset): int
    {
        return substr_count(substr($source, 0, $offset), "\n") + 1;
    }

    private function firstLine(string $message): string
    {
        $lines = explode("\n", $message, 2);
        return trim($lines[0]);
    }

    private function shortenPath(string $path): string
    {
        $extensionRoot = $this->findExtensionRoot($path);
        if ($extensionRoot === null) {
            return $path;
        }
        return basename($extensionRoot) . '/' . ltrim(substr($path, strlen($extensionRoot)), '/');
    }
}
