<?php

/**
 * Router Class
 *
 * Adapted from the CodeIgniter Core Classes
 * @link https://codeigniter.com/userguide3/general/routing.html
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\Router;

use CodeigniterXtend\Framework\Package\PackageManager;
use CodeigniterXtend\Framework\Router\Route;

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
   }

	// --------------------------------------------------------------------

	/**
	 * Set route mapping
	 *
	 * Determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * @return	void
	 */
	protected function _set_routing()
	{
		// Load the routes.php file. It would be great if we could
		// skip this for enable_query_strings = TRUE, but then
		// default_controller would be empty ...
		if (file_exists(APPPATH.'config/routes.php'))
		{
			include(APPPATH.'config/routes.php');
		}

		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/routes.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/routes.php');
		}

      // Package routes.
      foreach (PackageManager::lists(true) as $folder => $path) {
         if (TRUE !== PackageManager::is_enabled($folder)) {
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
		if (isset($route) && is_array($route))
		{
			isset($route['default_controller']) && $this->default_controller = $route['default_controller'];
			isset($route['translate_uri_dashes']) && $this->translate_uri_dashes = $route['translate_uri_dashes'];
			unset($route['default_controller'], $route['translate_uri_dashes']);
			$this->routes = \CodeigniterXtend\Framework\Router\Route::map($route);
		}

		// Are query strings enabled in the config file? Normally CI doesn't utilize query strings
		// since URI segments are more search-engine friendly, but they can optionally be used.
		// If this feature is enabled, we will gather the directory/class/method a little differently
		if ($this->enable_query_strings)
		{
			// If the directory is set at this time, it means an override exists, so skip the checks
			if ( ! isset($this->directory))
			{
				$_d = $this->config->item('directory_trigger');
				$_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';

				if ($_d !== '')
				{
					$this->uri->filter_uri($_d);
					$this->set_directory($_d);
				}
			}

			$_c = trim($this->config->item('controller_trigger'));
			if ( ! empty($_GET[$_c]))
			{
				$this->uri->filter_uri($_GET[$_c]);
				$this->set_class($_GET[$_c]);

				$_f = trim($this->config->item('function_trigger'));
				if ( ! empty($_GET[$_f]))
				{
					$this->uri->filter_uri($_GET[$_f]);
					$this->set_method($_GET[$_f]);
				}

				$this->uri->rsegments = array(
					1 => $this->class,
					2 => $this->method
				);
			}
			else
			{
				$this->_set_default_controller();
			}

			// Routing rules don't apply to query strings and we don't need to detect
			// directories, so we're done here
			return;
		}

		// Is there anything to parse?
		if ($this->uri->uri_string !== '')
		{
			$this->_parse_routes();
		}
		else
		{
			$this->_set_default_controller();
		}
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
      if (PackageManager::has(Route::get_subdomain())) {
         array_unshift($segments, Route::get_subdomain());
      } elseif (PackageManager::has($this->default_package)) {
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
      if (PackageManager::has(Route::get_subdomain())) {
         array_unshift($segments, Route::get_subdomain());

         // Again, look for the controller.
         if ($located = $this->_locate($segments)) {
            return $located;
         };

         unset($segments[0]);
      }

      // is default_controller
      if (PackageManager::has($this->default_controller)) {
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
         $controller = $controller ? str_replace('-', '_', $controller) : '';
      }

      // Flag to see if we are in a package.
      $is_package = false;

      if (PackageManager::has($package)) {
         $is_package = true;
         $location   = PackageManager::$_packages[$package];
      }
      // Because of revered routes ;)
      elseif (PackageManager::has($directory)) {
         $is_package = true;
         $location   = PackageManager::$_packages[$directory];
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
            if (is_file($source . $directory . '/' . ucfirst($package) . '.php')) {
               $this->directory .= $directory . '/';
               $segments[1] = $package;
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
