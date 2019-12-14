<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Xtend;

use Composer\Script\Event;
use Xtend\Helpers\FileHelper;

final class ComposerScripts
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
   * @var string XTEND_DIR
   */
  const XTEND_DIR = 'vendor/buddywinangun/codeigniter-xtend/';

  /**
   * Composer run script
   *
   * @param Event $event
   */
  public static function run(Event $event)
  {
    $io = $event->getIO();

    self::pathExtra($io);

    $io->write('Updating composer.');
    FileHelper::copyFile('composer.json.dist', 'composer.json');
    passthru('composer update');

    $cwd = getcwd();
    chdir($cwd);
    $io->write('Deleting unnecessary files.');
    FileHelper::delete(
      $cwd . '/.github',
      $cwd . '/extra',
      $cwd . '/src',
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

    self::pathExtra($io, self::XTEND_DIR);

    $io->write('Update is complete.');
    $io->write('See <https://packagist.org/packages/buddywinangun/codeigniter-xtend> for details.');
  }

  /**
   * @param Event $event
   */
  public static function pathExtra($io, $path = '')
  {
    $io->write('Preparing the application file.');
    FileHelper::copyDirectory(static::FRAMEWORK_DIR . 'application', 'application');
    FileHelper::copyDirectory($path . 'extra/application', 'application');
    FileHelper::copyDirectory($path . 'extra/public', static::DOCUMENT_ROOT);

    $io->write('Create an entry point.');
    FileHelper::copyFile(static::FRAMEWORK_DIR . 'index.php', static::DOCUMENT_ROOT . 'index.php');
    FileHelper::copyFile(static::FRAMEWORK_DIR . '.gitignore', '.gitignore');
    FileHelper::replace(static::DOCUMENT_ROOT . 'index.php', [
      '$system_path = \'system\';' => '$system_path = \'../' . static::FRAMEWORK_DIR . 'system\';',
      '$application_folder = \'application\';' => '$application_folder = \'../application\';',
    ]);

    $io->write('Create a config.');
    FileHelper::replace('application/config/autoload.php', [
      '$autoload[\'libraries\'] = array();' => '$autoload[\'libraries\'] = array(\'session\',\'form_validation\');',
      '$autoload[\'helper\'] = array();' => '$autoload[\'helper\'] = array(\'url\', \'array\');'
    ]);
    FileHelper::replace('application/config/config.php', [
      '$config[\'base_url\'] = \'\';' => '$config[\'base_url\'] = ((isset($_SERVER[\'HTTPS\']) && $_SERVER[\'HTTPS\'] == \'on\') ? \'https\' : \'http\').\'://\'.$_SERVER[\'HTTP_HOST\'].str_replace(basename($_SERVER[\'SCRIPT_NAME\']),"",$_SERVER[\'SCRIPT_NAME\']);',
      '$config[\'index_page\'] = \'index.php\';' => '$config[\'index_page\'] = \'\';',
      '$config[\'enable_hooks\'] = FALSE;' => '$config[\'enable_hooks\'] = TRUE;',
      '$config[\'subclass_prefix\'] = \'MY_\';' => '$config[\'subclass_prefix\'] = \'App\';',
      '$config[\'composer_autoload\'] = FALSE;' => '$config[\'composer_autoload\'] = realpath(\APPPATH . \'../vendor/autoload.php\');',
      '$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-\';' => '$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-,\';',
      '$config[\'log_threshold\'] = 0;' => '$config[\'log_threshold\'] = 2;',
      '$config[\'cookie_httponly\']  = FALSE;' => '$config[\'cookie_httponly\']  = TRUE;',
    ]);
  }
}
