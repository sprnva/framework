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

		if ($groupPrefix == "") {
			$_uri = $uri;

			$callbackIsCallable = (is_callable($callback)) ? $callback : $callback[0];
			$middleware = [];

			if (!is_callable($callback)) {
				if (!empty($callback[1])) {
					$middleware = $callback[1];
				}
			}

			$actions = [
				"action" => $callbackIsCallable,
				"middleware" => $middleware
			];
		} else {
			$_uri = static::groupUriBuilder($uri);

			$actions = [
				"action" => $callback[0],
				"middleware" => static::$currentGroupMiddleware
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
	public static function list()
	{
		return static::$routes;
	}
}
