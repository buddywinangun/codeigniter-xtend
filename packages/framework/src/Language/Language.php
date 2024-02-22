<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * Provides enhanced Language capabilities to CodeIgniter Language.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\Language;

final class Language
{
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
