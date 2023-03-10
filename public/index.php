<?php

use Alura\Mvc\Controller\Error404Controller;
use Alura\Mvc\Repository\VideoRepository;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

require_once __DIR__ . '/../vendor/autoload.php';

$dbPath = __DIR__ . '/../banco.sqlite';
$pdo = new PDO("sqlite:$dbPath");
$repository = new VideoRepository($pdo);

$routes = require_once __DIR__ . '/../config/routes.php';
$diContainer = require_once __DIR__ . '/../config/dependencies.php';

$httpMethod = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';

session_start();
session_regenerate_id();

$isLogginRoute = $path=== '/login';

if (!array_key_exists('logado', $_SESSION) && !$isLogginRoute) {
    header('Location: /login');
}

$key = "$httpMethod|$path";

if (array_key_exists($key, $routes)) {
    $controllerClass = $routes[$key];
    $controller =  $diContainer->get($controllerClass);
} else {
    $controller = new Error404Controller();
}

$psr17Factory = new Psr17Factory();

$creator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$request = $creator->fromGlobals();

$response = $controller->handle($request);

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {  
        header (sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
