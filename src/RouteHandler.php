<?php

namespace App;

use App\Core\DependencyContainer;

class RouteHandler
{

    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function handleRoute()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $route = $this->normalizeRoute($uri);

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        foreach ($this->routes as $config) {
            $path = $config['route'];
            $aliases = $config['aliases'] ?? [];
            $allowedMethods = [$config['method'] ?? 'GET'];

            if ($path == $route || in_array($route, $aliases)) {
                if (!in_array($method, $allowedMethods)) {
                    continue;
                }

                if ($method === 'POST') {
                    validateCsrfToken();
                }

                $controllerClass = $config['controller'][0];
                $controllerMethod = $config['controller'][1] ?? 'index';

                if (isset($config['auth']) && $config['auth'] === true) {
                    if (!isLoggedIn()) {
                        return $this->redirectToIndex();
                    }
                }

                $controller = DependencyContainer::get($controllerClass);

                return call_user_func([$controller, $controllerMethod]);
            }
        }

        return $this->redirectToIndex();
    }
    
    private function normalizeRoute(string $route): string
    {
        return '/' . trim($route, '/');
    }

    private function redirectToIndex()
    {
        header('Location: /');
        exit();
    }
}
