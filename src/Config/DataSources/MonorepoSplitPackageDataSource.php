<?php

declare(strict_types=1);

namespace Xtend\Release\Config\DataSources;

class MonorepoSplitPackageDataSource
{
    public function __construct(protected string $rootDir)
    {
    }

    /**
     * @return string[]
     */
    public function getSkipMonorepoSplitPackagePaths(): array
    {
        return [];
    }
}
