<?php

namespace App\ConsoleCommands\User;

use App\Exceptions\User\UserAlreadyExistsException;
use App\Models\User;
use App\PDO\User\UserController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends Command
{
    public function __construct(
        private UserController $controller
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('users:create')
            ->setDescription('Creates new user')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $user = User::create(
            $input->getArgument('username'),
            $input->getArgument('password')
        );


        try {
            $this->controller->save($user);
            $output->writeln("User created: " . $user->username());
        } catch (UserAlreadyExistsException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
