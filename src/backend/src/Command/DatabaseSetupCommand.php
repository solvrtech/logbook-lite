<?php

namespace App\Command;

use App\Common\CommandStyle;
use App\Common\EnvReplacer;
use App\Kernel;
use App\Model\Request\DatabaseDsnRequest;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Dotenv\Dotenv;

#[AsCommand(
    name: 'app:database-setup',
    description: 'Setup the database connection',
    hidden: true
)]
class DatabaseSetupCommand extends Command
{
    private const MYSQL = "mysql";
    private const MARIADB = "mariadb";
    private const POSTGRES = "postgresql";

    private ParameterBagInterface $parameterBag;

    public function __construct(
        ParameterBagInterface $parameterBag
    ) {
        $this->parameterBag = $parameterBag;

        parent::__construct();
    }

    protected function configure(): void
    {
        // configuration
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new CommandStyle($input, $output);
        $io->title("Database configuration");

        while (Command::SUCCESS !== self::setDBConfiguration($io)) {
            $io->error(['Testing database connection: failed!', 'Please try again.']);
        }

        $this->getApplication()->find('cache:clear')->run($input, $output);
        $migration = self::dbMakeMigration($input, $output);

        if (Command::SUCCESS !== $migration) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Set the database configuration based on the input provided by the user.
     *
     * @param SymfonyStyle $io
     *
     * @return int
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    protected function setDBConfiguration(StyleInterface $io): int
    {
        $databaseDsn = (new DatabaseDsnRequest())
            ->setDbms(
                $io->choice(
                    "Choose the database management system:",
                    [self::MYSQL, self::MARIADB, self::POSTGRES],
                    self::MYSQL
                )
            );

        $dbServer = self::getDBServer($databaseDsn->getDbms());
        $port = $dbServer['port'];
        $version = self::getServerVersion($dbServer);
        $dbVersionFail = false;

        do {
            if ($dbVersionFail) {
                $io->error(['Database version cannot be empty!', 'Please try again.']);
            }

            $version = $io->ask("Database version (e.g. x or x.x.x for mariadb)", $version);
            $dbVersionFail = true;
        } while (null === $version);

        $databaseDsn
            ->setHostname(
                $io->ask("IP address of the server where the database is hosted (e.g. 127.0.0.1)", '127.0.0.1')
            )
            ->setPort($io->ask("Port number used to connect to the database (e.g. {$port})", $port))
            ->setDbName($io->ask("Database name (e.g. logbookdb)", 'logbookdb'))
            ->setUsername($io->ask("Username for accessing the database (e.g. logbookuser)", 'logbookuser'))
            ->setPassword(
                $io->askHidden("Password for accessing the database")
            );
        $database = self::createDatabaseConfig($databaseDsn, $version);

        if (null === $database) {
            return Command::FAILURE;
        }

        $envReplacer = new EnvReplacer();
        $dir = dirname(__DIR__).'/../';
        $envReplacer->bulkReplace($database, $dir);

        return Command::SUCCESS;
    }

    protected function getDBServer(string $key): array
    {
        $dbServer = [
            self::MYSQL => [
                'name' => self::MYSQL,
                'command' => 'mysql -V',
                'pattern' => '/Ver (\d+\.\d+\.\d+)/',
                'port' => 3306,
            ],
            self::MARIADB => [
                'name' => self::MARIADB,
                'command' => 'mysql -V',
                'pattern' => '/Distrib (\d+\.\d+\.\d+)-MariaDB/',
                'port' => 3306,
            ],
            self::POSTGRES => [
                'name' => self::POSTGRES,
                'command' => 'psql -V',
                'pattern' => '/\(PostgreSQL\) (\d+\.\d+)/',
                'port' => 5432,
            ],
        ];

        return $dbServer[$key];
    }

    /**
     * @throws Exception
     */
    protected function getServerVersion(array $dbServer): string|null
    {
        $serverVersion = shell_exec($dbServer['command']);

        preg_match($dbServer['pattern'], $serverVersion, $matches);
        $version = isset($matches[1]) ? $matches[1] : null;

        if (null === $version) {
            return null;
        }

        if (self::MARIADB === $dbServer['name']) {
            return $version;
        }

        $majorVersion = explode('.', $version);

        return $majorVersion[0];
    }

    /**
     * Generate database config: dsn and type.
     *
     * @param DatabaseDsnRequest $request
     * @param string $serverVersion
     *
     * @return array|null
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function createDatabaseConfig(DatabaseDsnRequest $request, string $serverVersion): ?array
    {
        $dsnFormat = "%s://%s:%s@%s:%d/%s?serverVersion=%s";

        switch ($request->getDbms()) {
            case self::MARIADB:
                $database = self::MYSQL;
                $dsn = sprintf(
                    $dsnFormat,
                    $database,
                    urlencode($request->getUsername()),
                    urlencode($request->getPassword()),
                    $request->getHostname(),
                    $request->getPort(),
                    urlencode($request->getDbName()),
                    "mariadb-{$serverVersion}&charset=utf8mb4"
                );
                break;
            case self::POSTGRES:
                $database = self::POSTGRES;
                $dsn = sprintf(
                    $dsnFormat,
                    $database,
                    urlencode($request->getUsername()),
                    urlencode($request->getPassword()),
                    $request->getHostname(),
                    $request->getPort(),
                    urlencode($request->getDbName()),
                    "{$serverVersion}&charset=utf8"
                );
                break;
            default:
                $database = self::MYSQL;
                $dsn = sprintf(
                    $dsnFormat,
                    $database,
                    urlencode($request->getUsername()),
                    urlencode($request->getPassword()),
                    $request->getHostname(),
                    $request->getPort(),
                    urlencode($request->getDbName()),
                    "{$serverVersion}&charset=utf8mb4"
                );
        }

        $testConnection = self::connection($dsn);

        if (null === $testConnection) {
            return null;
        }

        return [
            'DATABASE_URL' => $dsn,
            'DATABASE_TYPE' => $database,
        ];
    }

    /**
     * Test connection to database and get the version.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function connection(string $dsn): string|null
    {
        $dsnParser = new DsnParser(['mysql' => 'mysqli', 'postgresql' => 'pdo_pgsql']);
        $connectionParams = $dsnParser->parse($dsn);
        $conn = DriverManager::getConnection($connectionParams);

        try {
            $conn->connect();

            return $conn->executeQuery('SELECT version()')->fetchOne();
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Generate database migration.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws Exception
     */
    protected function dbMakeMigration(InputInterface $input, OutputInterface $output): int
    {
        $dotenv = new Dotenv();
        $dotenv->load(dirname(__DIR__).'/../.env');

        $kernel = new Kernel(self::getEnvironment(), false);
        $app = new Application($kernel);

        // run the maker command
        $makeMigrationInput = new ArrayInput([
            'command' => 'make:migration',
        ]);
        $app->setAutoExit(false);

        return $app->run($makeMigrationInput, $output);
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
}
