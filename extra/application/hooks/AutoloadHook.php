<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Autoload Hooks
 *
 * @category 	Hooks
 */
class AutoloadHook
{
	function init()
	{
		spl_autoload_register(function ($classname) {
			/* CI core classes */
			if (strstr($classname, 'CI_')) {
				$location = BASEPATH . 'core/' . substr($classname, 3) . '.php';
				if (!is_file($location)) {
					show_error('Failed to load CI core class: ' . $classname);
				}

				include_once $location;
				return;
			}

			$prefixes = config_item('package_locations');
			$prefixes['App'] = APPPATH;

			foreach ($prefixes as $prefix => $replacement) {
				if (strpos(strtolower($classname), "{$prefix}\\") !== 0) continue;

				// Locate class relative path
				$classname = str_replace("{$prefix}\\", '', $classname);
				$filepath = $replacement . str_replace('\\', DIRECTORY_SEPARATOR, ltrim($classname, '\\')) . '.php';

				if (!file_exists($filepath)) continue;

				include_once $filepath;
			}
		});
	}
}
