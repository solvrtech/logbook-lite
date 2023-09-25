<?php

namespace App\Command;

use App\Common\CommandStyle;
use App\Common\DateTimeHelper;
use App\Service\Auth\ResetPasswordServiceInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:expired-record:clear',
    description: 'Clears any expired records from the system',
)]
class ExpiredRecordClearCommand extends Command
{
    private LoggerInterface $logger;
    private ResetPasswordServiceInterface $resetPasswordService;

    public function __construct(
        LoggerInterface $logger,
        ResetPasswordServiceInterface $resetPasswordService
    ) {
        parent::__construct();

        $this->logger = $logger;
        $this->resetPasswordService = $resetPasswordService;
    }

    protected function configure(): void
    {
        // configuration
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateTimeHelper = new DateTimeHelper();
        $io = new CommandStyle($input, $output);
        $io->info(
            [
                $dateTimeHelper->dateTimeToStr(),
                "The auto clear process is currently running.",
            ]
        );

        while (true) {
            // clear expired reset password
            try {
                $this->resetPasswordService->clearExpiredSetPasswords();
            } catch (Exception $exception) {
                $this->logger->error($exception);
                $io->error(
                    [
                        $dateTimeHelper->dateTimeToStr(),
                        "The reset password clear process has encountered a failure: {$exception->getMessage()}",
                    ]
                );
            }

            sleep(120);
        }

        return Command::SUCCESS;
    }
}
