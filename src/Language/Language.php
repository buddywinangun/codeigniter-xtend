<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * Provides enhanced Language capabilities to CodeIgniter Language.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Xtend\Language;

use Xtend\Helpers\Path;

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

			self::$_languages[$name] = Path::normalizePath($language_path . '/');
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

			self::$_languages[$name] = Path::normalizePath($path . '/');
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

	// ------------------------------------------------------------------------

	/**
	 * Store language in session and change config item.
	 *
	 * @param 	none
	 * @return 	void
	 */
	public static function init()
	{
		global $CFG;

		static $session;

		if (is_null($session) or !isset($_SESSION)) {
			$session = &load_class('Session', 'libraries/Session');
		}

		// Site available language and all languages.
		$languages = self::lists(true);
		$site_languages = array_keys($languages);

		// Current and default language.
		$default = $CFG->item('language');
		$current = isset($_SESSION['language']) ? $_SESSION['language'] : $default;

		/**
		 * In case the language is not stored in session or is not available;
		 * we attempt to detect clients language. If available, we use it
		 * instead of the default language.
		 */
		if (
			!isset($_SESSION['language'])
			or !in_array($_SESSION['language'], $site_languages)
		) {
			$code = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
				? substr(html_escape($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2)
				: 'id';

			foreach ($languages as $folder => $lang) {
				/**
				 * In order for the language to be used, the code must exists and
				 * the language must be available.
				 */
				if (
					isset($lang['code'])
					&& $code === $lang['code']
					&& in_array($folder, $site_languages)
				) {
					$current = $folder;
					break;
				}
			}

			$_SESSION['language'] = $current;
		}

		$CFG->set_item('detail_language', $languages[$current]);
		$CFG->set_item('language', $current);

		unset($languages[$current]);
		$CFG->set_item('site_languages', $languages);
	}

	// -------------------------------------------------------------------------

	/**
	 * Load a fallback language file
	 */
	public static function fallback($langfile, $idiom, $found, $alt_path)
	{
		if ($found !== FALSE) {
			return $found;
		}

		// Load the base file, so any others found can override it
		$basepath = BASEPATH . 'language/' . $idiom . '/' . $langfile;
		if (($found = file_exists($basepath)) === TRUE) {
			include($basepath);
		}

		// Do we have an alternative path to look in?
		if ($alt_path !== '') {
			$alt_path .= 'language/' . $idiom . '/' . $langfile;
			if (file_exists($alt_path)) {
				include($alt_path);
				$found = TRUE;
			}
		} else {
			foreach (get_instance()->load->get_package_paths(TRUE) as $package_path) {
				$package_path .= 'language/' . $idiom . '/' . $langfile;
				if ($basepath !== $package_path && file_exists($package_path)) {
					include($package_path);
					$found = TRUE;
					break;
				}
			}
		}

		return $found;
	}

	// -------------------------------------------------------------------------

	/**
	 * Fetches a single line of text from the language array
	 */
	public static function line($line, $value)
	{
		if ($value !== FALSE) {
			return $value;
		}

		// check whether constant defined or not if not then php script exit and won't process further.
		$message = "<?php defined('BASEPATH') OR exit('No direct script access allowed');\n\n";

		$lang_folder = APPPATH . 'language/' . config_item('language') . '/';
		$filepath = $lang_folder . config_item('language') . '_lang.php';

		if (!file_exists($filepath)) {
			@mkdir($lang_folder, 0777, true);
			write_file($filepath, $message);
		}

		$file_contents = @file_get_contents($filepath);
		$file_pattern = '/<\?php/';
		if (!preg_match($file_pattern, $file_contents)) {
			write_file($filepath, $message);
		}

		$line = strtolower(str_replace(' ', '_', $line));
		$lang = ucfirst(str_replace('_', ' ', $line));

		$lang_contents = @file_get_contents($filepath);
		$lang_pattern = '~\$lang\[(\'|")' . preg_quote($line) . '(\'|")\]~';
		if (!preg_match($lang_pattern, $lang_contents)) {
			$message = '$lang[\'' . addcslashes($line, '\'') . '\'] = "' . addcslashes($lang, '"') . '";' . PHP_EOL;
			file_put_contents($filepath, $message, FILE_APPEND);
		}

		return $lang;
	}
}
