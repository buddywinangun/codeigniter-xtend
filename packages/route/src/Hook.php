<?php

/**
 * This file is part of Codeigniter Xtend Route.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Route;

use CodeigniterXtend\Route\Route;

/**
 * Defines and returns all the required hooks at framework startup
 */
class Hook
{
  /**
   * Gets the hooks
   *
   * @return array
   */
  public static function getHooks()
  {
    $hooks = [];

    $hooks['pre_system'][] = function () {
      self::preSystemHook();
    };

    return $hooks;
  }

  /**
   * "pre_system" hook
   *
   * @return void
   */
  private static function preSystemHook()
  {
    if (!file_exists(APPPATH . '/routes')) {
      mkdir(APPPATH . '/routes');
    }

    require_once(__DIR__ . '/Functions.php');
  }
}
