<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Router Class
 *
 * Parses URIs and determines routing
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/userguide3/general/routing.html
 */
class MY_Router extends CI_Router
{
	private $active_route;

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
		$load =& load_class('Loader', 'core');
		foreach ($load->get_package_paths() as $location)
		{
			// Load the routes.php file.
			if (!is_dir($location . 'routes')) {
				continue;
			}

			$file_list = scandir($location . 'routes');
			foreach ($file_list as $file) {
				if (is_file($location . 'routes/' . $file) and pathinfo($file, PATHINFO_EXTENSION) == 'php') {
					include($location . 'routes/' . $file);
				}
			}
		}

		parent::_set_routing();
	}

	// --------------------------------------------------------------------

	/**
	 * Set default controller
	 *
	 * @return	void
	 */
	protected function _set_default_controller()
	{
		$this->active_route = 'default_controller';

		parent::_set_default_controller();
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
		foreach ($this->routes as $key => $val) {
			//we have to keep the original key because we will have to use it
			//to recover the route again
			$this->active_route = $key;
			$this->uri->load_uri_parameters($key);
		}

		parent::_parse_routes();
	}

	// --------------------------------------------------------------------

	public function get_active_route()
	{
		return $this->active_route;
	}
}
