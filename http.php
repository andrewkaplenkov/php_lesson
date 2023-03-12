<?php

use App\Exceptions\HTTP\HTTPException;
use App\Exceptions\User\AuthException;
use App\HTTP\Actions\Auth\Login;
use App\HTTP\Actions\Auth\Logout;
use App\HTTP\Actions\Comments\CreateComment;
use App\HTTP\Actions\Comments\GetByPostId;
use App\HTTP\Actions\Posts\CreatePost;
use App\HTTP\Actions\Posts\DeletePost;
use App\HTTP\Actions\Posts\GetAllPosts;
use App\HTTP\Actions\Posts\GetPostById;
use App\HTTP\Actions\User\CreateUser;
use App\HTTP\Actions\User\DeleteUser;
use App\HTTP\Actions\User\GetAllUsers;
use App\HTTP\Actions\User\GetUserByUsername;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\PDO\Auth\AuthController;
use Psr\Log\LoggerInterface;

$container = require_once __DIR__ . '/container.php';
$logger = $container->get(LoggerInterface::class);
$request = new Request($_GET, $_SERVER, file_get_contents('php://input'));


$routes = [
    'GET' => [
        '/users' => GetAllUsers::class,
        '/user' => GetUserByUsername::class,
        '/posts' => GetAllPosts::class,
        '/post' => GetPostById::class,
        '/comments' => GetByPostId::class
    ],
    'POST' => [
        '/users/new' => CreateUser::class,
        '/posts/new' => CreatePost::class,
        '/comments/new' => CreateComment::class,
        '/login' => Login::class
    ],
    'DELETE' => [
        '/users/delete' => DeleteUser::class,
        '/logout' => Logout::class,
        '/posts/delete' => DeletePost::class
    ]
];


try {
    $method = $request->method();
    $path = $request->path();
} catch (HTTPException $e) {
    (new ErrorResponse($e->getMessage()))->send();
}

if (!array_key_exists($method, $routes) || !array_key_exists($path, $routes[$method])) {
    (new ErrorResponse('Route error!'))->send();
    return;
}

$actionClassname = $routes[$method][$path];
$action = $container->get($actionClassname);


try {
    $response = $action->handle($request);
} catch (HTTPException $e) {
    (new ErrorResponse($e->getMessage()))->send();
}

$response->send();
