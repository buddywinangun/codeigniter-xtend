<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * Adapted from the CodeIgniter Core Classes
 * @link https://codeigniter.com/userguide3/libraries/config.html
 *
 * Description:
 * This library extends the CI_Config class
 * and adds features allowing use of events.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\Config;

abstract class Config extends \CI_Config
{
	/**
	 * Class constructor
	 *
	 * Sets the $config data from the primary config.php file as a class variable.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// ----------------------------------------------------------------------------

	/**
	 * Build URI string
	 *
	 * @used-by	CI_Config::site_url()
	 * @used-by	CI_Config::base_url()
	 *
	 * @param	string|string[]	$uri	URI string or an array of segments
	 * @return	string
	 */
	protected function _uri_string($uri)
	{
		if (is_array($uri)) {
			return parent::_uri_string($uri);
		}

		$urx = '';
		if (($offset = strpos($uri, '?')) !== FALSE) {
			$urx = substr($uri, $offset);
			$uri = substr($uri, 0, $offset);
		}

		if (NULL != ($named = \CodeigniterXtend\Framework\Router\Route::named($uri))) {
			$uri = $named;
		}

		$uri = $uri . $urx;

		return parent::_uri_string($uri);
	}
}
