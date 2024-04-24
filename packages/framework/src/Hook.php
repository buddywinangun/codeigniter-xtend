<?php

/**
 * This file is part of Codeigniter Xtend Framework, for CodeIgniter 3.
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework;

use CodeigniterXtend\Framework\Package\PackageManager;
use CodeigniterXtend\Framework\View\Template;

class Hook
{
	public static function getHooks()
	{
		$hooks = [];

		$hooks['pre_system'][] = function () {
			self::preSystemHook();
		};

		$hooks['post_controller_constructor'][] = function () {
			global $params;
			self::postControllerConstructorHook($params);
		};

		return $hooks;
	}

	/**
	 * "pre_system" hook
	 *
	 * @return void
	 */
	private static function preSystemHook()
	{
		$autoloader = new CodeigniterXtend\Framework\Autoloader\Autoloader();
		$autoloader->register();
	}

  /**
   * "post_controller" hook
   *
   * @param  array $params
   *
   * @return void
   */
  private static function postControllerConstructorHook(&$params)
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
			get_instance()->load->add_package_path($path);

			// Include their  file if found.
			require_once($path . $folder . ".php");

			// We always fire this action.
			do_action('package_loaded_' . $folder);
		}

    global $BM;
    $BM->mark('theme_initialize_start');

    // Load the current theme's functions.php file.
    if (TRUE != ($function = Template::path('functions.php'))) {
      log_message('error', 'Unable to locate the theme\'s "functions.php" file: ' . self::current());
      show_error(sprintf('theme_missing_functions %s', Template::current()));
    }

    require_once($function);

    // load language
    Template::language();

    $BM->mark('theme_initialize_end');
  }
}
