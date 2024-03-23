<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/userguide3/general/controllers.html
 */
class MY_Controller extends CI_Controller
{
	private $__filter_params;

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->call_filters('before');
	}

	// --------------------------------------------------------------------

	public function _remap($method, $parameters = array())
	{
		empty($parameters) ? $this->$method() : call_user_func_array(array($this, $method), $parameters);

		$this->call_filters('after');
	}

	// --------------------------------------------------------------------

	/**
	 * @param string $type
	 */
	private function call_filters($type)
	{
		$this->__filter_params = array($this->uri->uri_string());
		$loaded_route = $this->router->get_active_route();
		$filter_list = \CodeigniterXtend\Route\Route::get_filters($loaded_route, $type);

		foreach ($filter_list as $filter_data) {
			$param_list = $this->__filter_params;

			$callback = $filter_data['filter'];
			$params = $filter_data['parameters'];

			// check if callback has parameters
			if (!is_null($params)) {
				// separate the multiple parameters in case there are defined
				$params = explode(':', $params);

				// search for uris defined as parameters, they will be marked as {(.*)}
				foreach ($params as &$p) {
					if (preg_match('/\{(.*)\}/', $p, $match_p)) {
						$p = $this->uri->segment($match_p[1]);
					}
				}

				$param_list = array_merge($param_list, $params);
			}

			if (class_exists('Closure') and method_exists('Closure', 'bind')) {
				$callback = Closure::bind($callback, $this);
			}

			call_user_func_array($callback, $param_list);
		}
	}
}
