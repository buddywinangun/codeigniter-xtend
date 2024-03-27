<?php

declare(strict_types=1);

namespace Xtend\ReleaseWorker;

use Xtend\Config\DataSourceAccessors\PackageDataSourceAccessor;
use Xtend\Config\DataSources\PackageDataSource;
use Xtend\Extensions\SmartFile\FileContentReplacerSystem;
use Xtend\Extensions\Utils\VersionUtils;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

abstract class AbstractConvertStableTagVersionInPackageReleaseWorker implements ReleaseWorkerInterface
{
  protected string $monorepoMetadataFile;
  protected $packages;

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
