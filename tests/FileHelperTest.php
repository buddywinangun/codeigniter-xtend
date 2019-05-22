<?php

use PHPUnit\Framework\TestCase;
use \Xtend\Helpers\File;

const TMP_DIR = __DIR__ . '/temp';
const INPUT_DIR = __DIR__ . '/input';

final class FileHelperTest extends TestCase
{
  public static function setUpBeforeClass(): void
  {
    // During testing, files in the input directory are overwritten, so reset the input directory before testing.
    File::delete(TMP_DIR);
    File::copyDirectory(INPUT_DIR, TMP_DIR);
  }

  public function testDeleteDirectoriesRecursively(): void
  {
    $deleteSelf = true;
    $dir = TMP_DIR . '/recursively-delete';
    File::delete($dir, $deleteSelf);
    $this->assertSame(file_exists($dir), false);
  }

  public function testDeleteRecursivelyOnlyChildrenOfDirectory(): void
  {
    $deleteSelf = false;
    $dir = TMP_DIR . '/recursively-delete-only-children';
    File::delete($dir, $deleteSelf);
    $numberOfFiles = count(glob($dir . '/*'));
    $this->assertSame($numberOfFiles, 0);
  }

  public function testMakeDirector(): void
  {
    $dir = TMP_DIR . '/path';
    File::makeDirectory($dir);
    $directoryExists = file_exists($dir);
    $this->assertSame($directoryExists, true);
  }

  public function testMakeDirectorAlreadyExists(): void
  {
    $dir = TMP_DIR . '/path';
    File::makeDirectory($dir);
    $directoryExists = file_exists($dir);
    $this->assertSame($directoryExists, true);
  }
}
