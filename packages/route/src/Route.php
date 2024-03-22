<?php

/**
 * This file is part of Codeigniter Xtend Route.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Route;

/**
 * Provides enhanced Routing capabilities to CodeIgniter-based applications.
 */
class Route
{

  /**
   * Our built routes.
   * @var array
   */
  protected static $routes = array();

  protected static $prefix = NULL;

  protected static $named_routes = array();

  protected static $default_home = 'home';

  protected static $pre_route_objects = array();

  protected static $route_objects = array();

  protected static $pattern_list = array();

  protected static $filter_list = array();

  protected static $active_subdomain;

  protected static $subdomain;

  //--------------------------------------------------------------------

  /**
   * Combines the routes that we've defined with the Route class with the
   * routes passed in. This is intended to be used  after all routes have been
   * defined to merge CI's default $route array with our routes.
   *
   * Example:
   *     $route['default_controller'] = 'home';
   *     Route::resource('posts');
   *     $route = Route::map($route);
   *
   * @param  array $routes The array to merge
   * @return array         The merge route array.
   */
  public static function map($routes = array())
  {
    $controller = isset($routes['default_controller']) ? $routes['default_controller'] : self::$default_home;

    //we mount the route object array with all the from routes remade
    foreach (self::$pre_route_objects as &$object) {
      self::$route_objects[$object->get_from()] = &$object;
    }

    self::$pre_route_objects = array();

    foreach (self::$route_objects as $key => $route_object) {
      $add_route = TRUE;
      //if there is a subdomain, we will check if it's ok with the route.
      //all the previously checked subdomain routes should be ignored because
      //we already have checked them
      if ($route_object->get_options('subdomain') != FALSE) {
        $add_route = self::_CheckSubdomain($route_object->get_options('subdomain'));
      }

      if ($add_route) {
        $from = $route_object->get_from();
        $to = $route_object->get_to();

        $routes[$from] = str_replace('{default_controller}', $controller, $to);
      }
    }

    return $routes;
  }

  //--------------------------------------------------------------------

  /**
   * Returns the parameters of the selected route in case of we have defined
   * a parameter. This will be used in the URI library for naming uris purpouse
   *
   * Example:
   *     Route::get_parameters('welcome/index');
   *
   * @param  array $route The string with the route to search
   * @return array         The parameters
   */
  public static function get_parameters($route)
  {
    if (array_key_exists($route, self::$route_objects)) {
      return self::$route_objects[$route]->get_parameters();
    }

    return array();
  }

  //--------------------------------------------------------------------


  /**
   * A single point to the basic routing. Can be used in place of CI's $route
   * array if desired. Used internally by many of the methods.
   *
   * @param string $from
   * @param string $to
   * @return void
   */
  public static function any($from, $to, $options = array(), $nested = FALSE)
  {
    return self::createRoute($from, $to, $options, $nested);
  }

  //--------------------------------------------------------------------

  //--------------------------------------------------------------------
  // HTTP Verb-based routing
  //--------------------------------------------------------------------
  // Verb-based Routing works by only creating routes if the
  // $_SERVER['REQUEST_METHOD'] is the proper type.
  //

