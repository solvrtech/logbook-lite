<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

// the name of the command is what users type after "php console"
#[AsCommand(
    name: 'build',
    description: 'Compile the Logbook application into the dist directory.'
)]
class BuildCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fileSystem = new Filesystem();
        $path = dirname(__DIR__);
        $distDir = $path.'/../dist';

        if (!self::isAngularCLIInstalled()) {
            $io->error('Build was failed: angular CLI is not installed!');

            return Command::FAILURE;
        }

        // create dist directory if it does not exist
        if (!$fileSystem->exists($distDir)) {
            $fileSystem->mkdir($distDir);
        }

        $logbookDir = $distDir.'/logbook';

        if ($fileSystem->exists($logbookDir)) {
            $fileSystem->remove($logbookDir);
        }

        $rsync = "rsync -av --progress %s %s %s";
        $exclude = "--exclude vendor --exclude var --exclude '.env.local' --exclude migrations/'*.php*' --exclude public/api --exclude composer.lock";
        shell_exec(sprintf($rsync, $path.'/backend/', $logbookDir, $exclude));
        self::frontendBuild($path);

        // mkdir asset directory
        $fileSystem->mkdir($logbookDir.'/var');
        $fileSystem->mkdir($logbookDir.'/public/api/backups');
        $fileSystem->mkdir($logbookDir.'/public/api/uploads/logo');

        $this->prodEnvironment($distDir.'/logbook/');
        $io->success('Logbook application has been built.');

        return Command::SUCCESS;
    }

    /**
     * Check if Angular CLI is installed.
     *
     * @return bool
     */
    protected function isAngularCLIInstalled(): bool
    {
        $output = shell_exec('ng v');

        return strpos($output, 'Angular CLI');
    }

    /**
     * Build the frontend assets for the LogBook application.
     *
     * @param string $path
     */
    protected function frontendBuild(string $path): void
    {
        // build frontend
        shell_exec(
            sprintf(
                "cd %s && ng build -c production --aot --output-hashing=all",
                $path.'/frontend'
            )
        );

        // copy built frontend to dist/logbook/public folder
        shell_exec(
            sprintf(
                "cp -R %s %s",
                $path.'/frontend/dist/frontend',
                $path.'/../dist/logbook/public'
            )
        );
    }

    /**
     * Replace the environment to prod mode.
     *
     * @param string $dir
     */
    protected function prodEnvironment(string $dir): void
    {
        $path = $dir.'.env';
        $key = 'APP_ENV';

        $content = file_get_contents($path);
        $pattern = '/^'.preg_quote($key).'=.*/m';
        $replacement = $key.'=prod';
        $modifiedContent = preg_replace($pattern, $replacement, $content);

        file_put_contents($path, $modifiedContent);
    }
}
