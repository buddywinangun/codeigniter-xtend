<?php

/**
 * This file is part of Codeigniter Xtend Framework, for CodeIgniter 3.
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework;

use CodeigniterXtend\Framework\Route\RouteBuilder as Route;

// $hook['pre_system'][] = [new CodeigniterXtend\Framework\Autoloader\Autoloader, 'register'];
// $hook['pre_controller_constructor'][] = [new CodeigniterXtend\Framework\Package\Package, 'init'];
// $hook['pre_controller_constructor'][] = [new CodeigniterXtend\Framework\View\Template, 'init'];
class Hook
{
	public static function getHooks($config = null)
	{
		$hooks = [];

		$hooks['pre_system'][] = function () use ($config) {
			self::preSystemHook($config);
		};

		$hooks['pre_controller'][] = function () {
			global $params, $URI, $class, $method;
			self::preControllerHook($params, $URI, $class, $method);
		};

		$hooks['post_controller_constructor'][] = function () use ($config) {
			global $params;
			self::postControllerConstructorHook($config, $params);
		};

		$hooks['post_controller'][] = function () use ($config) {
			self::postControllerHook($config);
		};

		$hooks['display_override'][] = function () {
			self::displayOverrideHook();
		};

		return $hooks;
	}

	/**
	 * "pre_system" hook
	 *
	 * @param array $config
	 * @return void
	 */
	private static function preSystemHook($config)
	{
		$isAjax =  isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
			&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

		$isCli  =  is_cli();

		require_once __DIR__ . '/Facades/Route.php';

		if (!file_exists(APPPATH . '/routes')) {
			mkdir(APPPATH . '/routes');
		}

		if (!file_exists(APPPATH . '/middleware')) {
			mkdir(APPPATH . '/middleware');
		}

		// Compiling all routes
		Route::compileAll();

		// HTTP verb tweak
		//
		// (This allows us to use any HTTP Verb if the form contains a hidden field
		// named "_method")
		if (isset($_SERVER['REQUEST_METHOD'])) {
			if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && isset($_POST['_method'])) {
				$_SERVER['REQUEST_METHOD'] = $_POST['_method'];
			}

			$requestMethod = $_SERVER['REQUEST_METHOD'];
		} else {
			$requestMethod = 'CLI';
		}

		// Getting the current url
		$url = Utils::currentUrl();

		try {
			$currentRoute = Route::getByUrl($url);
		} catch (RouteNotFoundException $e) {
			Route::$compiled['routes'][$url] = Route::DEFAULT_CONTROLLER . '/index';
			$currentRoute =  Route::{!is_cli() ? 'any' : 'cli'}($url, function () {
				if (!is_cli() && is_callable(Route::get404())) {
					$_404 = Route::get404();
					call_user_func($_404);
				} else {
					show_404();
				}
			});
			$currentRoute->is404 = true;
			$currentRoute->isCli = is_cli();
		};

		$currentRoute->requestMethod = $requestMethod;

		Route::setCurrentRoute($currentRoute);
	}
}
