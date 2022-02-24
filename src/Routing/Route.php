<?php

namespace App\Core\Routing;

use Exception;
use App\Core\App;
use App\Core\Routing\RouteBinding;

class Route
{
	/**
	 * All registered routes.
	 *
	 * @var array
	 */
	public static $routes = [
		'GET' => [],
		'POST' => [],
		'DELETE' => [],
		'PUT' => [],
		'PATCH' => [],
		'OPTIONS' => []
	];

	protected static $currentGroupPrefix = '';
	protected static $currentGroupController = '';
	protected static $currentGroupMiddleware = [];

	public function __construct()
	{
		$this->routeBinder = new RouteBinding(static::$routes);
	}

	/**
	 * Register a method specified.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public static function addRoute($method, $uri, $callback)
	{
		$groupPrefix = static::$currentGroupPrefix;
		$groupController = static::$currentGroupController;

		if ($groupPrefix == "") {
			$_uri = $uri;

			$callbackIsCallable = (is_callable($callback))
				? $callback // if callback is a function
				: ((is_array($callback))
					? (($groupController != "")
						? static::buildAction($callback[0]) // if callback passes through groupController
						: $callback[0]) // return the callback directly
					: static::buildAction($callback)); // if string build controller and method

			$middleware = [];
			if (!empty(static::$currentGroupMiddleware)) {
				$middleware = static::$currentGroupMiddleware;
			} else {
				if (!is_callable($callback)) {
					if (is_array($callback)) {
						if (!empty($callback[1])) {
							$middleware = $callback[1]; // if callback is array and !empty  element [1]
						}
					}
				}
			}

			$actions = [
				"action" => $callbackIsCallable,
				"middleware" => $middleware
			];
		} else {
			$_uri = static::groupUriBuilder($uri);

			$controller = (is_array($callback))
				? (($groupController != "")
					? static::buildAction($callback[0]) // if !empty groupController, build controller and method
					: $callback[0]) // if empty groupController
				: static::buildAction($callback); // if !array, build controller and method

			$middleware = (!empty(static::$currentGroupMiddleware))
				? static::$currentGroupMiddleware // check if currentGroupMiddleware is !empty
				: ((is_array($callback)) // check if callback is array
					? ((!empty($callback[1])) // if middleware is present
						? $callback[1] // return middleware array
						: []) // return empty array
					: []); // if callback is not array return empty array

			$actions = [
				"action" => $controller,
				"middleware" => $middleware
			];
		}

		static::$routes[$method][$_uri] = $actions;
	}

	/**
	 * this will build the uri of the grouped routes.
	 *
	 * @param string $uri
	 */
	public static function groupUriBuilder($uri)
	{
		if ($uri == '/') {
			$_uri = "/" . static::$currentGroupPrefix;
		} else {
			$_uri = "/" . static::$currentGroupPrefix . $uri;
		}

		return $_uri;
	}

	public static function buildAction($callback)
	{
		return static::$currentGroupController . '@' . $callback;
	}

	/**
	 * Register a GET route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public static function get($uri, $callback)
	{
		static::addRoute('GET', $uri, $callback);
	}

	/**
	 * Register a POST route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public static function post($uri, $callback)
	{
		static::addRoute('POST', $uri, $callback);
	}

	/**
	 * Register a DELETE route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public static function delete($uri, $callback)
	{
		static::addRoute('DELETE', $uri, $callback);
	}

	/**
	 * Register a PUT route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public static function put($uri, $callback)
	{
		static::addRoute('PUT', $uri, $callback);
	}

	/**
	 * Register a PATCH route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public static function patch($uri, $callback)
	{
		static::addRoute('PATCH', $uri, $callback);
	}

	/**
	 * Register a OPTIONS route.
	 *
	 * @param string $uri
	 * @param mixed $callback
	 */
	public static function options($uri, $callback)
	{
		static::addRoute('OPTIONS', $uri, $callback);
	}

	/**
	 * Register a route group
	 *
	 * @param array $prefix
	 * @param mixed $callback
	 */
	public static function group($param, $callback)
	{
		$previousGroupPrefix = static::$currentGroupPrefix;
		$previousGroupMiddleware = static::$currentGroupMiddleware;

		static::$currentGroupPrefix = $previousGroupPrefix . $param['prefix'];
		static::$currentGroupMiddleware = (!empty($param['middleware'])) ? $param['middleware'] : [];

		call_user_func($callback);

		static::$currentGroupPrefix = $previousGroupPrefix;
		static::$currentGroupMiddleware = $previousGroupMiddleware;
	}

	/**
	 * Register routes to collection
	 *
	 * @param string $uri
	 * @param string $requestType
	 */
	public static function register($uri, $requestType)
	{
		$self = new static;
		$self->routeBinder->direct($uri, $requestType);
	}

	/**
	 * get a list of routes in the collection
	 *
	 */
	public static function uriCollection()
	{
		return static::$routes;
	}

	/**
	 * Register a controller group
	 *
	 * @param string $controller
	 * @param mixed $callback
	 */
	public static function controller($param, $callback)
	{
		$previousGroupController = static::$currentGroupController;
		$previousGroupMiddleware = static::$currentGroupMiddleware;

		static::$currentGroupController = $param[0];
		if (empty($previousGroupMiddleware)) {
			static::$currentGroupMiddleware = (!empty($param[1])) ? $param[1] : [];
		}

		call_user_func($callback);

		static::$currentGroupController = $previousGroupController;
		static::$currentGroupMiddleware = $previousGroupMiddleware;
	}
}
