<?php

namespace App\Core\Routing;

use App\Core\Auth;
use Exception;
use App\Core\Routing\Exception\RoutingException;

class RouteBinding
{

    protected $routes;

    public function __construct($routes)
    {
        $this->routes = $routes;
    }

    /**
     * Load the requested URI's associated controller method.
     *
     * @param string $uri
     * @param string $requestType
     */
    public function direct($uri, $requestType)
    {
        Auth::routeGuardian($this->routes[$requestType][$uri]['middleware']);

        if (array_key_exists($uri, $this->routes[$requestType])) {
            if (is_callable($this->routes[$requestType][$uri]['action'])) {
                call_user_func($this->routes[$requestType][$uri]['action']);
                die();
            } else {
                Auth::routeGuardian($this->routes[$requestType][$uri]['middleware']);

                $splat  = explode('@', $this->routes[$requestType][$uri]['action']);
                return $this->callAction($splat[0], $splat[1]);
            }
        } else {
            foreach ($this->routes[$requestType] as $key => $val) {
                $pattern = preg_replace('#\(/\)#', '/?', $key);
                $pattern = "@^" . preg_replace('/{([a-zA-Z0-9\_\-]+)}/', '(?<$1>[a-zA-Z0-9\_\-]+)', $pattern) . "$@D";
                preg_match($pattern, $uri, $matches);
                array_shift($matches);

                if ($matches) {
                    if (is_callable($val['action'])) {
                        $param_array = array_filter($matches, 'is_int', ARRAY_FILTER_USE_KEY);
                        call_user_func_array($val['action'], $param_array);
                        die();
                    } else {
                        Auth::routeGuardian($val['middleware']);

                        $getAction = explode('@', $val['action']);
                        return $this->callAction($getAction[0], $getAction[1], $matches);
                    }
                }
            }
        }

        throw new RoutingException("No route defined for [{$uri}]", new Exception());
    }

    /**
     * Load and call the relevant controller action.
     *
     * @param string $controller
     * @param string $action
     */
    protected function callAction($controller, $action, $paramerters = [])
    {
        $param_array = array_filter($paramerters, 'is_int', ARRAY_FILTER_USE_KEY);

        if (class_exists($controller)) {
            throw new RoutingException("Controller [{$controller}] already exist.", new Exception());
        }

        $useController = "App\\Controllers\\{$controller}";
        $controllerClass = new $useController;

        if (!method_exists($controllerClass, $action)) {
            throw new RoutingException("{$controller} does not respond to the [{$action}] action.", new Exception());
        }

        if (!empty($param_array)) {
            return call_user_func_array([$controllerClass, $action], $param_array);
        } else {
            return call_user_func([$controllerClass, $action]);
        }
    }
}
