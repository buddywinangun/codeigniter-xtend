<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\Helpers;

use CodeigniterXtend\Framework\Log\Logger;

final class FileHelper
{

  /**
   *
   * Make directory
   *
   * @throws RuntimeException
   * @param string $dirPath
   * @param int $mode
   * @return void
   */
  public static function makeDirectory(string $dir, int $mode = 0755): bool
  {
    // If the directory already exists, do nothing.
    if (file_exists($dir))
      return false;

    // Create a directory.
    if (@mkdir($dir, $mode, true) === false) {
      // If the directory creation fails, get the reason.
      $error = error_get_last();
      $reason = !empty($error['message']) ? $error['message'] : 'unknown';
      Logger::info("{$dir} directory creation failed, reason is \"{$reason}\"");
      return false;
    }
    return true;
  }

  /**
   *
   * Copy file
   *
   * @throws RuntimeException
   * @param string $srcPath
   * @param string $dstPath
   * @return void
   */
  public static function copyFile(string $srcPath, string $dstPath, $group = null, $user = null)
  {
    if (!file_exists($srcPath))
      throw new \RuntimeException('Not found file ' . $srcPath);
    else if (!is_file($srcPath))
      throw new \RuntimeException($srcPath . ' is not file');
    self::makeDirectory(dirname($dstPath));
    if (copy($srcPath, $dstPath) === false)
      throw new \RuntimeException('Can not copy from ' . $srcPath . ' to ' . $dstPath);
    if (isset($group))
      chgrp($dstPath, $group);
    if (isset($user))
      chown($dstPath, $user);
  }

  /**
   *
   * Copy directory
   *
   * @throws RuntimeException
   * @param string $srcDir
   * @param string $dstDir
   * @return void
   */
  public static function copyDirectory(string $srcDir, string $dstDir)
  {
    if (!file_exists($srcDir))
      throw new \RuntimeException('Not found directory ' . $srcDir);
    else if (!is_dir($srcDir))
      throw new \RuntimeException($srcDir . ' is not directory');
    self::makeDirectory($dstDir);
    $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($srcDir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
    foreach ($it as $info) {
      if ($info->isDir())
        self::makeDirectory($dstDir . '/' . $it->getSubPathName());
      else
        self::copyFile($info, $dstDir . '/' . $it->getSubPathName());
    }
  }

  /**
   *
   * Replace file content
   *
   * @param string $path
   * @return  void
   */
  public static function replace(string $path, array $replace)
  {
    $content = file_get_contents($path);
    $content = str_replace(array_keys($replace), array_values($replace), $content);
    file_put_contents($path, $content, LOCK_EX);
  }

  /**
   * Delete directory or file.
   *
   * ```php
   * use \CodeigniterXtend\Framework\Helpers\FileHelper;
   *
   * // Delete all files and folders in "/ path"..
   * FileHelper::delete('/test');
   *
   * // Delete all files and folders in the "/ path" folder and also in the "/ path" folder.
   * $deleteSelf = true;
   * FileHelper::delete('/test', $deleteSelf);
   *
   * // Lock before deleting, Locks are disabled by default.
   * $deleteSelf = true;
   * $enableLock = true;
   * FileHelper::delete('/test', $deleteSelf, $enableLock);
   * ```
   */
  public static function delete(...$paths)
  {
    if (is_array(reset($paths)))
      $paths = reset($paths);
    $deleteSelf = true;
    $enableLock = false;
    if (count($paths) > 2 && is_bool(end($paths)) && is_bool($paths[count($paths) - 2])) {
      $enableLock = end($paths);
      unset($paths[count($paths) - 1]);
      $deleteSelf = end($paths);
      unset($paths[count($paths) - 1]);
    } else if (count($paths) > 1 && is_bool(end($paths))) {
      $deleteSelf = end($paths);
      unset($paths[count($paths) - 1]);
    }
    foreach ($paths as $path) {
      if (!file_exists($path))
        continue;
      if (is_file($path))
        unlink($path);
      else {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $info) {
          if ($info->isDir())
            rmdir($info);
          else {
            if ($enableLock) {
              // 'w' mode truncates file, you don't want to do that yet!
              $fp = fopen($info->getPathname(), 'c');
              flock($fp, LOCK_EX);
              ftruncate($fp, 0);
              fclose($fp);
              unlink($info->getPathname());
            } else
              unlink($info);
          }
        }
        if ($deleteSelf) {
          // Clear the cache of file statuses.
          clearstatcache();
          rmdir($path);
        }
      }
    }
  }
}
