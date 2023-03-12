<?php

use App\ConsoleCommands\Auth\Login;
use App\ConsoleCommands\Auth\Logout;
use App\ConsoleCommands\User\CreateUser;
use App\ConsoleCommands\User\DeleteUser;
use App\ConsoleCommands\User\UpdateUser;
use Symfony\Component\Console\Application;

$container = require_once __DIR__ . '/container.php';
$application = new Application();


$commandClasses = [
    CreateUser::class,
    DeleteUser::class,
    UpdateUser::class,
    Login::class,
    Logout::class
];

foreach ($commandClasses as $commandClass) {
    $command = $container->get($commandClass);
    $application->add($command);
}

$application->run();
