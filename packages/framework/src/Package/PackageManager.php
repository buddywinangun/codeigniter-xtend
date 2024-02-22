<?php

/**
 * This file is part of Codeigniter Manager.
 *
 * Provides enhanced Package capabilities to CodeIgniter Package.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\Package;

use CodeigniterXtend\Framework\Helpers\PathHelper;
use CodeigniterXtend\Framework\Package\Package;

final class PackageManager
{
	/**
	 * Array of all available packages.
	 * @var array
	 */
	public static $_packages;

	/**
	 * Array of all packages and their details.
	 * @var 	array
	 */
	protected static $_details = array();

	/**
	 * Holds the array of active packages.
	 * @var array
	 */
	protected static $_actives = array();

	// ------------------------------------------------------------------------

	/**
	 * Returns a list of available packages.
	 */
	public static function lists($details = false)
	{
		$_load = &load_class('Loader', 'core');
		$_load->helper(['directory', 'path']);

		// Not cached? Cache them first.
		if (empty(self::$_packages)) {
			self::$_packages = array();

			// Let's go through folders and check if there are any.
			foreach (Package::locations() as $location) {
				$modules = directory_map($location, 1);

				if (is_null($modules)) {
					continue;
				}

				foreach ($modules as $name) {
					$name = strtolower(trim($name));

					/**
					 * Filename may be returned like chat/ or chat\ from the directory_map function
					 */
					foreach (['\\', '/'] as $trim) {
						$name = rtrim($name, $trim);
					}

					// If the module hasn't already been added and isn't a file
					if (stripos($name, '.')) {
						continue;
					}

					$module_path = $location . $name . '/';
					$init_file   = $module_path . $name . '.php';

					// Make sure a valid module file by the same name as the folder exists
					if (!file_exists($init_file)) {
						continue;
					}

					self::$_packages[$name] = PathHelper::normalizePath($module_path . '/');
				}
			}
		}

		$return = self::$_packages;

		if (true === $details) {
			$_details = array();

			foreach (self::$_packages as $folder => $path) {
				if (isset(self::$_details[$folder])) {
					$_details[$folder] = self::$_details[$folder];
				} elseif (false !== ($details = self::details($folder, $path))) {
					$_details[$folder] = $details;
				}
			}

			empty($_details) or $return = $_details;
		}

		return $return;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get the list of active packages.
	 */
	public static function actives($details = false)
	{
		// Not cached? Cache them first.
		if (empty(self::$_actives)) {
			$packages = array();

			/**
			 * Because we are automatically assigning options from database
			 * to config array, we see if we have the item
			 */
			if (!empty(config_item('active_packages'))) {
				$packages = config_item('active_packages');
			}

			// We make sure it's an array before finally caching it.
			is_array($packages) or $packages = array();
			self::$_actives = $packages;
		}

		$return = self::$_actives;

		if (true === $details) {
			$_details = array();

			foreach (self::$_actives as $key => $folder) {
				if (isset(self::$_details[$folder])) {
					$_details[$folder] = self::$_details[$folder];
				} elseif (false !== ($details = self::details($folder))) {
					$_details[$folder] = $details;
				}
			}

			empty($_details) or $return = $_details;
		}

		return $return;
	}

	// ------------------------------------------------------------------------

	/**
	 * Returns TRUE if the selected package is valid.
	 * @access 	private
	 * @param 	string 	$name
	 * @return 	boolean
	 */
	public static function has($name = null)
	{
		$array = self::$_packages;
		$array = (is_array($array)) ? $array : array();
		return array_key_exists($name, $array);
	}

	// ------------------------------------------------------------------------

	/**
	 * Returns the real path to the selected package.
	 *
	 * @access 	public
	 * @param 	string 	$name 	package name.
	 * @return 	the full path if found, else FALSE.
	 */
	public static function path($name = null)
	{
		if (empty($name)) {
			return false;
		}

		if (!isset(self::$_packages[$name])) {
			$path = false;

			foreach (self::locations() as $location) {
				if (is_dir($location . $name)) {
					$path = $location . $name;
					break;
				}
			}

			if (false === $path) {
				return false;
			}

			self::$_packages[$name] = PathHelper::normalizePath($path . '/');
		}

		return self::$_packages[$name];
	}

	// ----------------------------------------------------------------------------

	/**
	 * Returns the URL to the currently active package, whether it's the front-end
	 * package or the dashboard package.
	 * @access 	public
	 * @param 	string 	$uri
	 * @param 	string 	$protocol
	 * @return 	string
	 */
	public static function url($uri = '', $protocol = null, $package = null)
	{
		static $_protocol, $cached_uris;

		$package or $package = get_instance()->router->fetch_package();

		if ($_protocol !== $protocol) {
			$_protocol = $protocol;
		}

		// $return = PathHelper::pathJoin(base_url('assets', $_protocol), $package);
		$return = base_url('assets', $_protocol);

		if (empty($uri)) {
			return $return;
		}

		$path = 'package/' . $package . DIRECTORY_SEPARATOR;
		$uris = rtrim(str_replace('assets/', '', $uri), '/');

		if (file_exists(PathHelper::pathJoin(FCPATH, 'assets/' . $path . $uris))) {
			$cached_uris[$uri] = PathHelper::pathJoin($return, $path . $uris);
		} else {
			$return = base_url('loader', $_protocol);
			$cached_uris[$uri] = PathHelper::pathJoin($return, $path . $uri);
		}

		$return = $cached_uris[$uri];
		return $return;
	}

	// ------------------------------------------------------------------------

	/**
	 * Reads details about the plugin from the manifest.json file.
	 */
	public static function details($folder = null, $path = null)
	{
		if (empty($folder)) {
			$folder = get_instance()->router->fetch_package();

			if (empty($folder)) {
				return false;
			}
		}

		if (isset(self::$_details[$folder])) {
			return self::$_details[$folder];
		}

		// header
		$detail['header'] = self::header($folder, $path);

		// Is package enabled?
		$detail['enabled'] = self::is_enabled($folder);

		// Installed version
		$detail['installed_version'] = false;

		// Add all internal details.
		$detail['contexts'] = self::contexts($folder, $detail['header']['full_path']);
		foreach ($detail['contexts'] as $key => $val) {
			$detail['has_' . $key] = (false !== $val);
		}

		/**
		 * If the package comes without a "help" controller, we see if
		 * the developer provided a package URI so we can use it as
		 * a URL later.
		 */
		if (
			$detail['contexts']
			&& false === $detail['has_help']
			&& !empty($detail['header']['uri'])
		) {
			$detail['contexts']['help'] = $detail['header']['uri'];
			$detail['has_help'] = true;
		}

		empty($detail['my_menu']) && $detail['my_menu'] = $folder;
		$detail['folder'] = $folder;

		// Cache everything before returning.
		self::$_details[$folder] = $detail;
		return $detail;
	}

	// ----------------------------------------------------------------------------

	public static function header($folder, $path = null)
	{
		$path = $path ? $path : self::path($folder);

		$module_source = $path . $folder . '.php';
		$module_data = @file_get_contents($module_source); // Read the module init file.

		preg_match('|Name:(.*)$|mi', $module_data, $name);
		preg_match('|Package URI:(.*)$|mi', $module_data, $uri);
		preg_match('|Version:(.*)|i', $module_data, $version);
		preg_match('|Description:(.*)$|mi', $module_data, $description);
		preg_match('|License:(.*)$|mi', $module_data, $license_name);
		preg_match('|License URI:(.*)$|mi', $module_data, $license_uri);
		preg_match('|Author:(.*)$|mi', $module_data, $author_name);
		preg_match('|Author URI:(.*)$|mi', $module_data, $author_uri);
		preg_match('|Enabled:(.*)$|mi', $module_data, $enabled);
		preg_match('|Requires at least:(.*)$|mi', $module_data, $requires_at_least);
		preg_match('|Tags:(.*)$|mi', $module_data, $tags);

		$headers = [];
		$headers['name'] = (isset($name[1])) ? trim($name[1]) : '';
		$headers['uri'] = (isset($uri[1])) ? trim($uri[1]) : '';
		$headers['version'] = (isset($version[1])) ? trim($version[1]) : 0;
		$headers['enabled'] = (isset($enabled[1])) ? TRUE : FALSE;
		$headers['description'] = (isset($description[1])) ? trim($description[1]) : '';
		$headers['license'] = (isset($license_name[1])) ? trim($license_name[1]) : '';
		$headers['author'] = (isset($author_name[1])) ? trim($author_name[1]) : '';
		$headers['author_uri'] = (isset($author_uri[1])) ? trim($author_uri[1]) : '';
		$headers['requires_at_least'] = (isset($requires_at_least[1])) ? trim($requires_at_least[1]) : '';
		$headers['tags'] = (isset($tags[1])) ? trim($tags[1]) : '';
		$headers['full_path'] = $path;

		if (isset($license_uri[1])) {
			$headers['license_uri'] = trim($license_uri[1]);
			if (false !== stripos($headers['license'], 'mit') && empty($headers['license_uri'])) {
				$headers['license_uri'] = 'http://opensource.org/licenses/MIT';
			}
		}

		return $headers;
	}

	// -----------------------------------------------------------------------------

	/**
	 * Method for list all package's available contexts.
	 */
	public static function contexts($package, $path = null)
	{
		// Nothing provided? Nothing to do...
		if (empty($package)) {
			return false;
		}

		// We start with an empty array.
		$contexts = array();

		// Make sure the package directory path if found.
		(null === $path) && $path = self::path($package);
		if (false === $path) {
			return $contexts;
		}

		// Loop through contexts and see if we find a controller.
		$back_contexts = apply_filters('back_contexts', []);
		foreach ($back_contexts as $context) {
			$contexts[$context] = is_file($path . '/controllers/' . ucfirst($context) . '.php');
		}

		// Return the final result.
		return $contexts;
	}

	// ------------------------------------------------------------------------

	/**
	 * Returns TRUE if the selected plugin is enabled.
	 */
	public static function is_enabled($name)
	{
		$active = self::actives();
		$header = self::header($name);
		if (
			true !== in_array($name, $active)
			&& true !== $header['enabled']
		) {
			return false;
		}
		return true;
	}
}
