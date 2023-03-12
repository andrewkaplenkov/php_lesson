<?php

namespace App\ConsoleCommands\Auth;

use App\Exceptions\User\AuthException;
use App\PDO\Auth\AuthController;
use PDOException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Logout extends Command
{
    public function __construct(
        private AuthController $authController,
        private Identification $identification
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('users:logout')
            ->setDescription('Logging out')
            ->addArgument('username', InputArgument::REQUIRED, 'Username');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        try {
            $user = $this->identification->user($username);
        } catch (AuthException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        try {
            $token = $this->authController->get($user->id());
        } catch (PDOException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }



        $this->authController->delete($token);
        $output->writeln("User logged out: " . $user->username());


        return Command::SUCCESS;
    }
}
