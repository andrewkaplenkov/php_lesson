<?php

namespace App\ConsoleCommands\Auth;

use App\Exceptions\User\AuthException;
use App\Models\AuthToken;
use App\PDO\Auth\AuthController;
use DateTimeImmutable;
use PDOException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Login extends Command
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
            ->setName('users:login')
            ->setDescription('Authorization')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password =  $input->getArgument('password');

        try {
            $user = $this->identification->passwordCheck($username, $password);
        } catch (AuthException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        $authToken = new AuthToken(
            bin2hex(random_bytes(40)),
            $user->id(),
            (new DateTimeImmutable())->modify('+1 day')
        );

        try {
            $this->authController->save($authToken);
            $output->writeln("User logged in: " . $user->username());
        } catch (PDOException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
