<?php

namespace App\Command;

use App\Common\CommandStyle;
use App\Model\Request\UserRequest;
use App\Service\User\UserServiceInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create new user',
)]
class UserCreateCommand extends Command
{
    private UserServiceInterface $userService;

    public function __construct(
        UserServiceInterface $userService
    ) {
        $this->userService = $userService;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
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

        $this->createNewUser($io);
        $io->success("New user has been created");

        return Command::SUCCESS;
    }

    public function createNewUser(StyleInterface $io): string|int
    {
        $io->title("Create new user");

        do {
            $user = self::user($io);

            try {
                $this->userService->create($user);

                $fail = false;
            } catch (Exception $exception) {
                $io->error([
                    "Create new user was failed",
                    $exception->getMessage(),
                    'Please try again.',
                ]);

                $fail = true;
            }
        } while ($fail);

        return $user->getEmail();
    }

    /**
     * Create a UserRequest instance based on input.
     *
     * @param StyleInterface $io
     *
     * @return UserRequest
     */
    protected function user(StyleInterface $io): UserRequest
    {
        $user = (new UserRequest())
            ->setEmail($io->ask("Email (e.g. admin@logbook.com)"))
            ->setName($io->ask("Name (e.g. Admin LogBook)"))
            ->setRole("ROLE_ADMIN");
        $passwordFail = false;

        do {
            if ($passwordFail) {
                $io->error(['Password does not match!', 'Please try again.']);
            }

            $password = $io->askHidden("Password");
            $confirmPassword = $io->askHidden("Confirm password");

            $passwordFail = true;
        } while ($password !== $confirmPassword);

        $user->setPassword($password);

        return $user;
    }
}
