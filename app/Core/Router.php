<?php

class Router
{
    private static $routes = [];

    public static function get($uri, $action)
    {
        self::addRoute('GET', $uri, $action);
    }

    public static function post($uri, $action)
    {
        self::addRoute('POST', $uri, $action);
    }

    public static function put($uri, $action)
    {
        self::addRoute('PUT', $uri, $action);
    }

    public static function delete($uri, $action)
    {
        self::addRoute('DELETE', $uri, $action);
    }

    private static function addRoute($method, $uri, $action)
    {
        self::$routes[] = [
            'method' => $method,
            'uri' => trim($uri, '/'),
            'action' => $action,
        ];
    }

    public static function dispatch()
    {
        $requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $basePath = trim(dirname($_SERVER['SCRIPT_NAME']), '/');

        if ($basePath && str_starts_with($requestUri, $basePath)) {
            $requestUri = substr($requestUri, strlen($basePath));
            $requestUri = trim($requestUri, '/');
        }

        $requestMethod = $_SERVER['REQUEST_METHOD'];

        foreach (self::$routes as $route) {
            $paramNames = [];


            $pattern = preg_replace_callback('/\{([^\/]+)\}/', function ($matches) use (&$paramNames) {
                $paramNames[] = $matches[1];
                return '([^/]+)';
            }, $route['uri']);

            $pattern = "@^" . $pattern . "$@";

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);


                $params = array_combine($paramNames, $matches);

                if (is_callable($route['action'])) {
                    call_user_func_array($route['action'], [$params]);
                } elseif (is_string($route['action'])) {
                    [$controller, $method] = explode('@', $route['action']);
                    require_once APP_ROOT . "/app/Controllers/{$controller}.php";
                    $controllerInstance = new $controller();
                    call_user_func_array([$controllerInstance, $method], [$params]);
                }

                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
    }
}
