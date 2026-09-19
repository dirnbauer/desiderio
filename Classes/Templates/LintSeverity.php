<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

/**
 * How bad a lint finding is.
 *
 * `Skipped` marks checks the linter could not perform because a third-party
 * ViewHelper namespace or partial root belongs to an extension that is not
 * installed; `--strict` reports those as `Error` instead.
 */
enum LintSeverity: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Skipped = 'skipped';

    /**
     * Symfony console style used to print the severity label.
     */
    public function consoleStyle(): string
    {
        return match ($this) {
            self::Error => 'fg=red',
            self::Warning => 'fg=yellow',
            self::Skipped => 'fg=gray',
        };
    }
}
