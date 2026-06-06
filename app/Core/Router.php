<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(string $uri, string $method): void
    {
        $parsedUrl = parse_url($uri);
        $path = $parsedUrl['path'] ?? '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                [$class, $function] = $route['handler'];
                $controller = new $class();
                $controller->$function();
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
