<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// the name of the command is what users type after "php console"
#[AsCommand(
    name: 'setup',
    description: 'Install all dependencies both backend and frontend.'
)]
class SetupCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = dirname(__DIR__);

        // install backend vendor
        shell_exec(
            sprintf(
                "cd %s && composer install",
                $path.'/backend'
            )
        );

        // generate new app key
        shell_exec(
            sprintf(
                "php %s app:generate-key",
                $path.'/backend/bin/console'
            )
        );

        // install frontend vendor
        shell_exec(
            sprintf(
                "cd %s && npm install",
                $path.'/frontend'
            )
        );

        return Command::SUCCESS;
    }
}
