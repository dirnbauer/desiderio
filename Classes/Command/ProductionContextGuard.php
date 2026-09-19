<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;

/**
 * The seed commands write demo content. None of them may do that against a
 * Production installation by accident, so they all offer the same
 * --allow-production escape hatch with the same refusal message. This holds
 * that one rule instead of three copies of it.
 */
final class ProductionContextGuard
{
    public const string OPTION = 'allow-production';

    public static function addOption(Command $command): void
    {
        $command->getDefinition()->addOption(new InputOption(
            self::OPTION,
            null,
            InputOption::VALUE_NONE,
            'Run even when the Application Context is Production. Use on a sandbox only.'
        ));
    }

    /**
     * @return bool true when the command may proceed
     */
    public static function allows(InputInterface $input, SymfonyStyle $io): bool
    {
        if ($input->getOption(self::OPTION) === true || !Environment::getContext()->isProduction()) {
            return true;
        }
        $io->error(sprintf(
            'Refusing to run in Production application context. Pass --%s to override (and only do so on a sandbox).',
            self::OPTION
        ));

        return false;
    }
}
