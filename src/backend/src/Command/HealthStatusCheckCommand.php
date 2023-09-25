<?php

namespace App\Command;

use App\Common\CommandStyle;
use App\Common\DateTimeHelper;
use App\Service\Health\HealthStatusCheckServiceInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:health-status:check',
    description: 'Check the health status of client apps',
)]
class HealthStatusCheckCommand extends Command
{
    private HealthStatusCheckServiceInterface $healthStatusCheckService;
    private LoggerInterface $logger;

    public function __construct(
        HealthStatusCheckServiceInterface $healthStatusCheckService,
        LoggerInterface $logger
    ) {
        $this->healthStatusCheckService = $healthStatusCheckService;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
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
                "The auto health check process is currently running.",
            ]
        );

        while (true) {
            try {
                $this->healthStatusCheckService->runCheckup();
            } catch (Exception $exception) {
                $this->logger->error($exception);
                $io->error(
                    [
                        $dateTimeHelper->dateTimeToStr(),
                        "The health check process has encountered a failure: {$exception->getMessage()}",
                    ]
                );
            }

            sleep(5);
        }

        return Command::SUCCESS;
    }
}
