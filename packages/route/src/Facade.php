<?php

/**
 * This file is part of Codeigniter Xtend Route.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Route;

use CodeigniterXtend\Route\Builder;

class Facade
{
	private $loaded_object;

	function __construct(Builder &$object)
	{
		$this->loaded_object = &$object;
	}

	public function where($parameter, $pattern = NULL)
	{
		$this->loaded_object->where($parameter, $pattern);

		return $this;
	}
}