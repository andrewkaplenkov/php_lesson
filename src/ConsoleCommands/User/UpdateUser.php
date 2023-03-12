<?php

namespace App\ConsoleCommands\User;

use App\ConsoleCommands\Auth\Identification;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\Models\UUID;
use App\PDO\User\UserController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUser extends Command
{
    public function __construct(
        private UserController $userController,
        private Identification $identification

    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('users:update')
            ->setDescription('Updates existing user')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Password'
            )
            ->addOption(
                'name',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $username = $input->getArgument('username');

        try {
            $user = $this->identification->user($username);
        } catch (UserNotFoundException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        if (
            empty($input->getOption('password'))
            && empty($input->getOption('name'))
        ) {
            $output->writeln('Nothing to update');
            return Command::SUCCESS;
        }

        $password = $input->getOption('password');

        $updatedUser = new User(
            $user->id(),
            $username,
            $password ? User::encrypt($password, $user->id()) : $user->password()
        );

        $updatedUser->setName($input->getOption('name'));

        try {
            $this->userController->save($updatedUser);
            $output->writeln("User updated: " . $user->username());
        } catch (UserAlreadyExistsException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
