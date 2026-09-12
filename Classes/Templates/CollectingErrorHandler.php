<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3Fluid\Fluid\View\Exception as ViewException;

/**
 * Fluid error handler that records every parser problem instead of throwing
 * on the first one, so one lint pass reports every unresolvable ViewHelper,
 * undeclared argument and broken expression of a template.
 *
 * @internal used by TemplateLinter only
 */
final class CollectingErrorHandler implements ErrorHandlerInterface
{
    /** @var list<\Throwable> */
    private array $errors = [];

    public function handleParserError(ParserException $error): string
    {
        $this->errors[] = $error;
        return '';
    }

    public function handleExpressionError(ExpressionException $error): string
    {
        $this->errors[] = $error;
        return '';
    }

    public function handleViewHelperError(ViewHelperException $error): string
    {
        $this->errors[] = $error;
        return '';
    }

    public function handleCompilerError(StopCompilingException $error): string
    {
        return '';
    }

    public function handleViewError(ViewException $error): string
    {
        $this->errors[] = $error;
        return '';
    }

    /**
     * @return list<\Throwable>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
