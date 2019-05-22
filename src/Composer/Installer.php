<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Xtend\Composer;

use Composer\Script\Event;
use Xtend\Helpers\File;

final class Installer
{
  /**
   * @var string DOCUMENT_ROOT
   */
  const DOCUMENT_ROOT = 'public/';

  /**
   * @var string FRAMEWORK_DIR
   */
  const FRAMEWORK_DIR = 'vendor/codeigniter/framework/';

  /**
   * Composer run script
   *
   * @param Event $event
   */
  public static function run(Event $event)
  {
    $cwd = getcwd();
    $io = $event->getIO();

    $io->write('Preparing the application file.');
    File::copyDirectory(static::FRAMEWORK_DIR . 'application', 'application');
    self::pathExtra();

    $io->write('Create an entry point.');
    File::copyFile(static::FRAMEWORK_DIR . 'index.php', static::DOCUMENT_ROOT . 'index.php');
    File::copyFile(static::FRAMEWORK_DIR . '.gitignore', '.gitignore');
    File::replace(static::DOCUMENT_ROOT . 'index.php', [
      '$system_path = \'system\';' => '$system_path = \'../' . static::FRAMEWORK_DIR . 'system\';',
      '$application_folder = \'application\';' => '$application_folder = \'../application\';',
    ]);

    $io->write('Create a config.');
    File::replace('application/config/autoload.php', [
      '$autoload[\'libraries\'] = array();' => '$autoload[\'libraries\'] = array(\'session\',\'form_validation\');',
      '$autoload[\'helper\'] = array();' => '$autoload[\'helper\'] = array(\'url\');'
    ]);
    File::replace('application/config/config.php', [
      '$config[\'base_url\'] = \'\';' => 'if (!empty($_SERVER[\'HTTP_HOST\'])) {$config[\'base_url\'] = "//".$_SERVER[\'HTTP_HOST\'] . str_replace(basename($_SERVER[\'SCRIPT_NAME\']),"",$_SERVER[\'SCRIPT_NAME\']);}',
      '$config[\'index_page\'] = \'index.php\';' => '$config[\'index_page\'] = \'\';',
      '$config[\'enable_hooks\'] = FALSE;' => '$config[\'enable_hooks\'] = TRUE;',
      '$config[\'subclass_prefix\'] = \'MY_\';' => '$config[\'subclass_prefix\'] = \'App\';',
      '$config[\'composer_autoload\'] = FALSE;' => '$config[\'composer_autoload\'] = realpath(\APPPATH . \'../vendor/autoload.php\');',
      '$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-\';' => '$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-,\';',
      '$config[\'log_threshold\'] = 0;' => '$config[\'log_threshold\'] = 2;',
      '$config[\'cookie_httponly\']  = FALSE;' => '$config[\'cookie_httponly\']  = TRUE;',
    ]);

    $io->write('Updating composer.');
    File::copyFile('composer.json.dist', 'composer.json');
    passthru('composer update');

    chdir($cwd);
    $io->write('Deleting unnecessary files.');
    File::delete(
      $cwd . '/src',
      $cwd . '/.github',
      $cwd . '/composer.json.dist',
      $cwd . '/CHANGELOG.md',
      $cwd . '/README.md',
      $cwd . '/LICENSE.md'
    );

    $io->write('Installation is complete.');
    $io->write('See <https://packagist.org/packages/buddywinangun/codeigniter-xtend> for details.');
  }

  /**
   * Composer update script
   *
   * @param Event $event
   */
  public static function update(Event $event)
  {
    $io = $event->getIO();

    self::pathExtra('vendor/buddywinangun/codeigniter-xtend/');

    $io->write('Update is complete.');
    $io->write('See <https://packagist.org/packages/buddywinangun/codeigniter-xtend> for details.');
  }

  /**
   * @param Event $event
   */
  public static function pathExtra($path = '')
  {
    File::copyDirectory($path . 'extra/application', 'application');
    File::copyDirectory($path . 'extra/public', static::DOCUMENT_ROOT);
  }
}
