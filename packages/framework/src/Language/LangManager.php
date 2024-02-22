<?php

/**
 * This file is part of Codeigniter Manager.
 *
 * Provides enhanced Language capabilities to CodeIgniter Language.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Manager\Language;

use CodeigniterXtend\Framework\Helpers\PathHelper;

final class Language
{
	/**
	 * Array of all packages and their details.
	 * @var 	array
	 */
	protected static $_details = array();

	/**
	 * Array of all available languages.
	 * @var array
	 */
	public static $_languages;

	// ------------------------------------------------------------------------

	/**
	 * Returns an array of languages.
	 *
	 * @return 	array
	 */
	public static function lists($details = false)
	{
		$_load = &load_class('Loader', 'core');
		$_load->helper(['directory', 'path']);

		$location = APPPATH . 'language/';
		$languages = directory_map($location, 1);
		foreach ($languages as $name) {
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

			$language_path = $location . $name . '/';
			$init_file   = $language_path . $name . '_lang.php';

			// Make sure a valid module file by the same name as the folder exists
			if (!file_exists($init_file)) {
				continue;
			}

			self::$_languages[$name] = PathHelper::normalizePath($language_path . '/');
		}

		$return = self::$_languages;

		if (true === $details) {
			$_details = array();

			foreach (self::$_languages as $folder => $path) {
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

	// ------------------------------------------------------------------------

	/**
	 * Returns the real path to the selected lang.
	 *
	 * @access 	public
	 * @param 	string 	$name 	lang name.
	 * @return 	the full path if found, else FALSE.
	 */
	public static function path($name = null)
	{
		if (empty($name)) {
			return false;
		}

		if (!isset(self::$_languages[$name])) {
			$path = false;

			foreach (self::locations() as $location) {
				if (is_dir($location . $name)) {
					$path = $location . $name;
					break;
				}
			}

			if (false === $path) {
				return false;
			}

			self::$_languages[$name] = PathHelper::normalizePath($path . '/');
		}

		return self::$_languages[$name];
	}

	// ------------------------------------------------------------------------

	/**
	 * Reads details about the plugin from the manifest.json file.
	 */
	public static function details($lang = null)
	{
		if (isset(self::$_details[$lang])) {
			return self::$_details[$lang];
		}

		// header
		$path = self::path($lang);

		$lang_source = $path . $lang . '_lang.php';
		$lang_data = @file_get_contents($lang_source); // Read the module init file.

		preg_match('|Name:(.*)$|mi', $lang_data, $name);
		preg_match('|name_en:(.*)$|mi', $lang_data, $name_en);
		preg_match('|locale:(.*)$|mi', $lang_data, $locale);
		preg_match('|currency:(.*)$|mi', $lang_data, $currency);
		preg_match('|currency_key:(.*)$|mi', $lang_data, $currency_key);
		preg_match('|direction:(.*)$|mi', $lang_data, $direction);
		preg_match('|code:(.*)$|mi', $lang_data, $code);
		preg_match('|flag:(.*)$|mi', $lang_data, $flag);

		$detail['name'] = (isset($name[1])) ? trim($name[1]) : '';
		$detail['name_en'] = (isset($name_en[1])) ? trim($name_en[1]) : '';
		$detail['locale'] = (isset($locale[1])) ? trim($locale[1]) : '';
		$detail['currency'] = (isset($currency[1])) ? trim($currency[1]) : '';
		$detail['currency_key'] = (isset($currency_key[1])) ? trim($currency_key[1]) : '';
		$detail['direction'] = (isset($direction[1])) ? trim($direction[1]) : '';
		$detail['code'] = (isset($code[1])) ? trim($code[1]) : '';
		$detail['flag'] = (isset($flag[1])) ? trim($flag[1]) : '';

		// Cache everything before returning.
		self::$_details[$lang] = $detail;
		return $detail;
	}
}
