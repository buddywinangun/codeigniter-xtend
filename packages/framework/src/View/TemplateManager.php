<?php

/**
 * This file is part of Codeigniter Manager.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Manager\View;

use CodeigniterXtend\Framework\Helpers\PathHelper;

final class Template
{
  /**
   * Array of all available packages.
   * @var array
   */
  public static $_templates;

  /**
   * Array of all packages and their details.
   * @var 	array
   */
  protected static $_details = array();

  // ----------------------------------------------------------------------------

  public static function lists($details = false)
  {
    if (is_null(self::$_templates)) {
      self::$_templates = array();

      // Let's go through folders and check if there are any.
      foreach (self::locations() as $location) {
        $themes = directory_map($location, 1);

        if (is_null($themes)) {
          continue;
        }

        foreach ($themes as $name) {
          $name = strtolower(trim($name));

          /**
           * Filename may be returned like chat/ or chat\ from the directory_map function
           */
          foreach (['\\', '/'] as $trim) {
            $name = rtrim($name, $trim);
          }

          // If the module hasn't already been added and isn't a file
          if (stripos($name, '.')) {
            continue;
          }

          $module_path = $location . $name . '/';

          self::$_templates[$name] = PathHelper::normalizePath($module_path . '/');
        }

        // Alphabetically order packages.
        ksort(self::$_templates);
      }
    }

    $return = self::$_templates;

    if (true === $details) {
      $_details = array();

      foreach (self::$_templates as $folder => $path) {
        if (isset(self::$_details[$folder])) {
          $_details[$folder] = self::$_details[$folder];
        } elseif (false !== ($details = self::details($folder, $path))) {
          $_details[$folder] = $details;
        }
      }

      empty($_details) or $return = $_details;
    }

    return $return;
  }

  // -----------------------------------------------------------------------------

  /**
   * Returns details about the given theme.
   * @access 	public
   * @param 	string 	$folder 	The theme's folder name.
   * @return 	mixed 	Array of details if valid, else false.
   */
  public static function details($folder = null)
  {
    $folder or $folder = self::current();

    if (!$folder) {
      return false;
    }

    if (isset(self::$_details[$folder])) {
      return self::$_details[$folder];
    }

    // header
    $detail['header'] = self::header($folder);

    if (empty($detail['screenshot'])) {
      $detail['screenshot'] = get_upload_url('blank.png');
      foreach (array('.png', '.jpg', '.jpeg', '.gif') as $ext) {
        if (false !== self::path('screenshot' . $ext, $folder)) {
          $detail['screenshot'] = get_theme_url('screenshot' . $ext, null,  $folder);
          break;
        }
      }
    }

    // Add extra stuff.
    $detail['folder'] = $folder;

    // Is the theme enabled?
    $detail['enabled'] = ($folder === get_option('theme', 'default'));

    // Send default language folder and index.
    empty($detail['textdomain']) && $detail['textdomain'] = $folder;
    empty($detail['domainpath']) && $detail['domainpath'] = 'language';

    // Cache it first.
    return self::$_details[$folder] = $detail;
  }

  // ----------------------------------------------------------------------------

  public static function header($folder, $path = null)
  {
    $path = $path ? $path : self::path('', $folder);

    $module_source = $path . 'style.css';
    $module_data = @file_get_contents($module_source); // Read the module init file.

    preg_match('|Name:(.*)$|mi', $module_data, $name);
    preg_match('|Description:(.*)$|mi', $module_data, $description);
    preg_match('|Version:(.*)|i', $module_data, $version);
    preg_match('|License:(.*)$|mi', $module_data, $license_name);
    preg_match('|License URI:(.*)$|mi', $module_data, $license_uri);
    preg_match('|Author:(.*)$|mi', $module_data, $author_name);
    preg_match('|Author URI:(.*)$|mi', $module_data, $author_uri);
    preg_match('|Author Email:(.*)$|mi', $module_data, $author_email);
    preg_match('|Tags:(.*)$|mi', $module_data, $tags);

    $headers = [];
    $headers['name'] = (isset($name[1])) ? trim($name[1]) : '';
    $headers['description'] = (isset($description[1])) ? trim($description[1]) : '';
    $headers['version'] = (isset($version[1])) ? trim($version[1]) : 0;
    $headers['license'] = (isset($license_name[1])) ? trim($license_name[1]) : '';
    $headers['author'] = (isset($author_name[1])) ? trim($author_name[1]) : '';
    $headers['author_uri'] = (isset($author_uri[1])) ? trim($author_uri[1]) : '';
    $headers['author_email'] = (isset($author_email[1])) ? trim($author_email[1]) : '';
    $headers['tags'] = (isset($tags[1])) ? trim($tags[1]) : '';
    $headers['full_path'] = $path;

    if (isset($license_uri[1])) {
      $headers['license_uri'] = trim($license_uri[1]);
      if (false !== stripos($headers['license'], 'mit') && empty($headers['license_uri'])) {
        $headers['license_uri'] = 'http://opensource.org/licenses/MIT';
      }
    }

    return $headers;
  }
}
