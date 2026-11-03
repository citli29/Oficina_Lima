<?php
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/../..");
$dotenv->load();

header("Access-Control-Allow-Origin: ". $_ENV['CORS_IP']);
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

use App\Controllers\AuthController;
use App\Database\Database;

// Routes reachable without logging in. Everything else needs the shop
// login token (Authorization: Bearer ...).
const PUBLIC_ROUTES = [
    'POST /api/auth/login',
];

$db = Database::getConnection();
$routes = require __DIR__ . '/../routes/web.php';

$dispatcher = FastRoute\simpleDispatcher(function (
    FastRoute\RouteCollector $r
) use ($routes) {
    foreach ($routes as $route) {
        [$method, $path, $handler] = $route;
        $r->addRoute($method, $path, $handler);
    }
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (($pos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "404 Not Found";
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo "Method Not Allowed";
        break;

    case FastRoute\Dispatcher::FOUND:
        if (!in_array("$httpMethod $uri", PUBLIC_ROUTES, true) && !AuthController::requireAuth($db)) {
            break;
        }

        [$class, $method] = $routeInfo[1];
        $vars = $routeInfo[2];

        $controller = new $class($db);

        call_user_func_array(
            [$controller, $method],
            array_values($vars)
        );
        break;
}
