<?php

namespace Xtend\Core;

/**
 * Router Class
 *
 * Adapted from the CodeIgniter Core Classes
 * @link		https://codeigniter.com/userguide3/general/routing.html
 *
 * Description:
 * This library extends the CI_Router class.
 * and adds features allowing use of events.
 */

abstract class Router extends \CI_Router
{
   /**
    * Current package name
    *
    * @var	string
    */
   public static $package;

   // -----------------------------------------------------------------------

   /**
    * Default package (and method if specific)
    *
    * @var	string
    */
   public $default_package;

   // -----------------------------------------------------------------------

   /**
    * Domain package (and method if specific)
    *
    * @var	string
    */
   public $domain_package;

   // --------------------------------------------------------------------

   /**
    * Class constructor
    *
    * Runs the route mapping function.
    *
    * @param	array	$routing
    * @return	void
    */
   public function __construct($routing = NULL)
   {
      is_array($routing) && isset($routing['package']) && $this->default_package = $routing['package'];

      parent::__construct($routing);

      $domain = explode('.', $_SERVER['HTTP_HOST'], 2);
      $this->domain_package = $domain[0];
   }

   // --------------------------------------------------------------------

   /**
    * Set default controller
    *
    * @return	void
    */
   protected function _set_default_controller()
   {
      if (empty($this->default_controller)) {
         show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
      }

      // Is the method being specified?
      if (sscanf($this->default_controller, '%[^/]/%s', $class, $method) !== 2) {
         $method = 'index';
      }

      $segments = array($class, $method);

      //creates the various parts
      if (\Xtend\Util\Package::has($this->domain_package)) {
         array_unshift($segments, $this->domain_package);
      } elseif (\Xtend\Util\Package::has($this->default_package)) {
         array_unshift($segments, $this->default_package);
      }

      $located = $this->_locate($segments);
      if ($located) {
         // Found in application? Set it to found.
         $this->default_controller = implode('/', $located);
      }

      parent::_set_default_controller();
   }

   // --------------------------------------------------------------------

   /**
    * Validate request
    *
    * Attempts validate the URI request and determine the controller path.
    *
    * @used-by	CI_Router::_set_request()
    * @param	array	$segments	URI segments
    * @return	mixed	URI segments
    */
   protected function _validate_request($segments)
   {
      // If we have no segments, return as-is.
      if (count($segments) == 0) {
         return $segments;
      }

      // Let's detect package's parts first.
      if ($located = $this->_locate($segments)) {
         // If found, return the result.
         return $located;
      };

      //creates the various parts
      if (\Xtend\Util\Package::has($this->domain_package)) {
         array_unshift($segments, $this->domain_package);

         // Again, look for the controller.
         if ($located = $this->_locate($segments)) {
            return $located;
         };

         unset($segments[0]);
      }

      // is default_controller
      if (\Xtend\Util\Package::has($this->default_controller)) {
         array_unshift($segments, $this->default_controller);

         // Again, look for the controller.
         if ($located = $this->_locate($segments)) {
            return $located;
         };

         unset($segments[0]);
      }

      // Did the user specify a 404 override?
      if (!empty($this->routes['404_override'])) {
         $segments = explode('/', $this->routes['404_override']);

         // Again, look for the controller.
         if ($located = $this->_locate($segments)) {
            return $located;
         };
      }

      // Let the parent handle the rest!
      return parent::_validate_request($segments);
   }

   // --------------------------------------------------------------------

