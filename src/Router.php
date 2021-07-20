<?php

namespace App\Core;

use Exception;

class Router
{
	/**
	 * All registered routes.
	 *
	 * @var array
	 */
	public $routes = [
		'GET' => [],
		'POST' => [],
		'DELETE' => [],
		'PUT' => [],
		'PATCH' => [],
		'OPTIONS' => []
	];

	protected $currentGroupPrefix = '';
	protected $currentGroupMiddleware = [];

	/**
	 * Load a user's routes file.
	 *
	 * @param string $file
	 */
	public static function load($file)
	{
		$router = new static;
		require $file;

		return $router;
	}

	/**
	 * Register a method specified.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public function match($method, $uri, $callback)
	{
		if ($this->currentGroupPrefix == "") {
			$_uri = $uri;
		} else {
			if ($uri == '/') {
				$_uri = "/" . $this->currentGroupPrefix;
			} else {
				$_uri = "/" . $this->currentGroupPrefix . $uri;
			}
		}

		$this->routes[$method][$_uri] = [$this->currentGroupMiddleware, $callback];
	}

	/**
	 * Register a GET route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public function get($uri, $callback)
	{
		$this->match('GET', $uri, $callback);
	}

	/**
	 * Register a POST route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public function post($uri, $callback)
	{
		$this->match('POST', $uri, $callback);
	}

	/**
	 * Register a DELETE route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public function delete($uri, $callback)
	{
		$this->match('DELETE', $uri, $callback);
	}

	/**
	 * Register a PUT route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public function put($uri, $callback)
	{
		$this->match('PUT', $uri, $callback);
	}

	/**
	 * Register a PATCH route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public function patch($uri, $callback)
	{
		$this->match('PATCH', $uri, $callback);
	}

	/**
	 * Register a OPTIONS route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public function options($uri, $callback)
	{
		$this->match('OPTIONS', $uri, $callback);
	}

	/**
	 * Register a route group
	 *
	 * @param array $prefix
	 * @param mixed $callback
	 */
	public function group($param, $callback)
	{
		$previousGroupPrefix = $this->currentGroupPrefix;
		$previousGroupMiddleware = $this->currentGroupMiddleware;

		$this->currentGroupPrefix = $previousGroupPrefix . $param['prefix'];
		$this->currentGroupMiddleware = (!empty($param['middleware'])) ? $param['middleware'] : [];

		$callback($this);

		$this->currentGroupPrefix = $previousGroupPrefix;
		$this->currentGroupMiddleware = $previousGroupMiddleware;
	}

	/**
	 * Load the requested URI's associated controller method.
	 *
	 * @param string $uri
	 * @param string $requestType
	 */
	public function direct($uri, $requestType)
	{
		Auth::routeGuardian($this->routes[$requestType][$uri][0]);

		if (array_key_exists($uri, $this->routes[$requestType])) {
			if (is_callable($this->routes[$requestType][$uri][1])) {
				call_user_func($this->routes[$requestType][$uri][1]);
				die();
			} else {
				Auth::routeGuardian([$this->routes[$requestType][$uri][1][1]]);

				$splat  = explode('@', $this->routes[$requestType][$uri][1][0]);
				return $this->callAction($splat[0], $splat[1]);
			}
		} else {
			foreach ($this->routes[$requestType] as $key => $val) {
				$pattern = preg_replace('#\(/\)#', '/?', $key);
				$pattern = "@^" . preg_replace('/{([a-zA-Z0-9\_\-]+)}/', '(?<$1>[a-zA-Z0-9\_\-]+)', $pattern) . "$@D";
				preg_match($pattern, $uri, $matches);
				array_shift($matches);

				if ($matches) {
					if (is_callable($val[1])) {
						$param_array = array_filter($matches, 'is_int', ARRAY_FILTER_USE_KEY);
						call_user_func_array($val[1], $param_array);
						die();
					} else {
						Auth::routeGuardian([$val[1][1]]);

						$getAction = explode('@', $val[1][0]);
						return $this->callAction($getAction[0], $getAction[1], $matches);
					}
				}
			}
		}

		throwException("No route defined for [{$uri}]", new Exception());
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
			throwException("Controller [{$controller}] already exist.", new Exception());
		}

		$useController = "App\\Controllers\\{$controller}";
		$controllerClass = new $useController;

		if (!method_exists($controllerClass, $action)) {

			throwException("{$controller} does not respond to the [{$action}] action.", new Exception());
		}

		if (!empty($param_array)) {
			return call_user_func_array([$controllerClass, $action], $param_array);
		} else {
			return call_user_func([$controllerClass, $action]);
		}
	}
}
