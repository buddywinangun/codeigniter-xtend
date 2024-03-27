<?php

declare(strict_types=1);

namespace Xtend\ReleaseWorker;

use Xtend\DataSourceAccessors\PackageDataSourceAccessor;
use Xtend\Extensions\SmartFile\FileContentReplacerSystem;
use Xtend\Extensions\Utils\VersionUtils;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

abstract class AbstractConvertVersionInMonorepoMetadataFileReleaseWorker implements ReleaseWorkerInterface
{
  protected string $monorepoMetadataFile;

  public function __construct(
    protected FileContentReplacerSystem $fileContentReplacerSystem,
    protected VersionUtils $versionUtils,
  ) {
  }

  protected function getPackages(): array
  {
    if ($this->packages === null) {
      $PackageDataSource = $this->getPackageDataSource();
      $PackageDataSourceAccessor = new PackageDataSourceAccessor($PackageDataSource);
      $this->packages = $PackageDataSourceAccessor->getPackageMainFiles();
    }
    return $this->packages;
  }

  protected function getPackageDataSource(): PackageDataSource
  {
    return new PackageDataSource(dirname(__DIR__, 2));
  }
}