   /**
    * Parse Routes
    *
    * Matches any routes that may exist in the config/routes.php file
    * against the URI to determine if the class/method need to be remapped.
    *
    * @return	void
    */
   protected function _parse_routes()
   {
      // Turn the segment array into a URI string
      $uri = implode('/', $this->uri->segments);

      // Package routes.
      foreach (\Xtend\Util\Package::lists() as $folder => $path) {
         if (TRUE !== \Xtend\Util\Package::is_enabled($folder)) {
            continue;
         }

         // "config/routes.php" not found? Nothing to do.
         if (!is_file($path . 'config/routes.php')) {
            continue;
         }

         // Include their config/routes.php file if found.
         require_once($path . "config/routes.php");
      }

      // Validate & get reserved routes
      if (isset($route) && is_array($route)) {
         $this->routes = \Xtend\Util\Route::map($route);
      }

      if (NULL != ($named = \Xtend\Util\Route::named($uri))) {
         $uri = $named;
      }

      // Get HTTP verb
      $http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

      // Loop through the route array looking for wildcards
      foreach ($this->routes as $key => $val) {
         // Check if route format is using HTTP verbs
         if (is_array($val)) {
            $val = array_change_key_case($val, CASE_LOWER);
            if (isset($val[$http_verb])) {
               $val = $val[$http_verb];
            } else {
               continue;
            }
         }

         // Convert wildcards to RegEx
         $key = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);

         // Does the RegEx match?
         if (preg_match('#^' . $key . '$#', $uri, $matches)) {
            // Are we using callbacks to process back-references?
            if (!is_string($val) && is_callable($val)) {
               // Remove the original string from the matches array.
               array_shift($matches);

               // Execute the callback using the values in matches as its parameters.
               $val = call_user_func_array($val, $matches);
            }
            // Are we using the default routing method for back-references?
            elseif (strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE) {
               $val = preg_replace('#^' . $key . '$#', $val, $uri);
            }

            $this->_set_request(explode('/', $val));
            return;
         }
      }

      // If we got this far it means we didn't encounter a
      // matching route so we'll set the site default route
      $this->_set_request(array_values($this->uri->segments));
   }

   // --------------------------------------------------------------------

   /**
    * Locate Routes
    *
    * @return	void
    */
   protected function _locate($segments)
   {
      /* get the segments array elements */
      list($package, $directory, $controller) = array_pad($segments, 3, null);

      if ($this->translate_uri_dashes === TRUE) {
         $package = str_replace('-', '_', $package);
         $directory = str_replace('-', '_', $directory);
         $controller = str_replace('-', '_', $controller);
      }

      // Flag to see if we are in a package.
      $is_package = false;

      if (\Xtend\Util\Package::has($package)) {
         $is_package = true;
         $location   = \Xtend\Util\Package::$_packages[$package];
      }
      // Because of revered routes ;)
      elseif (\Xtend\Util\Package::has($directory)) {
         $is_package = true;
         $location   = \Xtend\Util\Package::$_packages[$directory];
         $_package   = $package;
         $package    = $directory;
         $directory  = $_package;
      }

      /* package exists? */
      if (true !== $is_package or true !== is_dir($source = $location . 'controllers/')) {
         return false;
      }

      $relative    = rtrim(str_replace($package . '/', '', $location), '/');
      $start       = rtrim(realpath(APPPATH), '/');
      $parts       = explode('/', str_replace('\\', '/', $start));
      $parts_count = count($parts);

      for ($i = 1; $i <= $parts_count; $i++) {
         $relative = str_replace(
            implode('/', $parts) . '/',
            str_repeat('../', $i),
            $relative,
            $count
         );

         array_pop($parts);

         if ($count) {
            break;
         }
      }

      self::$package = $package;
      $this->directory = "{$relative}/{$package}/controllers/";

      if ($directory) {
         if (is_dir($source . $directory . '/')) {
            // Different controller's name?
            if ($controller && is_file($source . $directory . '/' . ucfirst($controller) . '.php')) {
               $this->directory .= $directory . '/';
               return array_slice($segments, 2);
            }

            // Module sub-directory with default controller?
            if (is_file($source . $directory . '/' . ucfirst($this->default_controller) . '.php')) {
               $this->directory .= $directory . '/';
               $segments[1] = $this->default_controller;
               return array_slice($segments, 1);
            }
         }

         /* package controller exists? */
         if (is_file($source . ucfirst($directory) . '.php')) {
            $this->class = $directory;
            $segments[0] = $package;
            $segments[1] = $directory;
            return array_slice($segments, 1);
         }
      }

      // package controller exists?
      if (is_file($source . ucfirst($package) . '.php')) {
         return $segments;
      }

      // package with default controller?
      if (is_file($source . ucfirst($this->default_controller) . '.php')) {
         $segments[0] = $this->default_controller;
         return $segments;
      }

      return FALSE;
   }

   // --------------------------------------------------------------------

   /**
    * Set package name
    *
    * @param	string	$package	Class name
    * @return	void
    */
   public function set_package($package)
   {
      self::$package = str_replace(array('/', '.'), '', $package);
   }

   // --------------------------------------------------------------------

   /**
    * Fetch the current package
    *
    * @return	string
    */
   public function fetch_package()
   {
      return self::$package;
   }
}
