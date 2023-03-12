<?php

namespace App\ConsoleCommands\User;

use App\ConsoleCommands\Auth\Identification;
use App\Exceptions\User\UserNotFoundException;
use App\PDO\User\UserController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeleteUser extends Command
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
            ->setName('users:delete')
            ->setDescription('Deletes user')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addOption(
                'delete-user',
                'd',
                InputOption::VALUE_OPTIONAL,
                'User to delete'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $usernameToDelete = $input->getOption('delete-user') ?: $username;

        $question = new ConfirmationQuestion(
            "Delete user $usernameToDelete [Y/n]? ",
            false
        );

        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        try {
            $user = $this->identification->user($username);
        } catch (UserNotFoundException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        $userToDelete = $this->userController->getByUsername($usernameToDelete);

        if ((string)$user->id() !== (string)$userToDelete->id()) {
            $output->writeln("No access");
            return Command::FAILURE;
        }

        $this->userController->delete($userToDelete);

        $output->writeln("User deleted: $username");
        return Command::SUCCESS;
    }
}
