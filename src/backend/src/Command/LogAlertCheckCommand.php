<?php

namespace App\Command;

use App\Entity\App;
use App\Service\Alert\AlertCheckerServiceInterface;
use App\Service\App\AppServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'log:alert:check',
    description: 'Checking all logs to create alert',
    hidden: true
)]
class LogAlertCheckCommand extends Command
{

    private AppServiceInterface $appService;
    private AlertCheckerServiceInterface $alertNotification;

    public function __construct(
        AppServiceInterface $appService,
        AlertCheckerServiceInterface $alertNotification
    ) {
        $this->appService = $appService;
        $this->alertNotification = $alertNotification;

        parent::__construct();
    }

    protected function configure(): void
    {
        // configuration
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $apps = $this->appService->getAllApps();

        foreach ($apps as $app) {
            if (!$app instanceof App) {
                continue;
            }

            $this->alertNotification->checkLogAlert($app);
        }

        return Command::SUCCESS;
    }
}
