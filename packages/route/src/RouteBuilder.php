<?php

namespace CodeigniterXtend\Route;

use CodeigniterXtend\Route\Exception\RouteNotFoundException;

class RouteBuilder
{
  const DEFAULT_CONTROLLER = 'Welcome';

  const HTTP_VERBS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'TRACE'];

  /**
   * @var Route[]
   */
  private static $routes = [];

  /**
   * @var array[]
   */
  private static $context = [
    'middleware' =>
    [
      'route'  => [],
      'global' =>
      [
        'pre_controller'  => [],
        'controller'      => [],
        'post_controller' => [],
      ],
    ],
    'namespace' => [],
    'prefix'    => [],
    'params'    => [],
  ];

  /**
   * @var array[]
   */
  public static $compiled = [
    'routes'   => [],
    'paths'    => [],
    'names'    => [],
    'reserved' => [],
  ];

  /**
   * @var Route
   */
  private static $current;

  /**
   * @var string|callable
   */
  private static $autoRoute = false;

  public static function __callStatic($callback, array $args)
  {
    if ($callback === 'setAutoRoute') {
      return;
    }

    if (is_cli() && $callback != 'cli' || !is_cli() && $callback == 'cli' || (!is_cli() && is_array($callback) && in_array('CLI', $callback))) {
      show_error('You only can define CLI routes in CLI context. Please define this route using the Route::cli() method in your routes/cli.php file instead');
    }

    if ($callback == 'match') {
      $methods = $args[0];
    } else {
      $methods = $callback;
    }

    if (!in_array(strtoupper($callback), self::HTTP_VERBS, true) && !in_array($callback, ['any', 'match', true])) {
      show_error("Call to undefined RouteBuilder::{$callback()} method", 500, 'Route builder error');
    }

    $route = new Route($methods, $args);

    self::$routes[] = $route;

    return $route;
  }

  /**
   * Allow to match URI against the controllers and methods
   *
   * @param boolean          $active
   *
   * @return void
   */
  public static function setAutoRoute($active)
  {
    self::$autoRoute = $active;
  }

  /**
   * Creates a new route group
   *
   * @param string          $prefix
   * @param callable|array  $attributes
   * @param callable|null   $routes
   *
   * @return void
   */
  public static function group($prefix, $attributes, $routes = null)
  {
    if ($routes === null && is_callable($attributes)) {
      $routes     = $attributes;
      $attributes = [];
    }

    self::$context['prefix'][] = $prefix;

    if (isset($attributes['namespace'])) {
      self::$context['namespace'][] = $attributes['namespace'];
    }

    if (isset($attributes['middleware'])) {
      if (is_string($attributes['middleware'])) {
        $attributes['middleware'] = [$attributes['middleware']];
      } else {
        if (!is_array($attributes['middleware'])) {
          show_error('Route group middleware must be an array o a string');
        }
      }
      self::$context['middleware']['route'][] = $attributes['middleware'];
    }

    call_user_func($routes);

    array_pop(self::$context['prefix']);

    if (isset($attributes['namespace'])) {
      array_pop(self::$context['namespace']);
    }

    if (isset($attributes['middleware'])) {
      array_pop(self::$context['middleware']['route']);
    }
  }

  /**
   * Compiles all routes
   *
   * @return void
   */
  public static function compileAll()
  {
    $routes = [];

    foreach (self::$routes as $route) {
      $routeName = $route->getName();

      if ($routeName !== null) {
        if (!isset(self::$compiled['names'][$routeName])) {
          self::$compiled['names'][$routeName] = clone $route;
        } else {
          show_error('Duplicated "<strong>' . $routeName . '</strong>" named route');
        }
      }

      foreach ($route->compile() as $compiled) {
        foreach ($compiled as $path => $action) {
          foreach ($action as $method => $target) {
            $routes[$path][$method] = $target;

            $routePlaceholders = RouteParam::getPlaceholderReplacements();
            $regexPath = implode('\\/', explode('/', $path));
            $regexPath = preg_replace(array_keys($routePlaceholders), array_values($routePlaceholders), $regexPath);
            self::$compiled['paths']['#^' . $regexPath . '$#'][] = clone $route;
          }
        }
      }
    }

    $routes['default_controller']   = isset(self::$compiled['reserved']['default_controller']) ?
      self::$compiled['reserved']['default_controller'] : null;

    $routes['translate_uri_dashes'] = isset(self::$compiled['reserved']['translate_uri_dashes']) ?
      self::$compiled['reserved']['translate_uri_dashes'] : FALSE;

    $routes['404_override'] = isset(self::$compiled['reserved']['404_override']) ?
      self::$compiled['reserved']['404_override'] : '';

    self::$compiled['routes'] = $routes;
  }

  /**
   * Gets the static context of the route builder
   *
   * @param string $context Context index
   *
   * @return mixed
   */
  public static function getContext($context)
  {
    return self::$context[$context];
  }

  /**
   * Gets the matching route of the provided URL
   *
   * @param string $url
   * @param string $requestMethod
   *
   * @throws RouteNotFoundException
   *
   * @return Route
   */
  public static function getByUrl($url, $requestMethod = null)
  {
    if ($requestMethod === null || empty($requestMethod)) {
      $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : (!is_cli() ? 'GET' : 'CLI');
    } else {
      $requestMethod = strtoupper($requestMethod);
    }

    // First, look for a direct match:
    $urlRegex = '#^' . str_replace('/', '\\/', $url) . '$#';

    if (isset(self::$compiled['paths'][$urlRegex])) {
      foreach (self::$compiled['paths'][$urlRegex] as $route) {
        if (in_array($requestMethod, $route->getMethods())) {
          return $route;
        }
      }
    }

    // Then, loop into the array of compiled path
    foreach (self::$compiled['paths'] as $path => $routes) {
      if (preg_match($path, $url)) {
        foreach ($routes as $route) {
          if (in_array($requestMethod, $route->getMethods())) {
            return $route;
          }
        }
      }
    }

    if (self::$autoRoute) {

      $segments = explode('/', $url);

      if (count($segments) >= 3) {
        $prefix = $segments[0];
        $class  = $segments[1];
        $method = $segments[2];
        $params = array_slice($segments, 3);

        if (is_dir(APPPATH . 'controllers/' . $prefix) && file_exists(APPPATH . 'controllers/' . $prefix . '/' . ucfirst($class) . '.php')) {
          self::$context['namespace'][] = $prefix;
          self::$context['prefix'][] = $prefix;

          $options = [
            0 => $url,
            1 => ucfirst($class) . '@' . $method
          ];

          foreach (self::$routes as $existingRoute) {
            if ($existingRoute->getPath() === '/' && $existingRoute->getFullPath() === $prefix) {
              $middlewares = $existingRoute->getMiddleware();
              $options[2]['middleware'] = $middlewares[0];
            }
          }

          $route = new Route('any', $options);

          return $route;
        }
      } else if (count($segments) >= 2) {
        $class  = $segments[0];
        $method = $segments[1];
        $params = array_slice($segments, 2);

        if (file_exists(APPPATH . 'controllers/' . ucfirst($class) . '.php')) {
          $route = new Route('any', [
            0 => $url,
            1 => ucfirst($class) . '@' . $method
          ]);

          return $route;
        }
      }
    }

    throw new RouteNotFoundException;
  }

  /**
   * Gets a route by its name
   *
   * @param string $name Route name to search
   *
   * @throws RouteNotFoundException
   *
   * @return Route
   */
  public static function getByName($name)
  {
    if (isset(self::$compiled['names'][$name])) {
      return self::$compiled['names'][$name];
    }

    throw new RouteNotFoundException;
  }

  /**
   * Gets all compiled routes
   *
   * @return string[]
   */
  public static function getRoutes()
  {
    return self::$compiled['routes'];
  }

  /**
   * Sets the current route
   *
   * @param Route $route
   *
   * @return void
   */
  public static function setCurrentRoute(Route $route)
  {
    self::$current = $route;
  }

  /**
   * Gets the current route
   *
   * @return Route|null
   */
  public static function getCurrentRoute()
  {
    return self::$current;
  }

  /**
   * Gets the global middleware
   *
   * @return array
   */
  public static function getGlobalMiddleware()
  {
    return self::$context['middleware']['global'];
  }

  /**
   * Gets all (global) default sticky parameters values
   * @return string
   */
  public static function getDefaultParams()
  {
    return self::$context['params'];
  }

  /**
   * Gets the custom 404 controller/callback
   *
   * @return string|callable|NULL
   */
  public static function get404()
  {
    if (self::$_404 !== null) {
      return self::$_404;
    }

    return isset(self::$compiled['reserved']['404_override']) ?
      self::$compiled['reserved']['404_override'] : null;
  }
}
