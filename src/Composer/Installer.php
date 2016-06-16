<?php

namespace Xtend\Composer;

use Composer\Script\Event;
use Composer\IO\ConsoleIO;
use Xtend\Util\FileHelper;

/**
 * Part of CodeIgniter Composer Installer
 *
 * @author     Buddy Winangun
 * @license    MIT License
 * @copyright  2016 Buddy Winangun
 */

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
   * Composer post install script
   *
   * @param Event $event
   */
  public static function post_install(Event $event)
  {
    $io = $event->getIO();

    // Create application
    FileHelper::copyDirectory(static::FRAMEWORK_DIR . 'application', 'application');

    // Create session directory
    FileHelper::makeDirecoty('application/session');
    touch('application/session/.gitkeep');

    // Create index.php
    FileHelper::copyFile(static::FRAMEWORK_DIR . 'index.php', static::DOCUMENT_ROOT . 'index.php');

    // Create .htaccess
    FileHelper::copyFile('htaccess.dist', static::DOCUMENT_ROOT . '.htaccess');

    // Update index.php
    self::update_index($io);

    // Update config.php
    self::update_config($io);

    // Show complete message
    self::show_message($io);

    // Delete unneeded files
    self::delete_self();
  }

  /**
   * Update index.php
   *
   * @param ConsoleIO $io
   * @return void
   */
  private static function update_index(ConsoleIO $io)
  {
    $io->write('==================================================');
    $io->write('<info>Update public/index.php is running');
    FileHelper::replace(static::DOCUMENT_ROOT . 'index.php', [
      '$system_path = \'system\';' => '$system_path = \'../' . static::FRAMEWORK_DIR . 'system\';',
      '$application_folder = \'application\';' => '$application_folder = \'../application\';',
    ]);
    $io->write('<info>Update public/index.php succeeded');
    $io->write('==================================================');
  }

  /**
   * Update application/config/config.php
   *
   * @param ConsoleIO $io
   * @return void
   */
  private static function update_config(ConsoleIO $io)
  {
    $io->write('==================================================');
    $io->write('<info>Update application/config/config.php is running');
    FileHelper::replace('application/config/config.php', [
      // Base Site URL
      '$config[\'base_url\'] = \'\';' => 'if (!empty($_SERVER[\'HTTP_HOST\'])) {$config[\'base_url\'] = "//".$_SERVER[\'HTTP_HOST\'] . str_replace(basename($_SERVER[\'SCRIPT_NAME\']),"",$_SERVER[\'SCRIPT_NAME\']);}',
      // Enable/Disable System Hooks
      '$config[\'enable_hooks\'] = FALSE;' => '$config[\'enable_hooks\'] = TRUE;',
      // Allowed URL Characters
      '$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-\';' => '$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-,\';',
      // Session Variables
      '$config[\'sess_save_path\'] = NULL;' => '$config[\'sess_save_path\'] = \APPPATH . \'session\';',
      // Cookie Related Variables
      '$config[\'cookie_httponly\']  = FALSE;' => '$config[\'cookie_httponly\']  = TRUE;',
      // Composer auto-loading
      '$config[\'composer_autoload\'] = FALSE;' => '$config[\'composer_autoload\'] = realpath(\APPPATH . \'../vendor/autoload.php\');',
      // Index File
      '$config[\'index_page\'] = \'index.php\';' => '$config[\'index_page\'] = \'\';',
      // Class Extension Prefix
      '$config[\'subclass_prefix\'] = \'MY_\';' => '$config[\'subclass_prefix\'] = \'App\';',
    ]);
    FileHelper::replace('application/config/autoload.php', [
      // Auto-load Helper Files
      '$autoload[\'helper\'] = array();' => '$autoload[\'helper\'] = array(\'url\');',
    ]);
    $io->write('<info>Update application/config/config.php succeeded');
    $io->write('==================================================');
  }

  /**
   * Show message
   *
   * @param ConsoleIO $io
   * @return void
   */
  private static function show_message(ConsoleIO $io)
  {
    $io->write('==================================================');
    $io->write('<info>`public/.htaccess` was installed. If you don\'t need it, please remove it.</info>');
    $io->write('See <https://packagist.org/packages/buddywinangun/codeigniter-xtend> for details');
    $io->write('==================================================');
  }
}
