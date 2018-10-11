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

		do_action('pre_controller_constructor');
	}

	// --------------------------------------------------------------------

	/**
	 * View Loader
	 *
	 * Loads "view" files.
	 *
	 * @param	string	$view	View name
	 * @param	array	$vars	An associative array of data
	 *				to be extracted for use in the view
	 * @param	bool	$return	Whether to return the view output
	 *				or leave it to the Output class
	 * @return	object|string
	 */
	public function view($view = '', $vars = array(), $return = FALSE)
	{
		do_action('loader_view');

		parent::view($view, $vars, $return);
	}
}
