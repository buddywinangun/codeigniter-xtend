<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\Helpers;

final class ArrayHelper
{
  /**
   * Searches and returns the value of the specified key from the array
   * ```php
   * use \CodeigniterXtend\Framework\Helpers\ArrayHelper;
   *
   * // Search from a simple array
   * $arr = [
   *   'Indonesia' => 'Jakarta',
   *   'Sumut' => 'Medan'
   * ];
   *
   * ArrayHelper::searchArrayByKey('Indonesia', $arr);
   * // Jakarta
   *
   * // Search from nested array
   * $nested = [
   *   'cities' => [
   *     'Indonesia' => 'Jakarta',
   *     'Sumut' => 'Medan'
   *   ]
   * ];
   * ```
   *
   * ArrayHelper::searchArrayByKey('Sumut', $nested);
   * // Medan
   * @param  string $needle
   * @param  array $haystack
   * @return mixed
   */
  public static function searchArrayByKey(string $needle, array $arr)
  {
    foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($arr)) as $key => $value) {
      if ($needle === $key)
        return $value;
    }
    return null;
  }
}
