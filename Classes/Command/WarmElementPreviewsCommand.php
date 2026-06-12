<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webconsulting\Desiderio\Library\PreviewWarmer;

#[AsCommand(
    name: 'desiderio:library:warm',
    description: 'Prerender all element library records into the TYPO3 page cache (fast picker previews).'
)]
final class WarmElementPreviewsCommand extends Command
{
    public function __construct(
        private readonly PreviewWarmer $previewWarmer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('folder', null, InputOption::VALUE_REQUIRED, 'Element Library sysfolder uid (printed by desiderio:library:seed).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $folderOption = $input->getOption('folder');
        $folderUid = is_numeric($folderOption) ? (int)$folderOption : 0;
        if ($folderUid <= 0) {
            $io->error('Pass --folder=<element library sysfolder uid>.');
            return self::FAILURE;
        }

        $result = $this->previewWarmer->warm($folderUid, function (string $cType, string $url, bool $success) use ($output): void {
            if ($output->isVerbose()) {
                $output->writeln(sprintf('%s %s %s', $success ? '<info>OK</info>  ' : '<error>FAIL</error>', $cType, $output->isVeryVerbose() ? $url : ''));
            }
        });

        $io->writeln(sprintf('%d previews warmed, %d failed', $result['warmed'], count($result['failed'])));
        foreach (array_slice($result['failed'], 0, 10) as $failure) {
            $io->warning($failure);
        }

        return $result['failed'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
