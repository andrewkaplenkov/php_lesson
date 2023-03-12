<?php

use App\Container\DIContainer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/vendor/autoload.php';


$container = new DIContainer();


$container->bind(
    LoggerInterface::class,
    (new Logger('blog'))->pushHandler(new StreamHandler(__DIR__ . '/logs/blog.log'))
);

$container->bind(
    PDO::class,
    new PDO('sqlite: blog.sqlite')
);




return $container;
