<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Package Hook
 *
 * @category 	Hooks
 */
class AppPackageHook
{
	public function __construct()
	{
		// Get CI instance
		$this->ci = &get_instance();
	}

	// -------------------------------------------------------------------------

	function init()
	{
		// Lopp through all packages.
		foreach (\Xtend\Util\Package::lists() as $folder => $path) {

			// package enabled but folder missing? Nothing to do.
			if (TRUE !== \Xtend\Util\Package::is_enabled($folder)) {
				continue;
			}

			// "main.php" not found? Nothing to do.
			if (!is_file($path . 'main.php')) {
				continue;
			}

			// Include their main file if found.
			require_once($path . "main.php");

			// added package path.
			$this->ci->load->add_package_path($path);

			// We always fire this action.
			do_action('package_loaded_' . $folder);
		}
	}
}
