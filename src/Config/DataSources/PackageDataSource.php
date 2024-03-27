<?php

declare(strict_types=1);

namespace Xtend\Config\DataSources;

use Xtend\Monorepo\MonorepoMetadata;

class PackageDataSource
{
  public function __construct(protected string $rootDir)
  {
  }

  public function getRootDir(): string
  {
    return $this->rootDir;
  }

  /**
   * @return array<array<mixed>>
   */
  public function getPackageConfigEntries(): array
  {
    $pluginConfigEntries = [
      [
        'path' => 'packages/framework/src',
        'plugin_slug' => 'framework',
        'main_file' => 'Application.php'
      ]
    ];

    foreach ($pluginConfigEntries as &$pluginConfigEntry) {
      $pluginConfigEntry['version'] = MonorepoMetadata::VERSION;
      $pluginConfigEntry['dist_repo_branch'] = MonorepoMetadata::GIT_BASE_BRANCH;
    }

    return $pluginConfigEntries;
  }
}
