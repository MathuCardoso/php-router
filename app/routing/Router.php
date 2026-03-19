<?php

namespace App\routing;

use BadMethodCallException;
use RuntimeException;

class Router
{
	private static array $routes = [];


	public static function get(string $uri, callable|array $action)
	{
		self::$routes[] = [
			'httpMethod' => 'GET',
			'uri' => $uri,
			'action' => $action,
			'params' => self::getParams($uri) ?? null,
		];
	}

	public static function post(string $uri, array|callable $action)
	{
		self::$routes[] = [
			'httpMethod' => 'POST',
			'uri' => $uri,
			'action' => $action,
			'params' => self::getParams($uri) ?? null,
		];
	}

	public static function put(string $uri, array|callable $action)
	{
		if (isset($_POST['__method']) && $_POST['__method'] === 'PUT') {
			$_SERVER['REQUEST_METHOD'] = 'PUT';

			self::$routes[] = [
				'httpMethod' => 'PUT',
				'uri' => $uri,
				'action' => $action,
				'params' => self::getParams($uri) ?? null,
			];
		}
	}

	public static function delete(string $uri, array|callable $action)
	{
		if (isset($_POST['__method']) && $_POST['__method'] === 'DELETE') {
			$_SERVER['REQUEST_METHOD'] = 'DELETE';

			self::$routes[] = [
				'httpMethod' => 'DELETE',
				'uri' => $uri,
				'action' => $action,
				'params' => self::getParams($uri) ?? null,
			];
		}
	}


	private static function getParams(string $uri)
	{
		$params = [];
		if ($_SERVER['REQUEST_URI'] !== '/') {
			$requestedUri = explode('/', ltrim($uri, '/'));
			$serverUri = explode('/', parse_url(ltrim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH));

			foreach ($requestedUri as $index => $seg) {
				if (str_contains($seg, '{')) {
					$param = ltrim(rtrim($seg, '}'), '{');

					$params[$param] = $serverUri[$index] ?? '';
				}
			}

			return $params ?? null;
		}
	}

	private static function isTheRightRoute(array $route, string $uriFixed)
	{
		$requestedUriSegments = array_filter(explode('/', trim($uriFixed, '/')));
		$serverUriSegments = array_filter(explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')));
		$serverUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

		$reqUriCount = \count($requestedUriSegments);
		$serUriCount = \count($serverUriSegments);

		if (($reqUriCount === $serUriCount)
			&& (str_starts_with($uriFixed, $serverUri))
			&& ($_SERVER['REQUEST_METHOD'] === $route['httpMethod'])
		) {
			return true;
		}

		return false;
	}

	private static function getFixedUri(string $uri)
	{
		$requestedUri = explode('/', ltrim($uri, '/'));
		$serverUri = explode('/', parse_url(ltrim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH));

		$fixedUri = '/';
		foreach ($requestedUri as $index => $seg) {
			if (str_contains($seg, '{') && isset($serverUri[$index])) {
				$fixedUri .= "{$serverUri[$index]}/";

				continue;
			}

			$fixedUri .= "{$seg}/";
		}

		return rtrim($fixedUri, '/') ?: '/';
	}

	public static function dispatch()
	{
		foreach (self::$routes as $route) {
			$uri = str_contains($route['uri'], '{') ? self::getFixedUri($route['uri']) : (rtrim($route['uri']) ?: '/');

			if (self::isTheRightRoute($route, $uri)) {
				if (is_callable($route['action'])) {
					$route['action']($route['params']);

					return;
				}

				[$className, $method] = $route['action'];

				if (!class_exists($className)) {
					throw new RuntimeException("Classe {$className} não encontrada.");
				}

				if (!method_exists($className, $method)) {
					throw new BadMethodCallException(
						message: "Método {$method} não existe na classe {$className}"
					);
				}

				$obj = new $className();
				$obj->$method($route['params']);

				return;
			}
		}

		http_response_code(404);
    exit("ERRO 404. PÁGINA NÃO ENCONTRADA!");
	}
}
