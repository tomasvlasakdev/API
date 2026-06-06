<?php


spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

if (file_exists(__DIR__ . '/../config/config.php')) {
    include __DIR__ . '/../config/config.php';
}

if (file_exists(__DIR__ . '/../library/functions.php')) {
    include __DIR__ . '/../library/functions.php';
}

use App\Core\Router;

$router = new Router();

require __DIR__ . '/../routes/web.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($uri, $method);
