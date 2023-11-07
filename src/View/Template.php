<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Xtend\View;

use Xtend\Helpers\FileHelper;
use Xtend\Helpers\PathHelper;
use Xtend\Config\Loader;

final class Template
{
  private $engine = null;

  /**
   * Holds the current active theme.
   * @var string
   */
  public static $current_theme;

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

  /**
   * Holds an array of theme locations.
   * @var array.
   */
  protected static $_locations;

	// -------------------------------------------------------------------------

	public static function init()
	{
		if (FALSE === self::path()) return;

		$GLOBALS['BM']->mark('theme_initialize_start');

		// Load the current theme's functions.php file.
		if (TRUE != ($function = self::path('functions.php'))) {
			log_message('error', 'Unable to locate the theme\'s "functions.php" file: ' . self::current());
			show_error(sprintf('theme_missing_functions %s', self::current()));
		}

		require_once($function);

		// load language
		self::language();

		$GLOBALS['BM']->mark('theme_initialize_end');
	}

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

  // ----------------------------------------------------------------------------

  /**
   * Allows themes to be translatable by loading their language files.
   * @access 	public
   * @param 	string 	$path 	The path to the theme's folder.
   * @param 	string 	$index 	Unique identifier to retrieve language lines.
   * @return 	void
   */
  public static function language($path = null, $index = null)
  {
    /**
     * Checks whether translations were already loaded or not.
     * @var boolean
     */
    static $loaded;

    if (true === $loaded) {
      return;
    }

    if (empty($path)) {
      $path = self::path('language');
    }

    if (true !== is_dir($path)) {
      return;
    }

    // Prepare our array of language lines.
    $full_lang = array();

    // We make sure the check the english version.
    $english_file = $path . '/english.php';
    $language_file = $path . '/' . config_item('language') . '.php';

    if (file_exists($english_file)) {
      require_once($english_file);

      if (isset($lang)) {
        $full_lang = array_replace_recursive($full_lang, $lang);
        unset($lang);
      }
    } else if (file_exists($language_file)) {
      require_once($language_file);

      if (isset($lang)) {
        $full_lang = array_replace_recursive($full_lang, $lang);
        unset($lang);
      }
    }

    $full_lang = array_clean($full_lang);

    if (!empty($full_lang)) {
      $textdomain = apply_filters('theme_translation_index', $index);
      empty($textdomain) && $textdomain = self::current();

      get_instance()->lang->language[$textdomain] = $full_lang;
    }

    $loaded = true;
  }

  // -----------------------------------------------------------------------------

  /**
   * Returns the currently active theme depending on the site area.
   * @access 	public
   * @param 	none
   * @return 	string
   */
  public static function current()
  {
    if (!isset(self::$current_theme)) {
      self::$current_theme = apply_filters('public_theme', config_item('theme'));
      self::$current_theme or self::$current_theme = 'default';
    }

    return self::$current_theme;
  }

  // ----------------------------------------------------------------------------

  /**
   * Returns the full path to the currently active theme, whether it's the
   * front-end theme or dashboard theme.
   * @access 	public
   * @param 	string 	$uri
   * @return 	mixed 	String if valid, else false.
   */
  public static function path($uri = '', $theme = null)
  {
    static $path;

    $theme or $theme = self::current();
    $path = false;

    foreach (self::locations() as $location) {
      if (is_dir($location . $theme)) {
        $path = $location . $theme;
        break;
      }
    }

    if (false === $path) {
      return false;
    }

    if (!empty($uri)) {
      if (!file_exists($path . '/' . $uri)) {
        return false;
      }
      $return = PathHelper::normalizePath($path . '/' . $uri);
    } else {
      $return = PathHelper::normalizePath($path . '/');
    }

    return $return;
  }

  // ------------------------------------------------------------------------

