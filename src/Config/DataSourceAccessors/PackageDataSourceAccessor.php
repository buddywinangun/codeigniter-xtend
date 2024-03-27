<?php

declare(strict_types=1);

namespace Xtend\Config\DataSourceAccessors;

use Xtend\Config\DataSources\PackageDataSource;

class PackageDataSourceAccessor
{
  public function __construct(protected PackageDataSource $PackageDataSource)
  {
  }

  /**
   * @return string[]
   */
  public function getPackageMainFiles(): array
  {
    $files = [];
    foreach ($this->PackageDataSource->getPackageConfigEntries() as $PackageConfigEntry) {
      $MainFile = $this->PackageDataSource->getRootDir() . '/' . $PackageConfigEntry['path'] . '/' . $PackageConfigEntry['main_file'];
      if (!file_exists($MainFile)) {
        continue;
      }
      $files[] = $MainFile;
    }
    return $files;
  }
}
