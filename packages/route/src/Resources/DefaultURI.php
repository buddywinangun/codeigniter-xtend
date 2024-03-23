<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * URI Class
 *
 * Parses URIs and determines routing
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	URI
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/userguide3/libraries/uri.html
 */
class MY_URI extends CI_URI
{
	private $uri_parameters = array();

	public function load_uri_parameters($uri)
	{
		$this->uri_parameters = \CodeigniterXtend\Route\Route::get_parameters($uri);
	}

	/**
	 * Fetch URI Segment
	 *
	 * @see		CI_URI::$segments
	 * @param	int		$n		Index
	 * @param	mixed		$no_result	What to return if the segment index is not found
	 * @return	mixed
	 */
	public function segment($n, $no_result = NULL)
	{
		if (!is_numeric($n)) {
			if (array_key_exists($n, $this->uri_parameters)) {
				$n = $this->uri_parameters[$n];
			} else {
				return $no_result;
			}
		}

		return isset($this->segments[$n]) ? $this->segments[$n] : $no_result;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch URI "routed" Segment
	 *
	 * Returns the re-routed URI segment (assuming routing rules are used)
	 * based on the index provided. If there is no routing, will return
	 * the same result as CI_URI::segment().
	 *
	 * @see		CI_URI::$rsegments
	 * @see		CI_URI::segment()
	 * @param	int		$n		Index
	 * @param	mixed		$no_result	What to return if the segment index is not found
	 * @return	mixed
	 */
	public function rsegment($n, $no_result = NULL)
	{
		if (!is_numeric($n)) {
			if (array_key_exists($n, $this->uri_parameters)) {
				$n = $this->uri_parameters[$n];
			} else {
				return $no_result;
			}
		}

		return isset($this->rsegments[$n]) ? $this->rsegments[$n] : $no_result;
	}
}
