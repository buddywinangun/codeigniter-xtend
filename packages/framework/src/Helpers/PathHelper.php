<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\Helpers;

final class PathHelper
{

	/**
	 * Normalizes a filesystem path.
	 *
	 * @param 	string 	$path 	Path to normalize.
	 * @return 	string 	Normalized path.
	 */
	public static function normalizePath($path)
	{
		$path = str_replace('\\', '/', $path);
		$path = preg_replace('|(?<=.)/+|', '/', $path);

		// Upper-case driver letters on windows systems.
		if (':' === substr($path, 1, 1) && !ctype_upper($path[0])) {
			$path = ucfirst($path);
		}

		return $path;
	}

	// ------------------------------------------------------------------------

	/**
	 * Joins two filesystem paths together.
	 *
	 * @param 	string 	$base 	The base path.
	 * @param 	string 	$path 	The relative path to $base.
	 * @return 	string 	The path with base or absolute path.
	 */
	public static function pathJoin($base, $path)
	{
		// If the provided $path is not an absolute path, we prepare it.
		if (!path_is_absolute($path)) {
			$base = rtrim(str_replace('\\', '/', $base), '/') . '/';
			$path = ltrim(str_replace('\\', '/', $path), '/');
			$path = $base . $path;
		}

		return $path;
	}
}
