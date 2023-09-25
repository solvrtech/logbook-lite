<?php

namespace App\Command;

use App\Common\CommandStyle;
use App\Common\EnvReplacer;
use Ketut\RandomString\Random;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:generate-key',
    description: 'Generate new secret key',
)]
class GenerateKeyCommand extends Command
{
    protected function configure(): void
    {
        // configuration
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new CommandStyle($input, $output);

        // generate secret environment
        $random = (new Random())
            ->numeric()
            ->lowercase();
        $keys = [
            'APP_SECRET' => $random->length(32)->generate(),
            'JWT_KEY' => $random->uppercase()->length(64)->generate(),
            'HMAC_SECRET' => $random->length(32)->generate(),
        ];

        $envReplacer = new EnvReplacer();
        $dir = dirname(__DIR__).'/../';
        $envReplacer->bulkReplace($keys, $dir);

        return Command::SUCCESS;
    }
}
