<?php

namespace App\Command;

use App\Common\CommandStyle;
use App\Common\EnvReplacer;
use App\Kernel;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Dotenv\Dotenv;

#[AsCommand(
    name: 'app:setup',
    description: 'LogBook app setup.',
    hidden: true
)]
class SetupManagerCommand extends Command
{
    private ParameterBagInterface $parameterBag;
    private EnvReplacer $envReplacer;

    public function __construct(
        ParameterBagInterface $parameterBag,
        ?EnvReplacer $envReplacer = null
    ) {
        parent::__construct();

        $this->parameterBag = $parameterBag;
        $this->envReplacer = $envReplacer ?? new EnvReplacer();

    }

    protected function configure(): void
    {
        // configuration
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new CommandStyle($input, $output);
        $io->info('Welcome to the LogBook installer.');

        if (self::checkPermission($io)) {
            $io->error("please set the correct permissions for 'var' and 'public' folder!");

            return Command::FAILURE;
        }

        // setup confirm
        if (!self::setupConfirm($io)) {
            $io->error("Setup canceled!");

            return Command::FAILURE;
        }

        // run the database setup
        $dbSetupCommand = $this->getApplication()->find('app:database-setup');
        if (Command::FAILURE === $dbSetupCommand->run($input, $output)) {
            $io->error("Setup was failed!");

            return Command::FAILURE;
        }

        // run the database migration and create user
        if (Command::FAILURE === self::migrate($io, $input, $output)) {
            return Command::FAILURE;
        }

        // set app URL
        $appUrl = $this->setAppURL($io);

        // update the environment to production
        $this->updateEnv($appUrl);
        $io->success([
            "We will proceed with clearing your LogBook cache"
        ]);

        return Command::SUCCESS;
    }

    /**
     * Checks whether the web server user has ownership and necessary permissions.
     *
     * @param StyleInterface $io
     *
     * @return bool
     */
    protected function checkPermission(StyleInterface $io): bool
    {
        $defaultUser = $io->ask("Your web server user (e.g. www-data)", "www-data");
        $io->text('// Checking for appropriate files/folders permissions using "'.$defaultUser.'" user');
        $rootDir = self::getRootDir();

        $dirs = [
            $rootDir.'public/',
            $rootDir.'var/',
        ];
        $anyFail = false;

        foreach ($dirs as $dir) {
            $fileGroup = posix_getpwuid(filegroup($dir));
            $permission = self::getOctalPermission($dir);

            if ($fileGroup['name'] !== $defaultUser || 755 > $permission) {
                $anyFail = true;
            }
        }

        return $anyFail;
    }

    /**
     * Get the root directory of the application.
     *
     * @return string
     */
    protected function getRootDir(): string
    {
        return dirname(__DIR__).'/../';
    }

    /**
     * Get the octal permission representation of a file or directory.
     *
     * @param string $path
     *
     * @return int
     */
    protected function getOctalPermission(string $path): int
    {
        return (int)decoct(fileperms($path) & 0777);
    }

    /**
     * Prompts the user for setup confirmation.
     *
     * @param StyleInterface $io
     *
     * @return bool
     */
    protected function setupConfirm(StyleInterface $io): bool
    {
        $isSetup = "false" === $this->parameterBag->get('app_setup');

        if ($isSetup) {
            $io->info([
                "We've found that LogBook setup has been run before.",
                "You might lose your data if you choose to continue.",
            ]);

            return $io->confirm(
                'Are you sure to proceed with the setup?',
                false
            );
        }

        $this->envReplacer->replaceEnvironmentValue(
            'APP_SETUP',
            'false',
            self::getEnvironmentDir()
        );

        return $io->confirm('Are you sure to proceed with the setup?');
    }

    /**
     * Retrieves the directory path for the current environment.
     *
     * @return string
     */
    protected function getEnvironmentDir(): string
    {
        return self::getRootDir();
    }

    protected function migrate(StyleInterface $io, InputInterface $input, OutputInterface $output): int
    {
        $dotenv = new Dotenv();
        $dotenv->load(self::getEnvironmentDir().'.env');
        $kernel = new Kernel($this->getEnvironment(), false);
        $app = new Application($kernel);
        $commandNames = [
            'cache:clear',
            'app:generate-key',
            'doctrine:migration:migrate',
            'app:user:create',
        ];

        foreach ($commandNames as $name) {
            $command = $app->find($name);

            if (Command::SUCCESS !== $command->run($input, $output)) {
                $io->error("Setup was failed!");

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Checking the current environment, on development or not.
     *
     * @return string
     */
    protected function getEnvironment(): string
    {
        return $this->parameterBag->get('kernel.environment');
    }

    /**
     * Sets the application URL based.
     *
     * @param StyleInterface $io
     *
     * @return array
     */
    protected function setAppURL(StyleInterface $io): array
    {
        $io->title("General");
        $urlFail = false;

        do {
            if ($urlFail) {
                $io->error(['Not valid URL!', 'Please try again.']);
            }

            $appUrl = $io->ask("App address(URL)", 'https://logbook.com');
            $urlFail = true;
        } while (filter_var($appUrl, FILTER_VALIDATE_URL) === false);

        $protocol = parse_url($appUrl, PHP_URL_SCHEME);
        $urlInfo = ["Type your LogBook URL: {$appUrl}"];

        if ("http" === $protocol) {
            $urlInfo[] = "You are not using SSL (HTTPS). Secure cookie config should be disabled in .env.";

            $io->info($urlInfo);
            $cookie = $io->confirm(
                'Disable secure cookie',
                true
            ) ? 'false' : 'true';
        } else {
            $urlInfo[] = "You are using SSL (HTTPS). Secure cookie config should be enabled in .env.";
            $io->info($urlInfo);
            $cookie = $io->confirm(
                'Enable secure cookie',
                true
            ) ? 'true' : 'false';
        }

        $mercureUrl = $appUrl.'/.well-known/mercure';

        return [
            'APP_URL' => $appUrl,
            'COOKIE_SECURE' => $cookie,
            'MERCURE_URL' => $mercureUrl,
            'MERCURE_PUBLIC_URL' => $mercureUrl,
        ];
    }

    /**
     * Update the environment state.
     */
    protected function updateEnv(array $update): void
    {
        $update['APP_ENV'] = 'prod';
        $this->envReplacer->bulkReplace($update, self::getEnvironmentDir());
    }
}
