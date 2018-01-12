<?php

namespace Xtend\Util;

use Xtend\Util\Logger;

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
  public static function makeDirectory(string $dir, int $mode = 0755): bool {
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
  public static function copyFile(string $srcPath, string $dstPath, $group = null, $user = null) {
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
  public static function copyDirectory(string $srcDir, string $dstDir) {
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
  public static function replace(string $path, array $replace) {
    $content = file_get_contents($path);
    $content = str_replace(array_keys($replace), array_values($replace), $content);
    file_put_contents($path, $content, LOCK_EX);
  }
}
