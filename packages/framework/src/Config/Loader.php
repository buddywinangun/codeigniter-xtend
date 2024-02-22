<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\Config;

final class Loader
{
  /**
   * Load model.
   */
  public static function model($models)
  {
    if (empty($models))
      return;
    if (is_string($models))
      $models = [$models];
    $CI = &\get_instance();
    foreach ($models as $model)
      $CI->load->model($model);
  }

  /**
   * Load library.
   */
  public static function library($libraries)
  {
    if (empty($libraries))
      return;
    if (is_string($libraries))
      $libraries = [$libraries];
    $CI = &\get_instance();
    foreach ($libraries as $library)
      $CI->load->library($library);
  }

  /**
   * Load databse.
   */
  public static function database($config = 'default', bool $return = false, $queryBuilder = null, bool $overwrite = false)
  {
    // Grab the super object
    $CI =& \get_instance();

    // Do we even need to load the database class?
    if (!$return && $queryBuilder === null && isset($CI->db) && is_object($CI->db) && !empty($CI->db->conn_id) && !$overwrite) {
      return;
    }

    // Load the DB class
    $db = $CI->load->database($config, $return, $queryBuilder);
    if (!$return || $overwrite) {
      // Initialize the db variable. Needed to prevent
      // reference errors with some configurations
      $CI->db = '';
      $CI->db =& $db;
    }
    if ($return) {
      return $db;
    }
  }

  /**
   * Load config.
   */
  public static function config(string $configName, string $configeKey = null)
  {
    static $config;
    if (isset($config[$configName])) {
      if (empty($configeKey))
        return $config[$configName];
      return $config[$configName][$configeKey] ?? '';
    }
    $CI = &\get_instance();
    $CI->config->load($configName, true);
    $config[$configName] = $CI->config->item($configName);
    if (empty($configeKey))
      return $config[$configName];
    return $config[$configName][$configeKey] ?? '';
  }
}