  /**
   * Returns the URL to the currently active theme, whether it's the front-end
   * theme or the dashboard theme.
   * @access 	public
   * @param 	string 	$uri
   * @param 	string 	$protocol
   * @return 	string
   */
  public static function url($uri = '', $protocol = null, $theme = null)
  {
    static $_protocol, $cached_uris;

    $theme or $theme = self::current();

    if ($_protocol !== $protocol) {
      $_protocol = $protocol;
    }

    // $return = path_join(base_url('assets', $_protocol), $theme);
    $return = base_url('assets', $_protocol);

    if (empty($uri)) {
      return $return;
    }

    $path = 'theme/' . $theme . DIRECTORY_SEPARATOR;
    $uris = rtrim(str_replace('assets/', '', $uri), '/');

    if (file_exists(path_join(FCPATH, 'assets/' . $path . $uris))) {
      $cached_uris[$uri] = path_join($return, $path . $uris);
    } else {
      $return = base_url('loader', $_protocol);
      $cached_uris[$uri] = path_join($return, $path . $uri);
    }

    $return = $cached_uris[$uri];
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

  // ------------------------------------------------------------------------

  /**
   * Load template engine.
   */
  public function load(string $path, array $vars = [], array $option = [], string $extension = 'html'): string
  {
    $cache = Loader::config('config', 'cache_templates');
    if (!empty($cache)) {
      FileHelper::makeDirectory($cache);
    }

    $option = array_merge([
      'paths' => [\VIEWPATH . self::current() . '/template'],
      'environment' => [
        'cache' => !empty($cache) ? $cache : false,
        'debug' => \ENVIRONMENT !== 'production',
        'autoescape' => 'html',
      ],
      'lexer' => [
        'tag_comment' => ['{#', '#}'],
        'tag_block' => ['{%', '%}'],
        'tag_variable' => ['{{', '}}'],
        'interpolation' => ['#{', '}'],
      ],
    ], $option);

    $this->engine = new \Twig\Environment(new \Twig\Loader\FileHelpersystemLoader($option['paths']), $option['environment']);
    $this->engine->addFunction(new \Twig\TwigFunction(
      'cache_busting',
      /**
       * This function generates a new file path with the last date of filechange to support better better client caching via Expires header:
       * e.g. <link rel="stylesheet" href="{{cache_busting('css/style.css')}}">
       *       css/style.css -> css/style.css?1428423235
       */
      function (string $filePath) {
        if (!file_exists(FCPATH . $filePath))
          return \base_url($filePath);
        $modified = filemtime($_SERVER['DOCUMENT_ROOT'] . '/' . $filePath);
        if (!$modified)
          $modified = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        return \base_url($filePath) . '?' . $modified;
      }
    ));
    $baseUrl = \base_url();
    $this->engine->addGlobal('baseUrl', $baseUrl);
    $this->engine->addGlobal('session', $_SESSION ?? null);
    $CI = &get_instance();
    $this->engine->addGlobal('action', ($CI->router->directory ?? '') . $CI->router->class . '/' . $CI->router->method);
    $this->engine->setLexer(new \Twig\Lexer($this->engine, $option['lexer']));

    return $this->engine->render($path . '.' . $extension, $vars);
  }

  // ----------------------------------------------------------------------------

  /**
   * Returns and array of theme locations.
   * @access 	public
   * @return 	array.
   */
  public static function locations()
  {
    isset(self::$_locations) or self::_prep_locations();
    return self::$_locations;
  }

  // ----------------------------------------------------------------------------

  /**
   * _prep_locations
   *
   * Method for formatting paths to themes directories.
   */
  protected static function _prep_locations()
  {
    if (isset(self::$_locations)) {
      return;
    }

		if (file_exists(APPPATH.'config/locate.php'))
		{
			include(APPPATH.'config/locate.php');
		}

		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/locate.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/locate.php');
		}

    self::$_locations = $locate['template'];

    if (null === self::$_locations) {
      self::$_locations = array(VIEWPATH);
    } elseif (!in_array(VIEWPATH, self::$_locations)) {
      self::$_locations[] = VIEWPATH;
    }

    foreach (self::$_locations as $i => &$location) {
      if (false !== ($path = realpath($location))) {
        $location = rtrim(str_replace('\\', '/', $path), '/') . '/';
        continue;
      }

      unset(self::$_locations[$i]);
    }
  }
}
