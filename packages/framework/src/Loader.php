<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * Adapted from the CodeIgniter Core Classes
 * @link https://codeigniter.com/userguide3/libraries/loader.html
 *
 * Description:
 * This library extends the CI_Loader class
 * and adds features allowing use of events.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework;

class Loader extends \CI_Loader
{
	/**
	 * Class constructor
	 *
	 * Sets the $loader data from the primary loader.php file as a class variable.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------

	/**
	 * Initializer
	 *
	 * @todo	Figure out a way to move this to the constructor
	 *		without breaking *package_path*() methods.
	 * @uses	CI_Loader::_ci_autoloader()
	 * @used-by	CI_Controller::__construct()
	 * @return	void
	 */
	public function initialize()
	{
		parent::initialize();

		$GLOBALS['EXT']->call_hook('pre_controller_constructor');
	}
}
