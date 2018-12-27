<?php

namespace Xtend\Core;

/**
 * Loader Class
 *
 * Adapted from the CodeIgniter Core Classes
 * @link		https://codeigniter.com/userguide3/libraries/loader.html
 *
 * Description:
 * This library extends the CI_Loader class
 * and adds features allowing use of events.
 */
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