  /**
   * @param string $from
   * @param string $to
   */
  public static function get($from, $to, $options = array(), $nested = FALSE)
  {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
      return self::createRoute($from, $to, $options, $nested);
    }
  }

  //--------------------------------------------------------------------

  /**
   * @param string $from
   * @param string $to
   */
  public static function post($from, $to, $options = array(), $nested = FALSE)
  {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
      return self::createRoute($from, $to, $options, $nested);
    }
  }

  //--------------------------------------------------------------------

  /**
   * @param string $from
   * @param string $to
   */
  public static function put($from, $to, $options = array(), $nested = FALSE)
  {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'PUT') {
      return self::createRoute($from, $to, $options, $nested);
    }
  }

  //--------------------------------------------------------------------

  /**
   * @param string $from
   * @param string $to
   */
  public static function delete($from, $to, $options = array(), $nested = FALSE)
  {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
      return self::createRoute($from, $to, $options, $nested);
    }
  }

  //--------------------------------------------------------------------

  public static function head($from, $to, $options = array(), $nested = FALSE)
  {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'HEAD') {
      return self::createRoute($from, $to, $options, $nested);
    }
  }

  //--------------------------------------------------------------------

  public static function patch($from, $to, $options = array(), $nested = FALSE)
  {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'PATCH') {
      return self::createRoute($from, $to, $options, $nested);
    }
  }

  //--------------------------------------------------------------------

  public static function options($from, $to, $options = array(), $nested = FALSE)
  {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      return self::createRoute($from, $to, $options, $nested);
    }
  }

  //--------------------------------------------------------------------

  public static function match(array $requests, $from, $to, $options = array(), $nested = FALSE)
  {
    $return = NULL;

    foreach ($requests as $request) {
      if (method_exists('Route', $request)) {
        $r = self::$request($from, $to, $options, $nested);

        if (!is_null($r)) {
          $return = $r;
        }
      }
    }
    return $return;
  }

  //--------------------------------------------------------------------

  /**
   * Creates HTTP-verb based routing for a controller.
   *
   * Generates the following routes, assuming a controller named 'photos':
   *
   *      Route::resources('photos');
   *
   *      Verb    Path            Action      used for
   *      ------------------------------------------------------------------
   *      GET     /photos         index       displaying a list of photos
   *      GET     /photos/new     create_new  return an HTML form for creating a photo
   *      POST    /photos         create      create a new photo
   *      GET     /photos/{id}    show        display a specific photo
   *      GET     /photos/{id}/edit   edit    return the HTML form for editing a single photo
   *      PUT     /photos/{id}    update      update a specific photo
   *      DELETE  /photos/{id}    delete      delete a specific photo
   *
   * @param  string $name The name of the controller to route to.
   * @param  array $options An list of possible ways to customize the routing.
   */
  public static function resources($name, $options = array(), $nested = FALSE)
  {
    if (empty($name)) {
      return;
    }

    $nest_offset = '';

    // In order to allow customization of the route the
    // resources are sent to, we need to have a new name
    // to store the values in.
    $new_name = $name;

    // If a new controller is specified, then we replace the
    // $name value with the name of the new controller.
    if (isset($options['controller'])) {
      $new_name = $options['controller'];
      unset($options['controller']);
    }

    // If a new module was specified, simply put that path
    // in front of the controller.
    if (isset($options['module'])) {
      $new_name = $options['module'] . '/' . $new_name;
      unset($options['module']);
    }

    // In order to allow customization of allowed id values
    // we need someplace to store them.
    $id = '([a-zA-Z0-9\-_]+)';

    if (isset($options['constraint'])) {
      $id = $options['constraint'];
      unset($options['constraint']);
    }

    // If the 'offset' option is passed in, it means that all of our
    // parameter placeholders in the $to ($1, $2, etc), need to be
    // offset by that amount. This is useful when we're using an API
    // with versioning in the URL.

    $offset = 0;

    if (isset($options['offset'])) {
      $offset = (int) $options['offset'];
      unset($options['offset']);
    }

    if (is_array(self::$prefix) && !empty(self::$prefix)) {
      foreach (self::$prefix as $key => $p) {
        $nest_offset .= '/$' . ($key + 1);
        $offset++;
      }
    }


    self::get($name, $new_name . '/index' . $nest_offset, $options, $nested);
    self::get($name . '/new', $new_name . '/create_new' . $nest_offset, $options, $nested);
    self::get($name . '/' . $id . '/edit', $new_name . '/edit' . $nest_offset . '/$' . (1 + $offset), $options, $nested);
    self::get($name . '/' . $id, $new_name . '/show' . $nest_offset . '/$' . (1 + $offset), $options, $nested);
    self::post($name, $new_name . '/create' . $nest_offset, $options, $nested);
    self::put($name . '/' . $id, $new_name . '/update' . $nest_offset . '/$' . (1 + $offset), $options, $nested);
    self::delete($name . '/' . $id, $new_name . '/delete' . $nest_offset . '/$' . (1 + $offset), $options, $nested);
  }

  //--------------------------------------------------------------------
  /**
   * Adds a global pattern that will be used along all the routes
   *
   *      Route::pattern('user_name', '[a-z][A-Z]');
   *
   * @param  string $pattern The pattern to search
   * @param  string $regex The regex to substitute for
   */
  public static function pattern($pattern, $regex)
  {
    self::$pattern_list[$pattern] = $regex;
  }

  //--------------------------------------------------------------------
  /**
   * Return the pattern in the list if exists. If not, returns NULL
   *
   * @param  string $pattern The pattern to search
   */
  public static function get_pattern($pattern)
  {
    if (array_key_exists($pattern, self::$pattern_list)) {
      return self::$pattern_list[$pattern];
    } else {
      return NULL;
    }
  }

  //--------------------------------------------------------------------

  /**
   * Add a prefix to the $from portion of the route. This is handy for
   * grouping items under a similar URL, like:
   *
   *      Route::prefix('admin', function()
   *      {
   *          Route::resources('users');
   *      });
   *
   * @param  string|array $name The prefix to add to the routes.
   * @param  Closure $callback
   */
  public static function prefix($name, Closure $callback)
  {
    if (is_array($name)) {
      if (array_key_exists('subdomain', $name)) {
        $subdomain = $name['subdomain'];
      }

      $name = $name['name'];
    }

    self::_add_prefix($name);

    if (isset($subdomain)) {
      self::subdomain($subdomain, $callback);
    } else {
      call_user_func($callback);
    }

    self::_delete_prefix();
  }

  //--------------------------------------------------------------------

  /**
   * Add a prefix to the $prefix array
   *
   * @param  string $prefix The prefix to add to the routes.
   */

  private static function _add_prefix($prefix)
  {

    self::$prefix[] = $prefix;
  }
  //--------------------------------------------------------------------

  /**
   * Deletes a prefix from the $prefix array
   */

  private static function _delete_prefix()
  {
    array_pop(self::$prefix);
  }
  //--------------------------------------------------------------------

  /**
   * Return the actual prefix of the routes
   */

  public static function get_prefix()
  {
    if (!empty(self::$prefix)) {
      return implode('/', self::$prefix) . '/';
    } else {
      return '';
    }
  }

  //--------------------------------------------------------------------

  /**
   * Returns the $from portion of the route if it has been saved with a name
   * previously.
   *
   * Example:
   *
   *      Route::get('posts', 'posts/show', array('as' => 'posts'));
   *      redirect( Route::named('posts') );
   *
   * @param  [type] $name [description]
   * @return [type]       [description]
   */
  public static function named($name, $parameters = array())
  {
    if (isset(self::$named_routes[$name])) {
      $return_url = self::$named_routes[$name];
    } else {
      return NULL;
    }

    if (!empty($parameters)) {
      foreach ($parameters as $key => $parameter) {
        $return_url = str_replace('$' . ($key + 1), $parameter, $return_url);
      }
    }

    return $return_url;
  }

  //--------------------------------------------------------------------

  /**
   * Sets a name for a route with $from portion of the route
   *
   * Example:
   *
   *      Route::get('posts', 'posts/show', array('as' => 'posts'));
   *      redirect( Route::named('posts') );
   *
   * @param  [type] $name [description]
   * @param  string $route the route itself
   */
  public static function set_name($name, $route)
  {
    self::$named_routes[$name] = $route;
  }

  //--------------------------------------------------------------------

  //--------------------------------------------------------------------
  // Contexts
  //--------------------------------------------------------------------

  /**
   * Contexts provide a way for modules to assign controllers to an area of the
   * site based on the name of the controller. This can be used for making a
   * '/developer' area of the site that all modules can create functionality into.
   *
   * @param  string $name The name of the URL segment
   * @param  string $controller The name of the controller
   * @param  array $options
   *
   * @return void
   */
  public static function context($name, $controller = NULL, $options = array())
  {
    // If $controller is an array, then it's actually the options array,
    // so we'll reorganize parameters.
    if (is_array($controller)) {
      $options = $controller;
      $controller = NULL;
    }

    // If $controller is empty, then we need to rename it to match
    // the $name value.
    if (empty($controller)) {
      $controller = $name;
    }

    $offset = isset($options['offset']) ? (int) $options['offset'] : 0;

    // Some helping hands
    $first = 1 + $offset;
    $second = 2 + $offset;
    $third = 3 + $offset;
    $fourth = 4 + $offset;
    $fifth = 5 + $offset;
    $sixth = 6 + $offset;

    self::any($name . '/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)', "\${$first}/{$controller}/\${$second}/\${$third}/\${$fourth}/\${$fifth}/\${$sixth}");
    self::any($name . '/(:any)/(:any)/(:any)/(:any)/(:any)', "\${$first}/{$controller}/\${$second}/\${$third}/\${$fourth}/\${$fifth}");
    self::any($name . '/(:any)/(:any)/(:any)/(:any)', "\${$first}/{$controller}/\${$second}/\${$third}/\${$fourth}");
    self::any($name . '/(:any)/(:any)/(:any)', "\${$first}/{$controller}/\${$second}/\${$third}");
    self::any($name . '/(:any)/(:any)', "\${$first}/{$controller}/\${$second}");
    self::any($name . '/(:any)', "\${$first}/{$controller}");

    unset($first, $second, $third, $fourth, $fifth, $sixth);

    // Are we creating a home controller?
    if (isset($options['home']) && !empty($options['home'])) {
      self::any($name, "{$options['home']}");
    }
  }

  //--------------------------------------------------------------------

  /**
   * Allows you to easily block access to any number of routes by setting
   * that route to an empty path ('').
   *
   * Example:
   *     Route::block('posts', 'photos/(:num)');
   *
   *     // Same as...
   *     $route['posts']          = '';
   *     $route['photos/(:num)']  = '';
   */
  public static function block()
  {
    $paths = func_get_args();

    if (!is_array($paths)) {
      return;
    }

    foreach ($paths as $path) {
      self::createRoute($path, '');
    }
  }

  //--------------------------------------------------------------------


  //--------------------------------------------------------------------
  // Utility Methods
  //--------------------------------------------------------------------

  /**
   * Resets the class to a first-load state. Mainly useful during testing.
   *
   * @return void
   */
  public static function reset()
  {
    self::$route_objects = array();
    self::$named_routes = array();
    self::$routes = array();
    self::$prefix = NULL;
    self::$pre_route_objects = array();
    self::$pattern_list = array();
    self::$filter_list = array();
  }

  //--------------------------------------------------------------------

  //--------------------------------------------------------------------
  // Create Route Methods
  //--------------------------------------------------------------------

  /**
   * Does the heavy lifting of creating an actual route. You must specify
   * the request method(s) that this route will work for. They can be separated
   * by a pipe character "|" if there is more than one.
   *
   * @param  string $from
   * @param  array $to
   * @param  array $options
   * @param  boolean $nested
   *
   * @return array          The built route.
   */
  static function createRoute($from, $to, $options = array(), $nested = FALSE)
  {
    if (!is_null(self::$active_subdomain)) {
      $options['subdomain'] = self::$active_subdomain;
    }

    if (array_key_exists('subdomain', $options) && self::_CheckSubdomain($options['subdomain']) === FALSE) {
      return FALSE;
    }

    $new_route = new \CodeigniterXtend\Route\Builder($from, $to, $options, $nested);

    self::$pre_route_objects[] = $new_route;

    $new_route->make();

    return new \CodeigniterXtend\Route\Facade($new_route);
  }


  //--------------------------------------------------------------------

  //--------------------------------------------------------------------
  // Subdomain Methods
  //--------------------------------------------------------------------


  static function get_subdomain()
  {

    if (is_null(self::$subdomain)) {
      if (defined('ROUTE_DOMAIN_NAME') === FALSE) {
        define('ROUTE_DOMAIN_NAME', $_SERVER['HTTP_HOST']);
      }

      self::$subdomain = preg_replace('/^(?:([^\.]+)\.)?' . ROUTE_DOMAIN_NAME . '$/', '\1', $_SERVER['HTTP_HOST']);
    }

    return self::$subdomain;
  }

  static function subdomain($subdomain_rules, closure $callback)
  {
    if (self::_CheckSubdomain($subdomain_rules) === TRUE) {
      self::$active_subdomain = $subdomain_rules;
      call_user_func($callback);
    }

    self::$active_subdomain = NULL;
  }


  static private function _CheckSubdomain($subdomain_rules = NULL)
  {
    $subdomain = self::get_subdomain();

    //if the subdomain rules are "FALSE" then if we have a subdomain we won't make the route, because
    //that's the indication of not allowing subdomains in that route
    if ($subdomain != '' and $subdomain_rules == FALSE) {
      return FALSE;
    } elseif ($subdomain == '' and $subdomain_rules == FALSE) {
      return TRUE;
    }

    //if subdomain it's empty, then we will return false, because there is no subdomain in the url
    if ($subdomain == '') {
      return FALSE;
    }

    $i = preg_match('/^\{(.+)\}$/', $subdomain_rules);

    //if the subdomain rules have a named parameter, we will wait till the end of the
    //route generation for it's rule
    if ($i > 0) {
      return TRUE;
    }

    $i = preg_match('/^\(\:any\)/', $subdomain_rules);

    if ($i > 0) {
      return TRUE;
    }

    $i = preg_match('/^\(\:num\)/', $subdomain_rules);

    if ($i > 0) {
      return is_numeric($subdomain);
    }

    //if we arrive here we will count the subdomain_rules as a regex, so se will check it
    $i = preg_match($subdomain_rules, $subdomain);

    if ($i > 0) {
      return TRUE;
    }

    return FALSE;
  }


  //--------------------------------------------------------------------

  //--------------------------------------------------------------------
  // Filter Methods
  //--------------------------------------------------------------------

  /**
   * Adds a new filter into the list
   *
   * @param  string $name
   * @param  Closure $callback
   *
   */
  static function filter($name, $callback)
  {
    self::$filter_list[$name] = $callback;
  }


  //--------------------------------------------------------------------

  static function get_filters($route, $type = 'before')
  {
    if (array_key_exists($route, self::$route_objects)) {
      $filter_list = self::$route_objects[$route]->get_filters($type);

      $callback_list = array();

      foreach ($filter_list as $filter) {
        if (is_callable($filter)) {
          $callback_list[] = array(
            'filter' => $filter,
            'parameters' => NULL
          );
        } else {
          $param = NULL;

          // check if callback has parameters
          if (preg_match('/(.*?)\[(.*)\]/', $filter, $match)) {
            $filter = $match[1];
            $param = $match[2];
          }

          if (array_key_exists($filter, self::$filter_list)) {
            $callback_list[] = array(
              'filter' => self::$filter_list[$filter],
              'parameters' => $param
            );
          }
        }
      }

      return $callback_list;
    }

    return array();
  }
}
