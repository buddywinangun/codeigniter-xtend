<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * Provides enhanced Package capabilities to CodeIgniter Package.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\Package;

use CodeigniterXtend\Framework\Helpers\PathHelper;
use CodeigniterXtend\Framework\Package\PackageManager;

final class Package
{
	/**
	 * Holds an array of package locations.
	 * @var array.
	 */
	protected static $_locations;

	// -------------------------------------------------------------------------

	public static function init()
	{
		// Lopp through all packages.
		foreach (PackageManager::lists() as $folder => $path) {

			// package enabled but folder missing? Nothing to do.
			if (TRUE !== PackageManager::is_enabled($folder)) {
				continue;
			}

			// ".php" not found? Nothing to do.
			if (!is_file($path . $folder . '.php')) {
				continue;
			}

			// added package path.
			$ci = &get_instance();
			$ci->load->add_package_path($path);

			// Include their  file if found.
			require_once($path . $folder . ".php");

			// We always fire this action.
			do_action('package_loaded_' . $folder);
		}
	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if the page belongs to a given package. If no argument is passed,
	 * it checks if we areusing a package.
	 * You may pass a single string, multiple comma- separated packages or an array.
	 */
	public static function is_package($name = null)
	{
		$package = get_instance()->router->fetch_package();

		return ($package == $name) ? true : false;
	}

	// ----------------------------------------------------------------------------

	/**
	 * Returns and array of package locations.
	 * @access 	public
	 * @return 	array.
	 */
	public static function locations()
	{
		isset(self::$_locations) or self::_prep_locations();
		return self::$_locations;
	}

	// ----------------------------------------------------------------------------

	/**
	 * _prep_locations
	 *
	 * Method for formatting paths to packages directories.
	 */
	protected static function _prep_locations()
	{
		if (isset(self::$_locations)) {
			return;
		}

		if (file_exists(APPPATH . 'config/locate.php')) {
			include(APPPATH . 'config/locate.php');
		}

		if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/locate.php')) {
			include(APPPATH . 'config/' . ENVIRONMENT . '/locate.php');
		}

		self::$_locations = $locate['packages'];

		if (null === self::$_locations) {
			self::$_locations = array(APPPATH . 'packages');
		} elseif (!in_array(APPPATH . 'packages', self::$_locations)) {
			array_unshift(self::$_locations, APPPATH . 'packages');
		}

		foreach (self::$_locations as $i => &$location) {
			if (false !== ($path = realpath($location))) {
				$location = rtrim(str_replace('\\', '/', $path), '/') . '/';
				continue;
			}

			unset(self::$_locations[$i]);
		}
	}
}
