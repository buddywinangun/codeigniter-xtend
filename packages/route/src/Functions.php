<?php

/**
 * This file is part of Codeigniter Xtend Route.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * Gets a route URL by its name
 *
 * @param string  $name    Route name
 * @param array   $params  Route parameters
 *
 * @return string
 */
function route($name = null, $params = [])
{
  return Route::named($name, $params);
}
