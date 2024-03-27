<?php

declare(strict_types=1);

namespace Xtend\Config\DataSources;

use Xtend\ReleaseWorker\GuardOnDefaultBranchReleaseWorker;
use Xtend\ReleaseWorker\ConvertStableTagVersionForProdInPackageReleaseWorker;
use Xtend\ReleaseWorker\ConvertVersionForProdInMonorepoMetadataFileReleaseWorker;
use Xtend\ReleaseWorker\BumpVersionForDevInMonorepoMetadataFileReleaseWorker;
use Xtend\ReleaseWorker\UpdateCurrentBranchAliasReleaseWorker;
use Xtend\ReleaseWorker\UpdateChangelogViaPhpReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateReplaceReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushNextDevReleaseWorker;

class ReleaseWorkersDataSource
{
    /**
     * @return string[]
     */
    public function getReleaseWorkerClasses(): array
    {
        return [
            GuardOnDefaultBranchReleaseWorker::class,

            // Remove "-dev" from the version
            ConvertStableTagVersionForProdInPackageReleaseWorker::class,
            ConvertVersionForProdInMonorepoMetadataFileReleaseWorker::class,

            /**
             * When doing a major release, the current alias must also be updated,
             * or otherwise there'll be conflicts with the "conflict" entries.
             */
            UpdateCurrentBranchAliasReleaseWorker::class,

            // Default workers
            UpdateReplaceReleaseWorker::class,
            SetCurrentMutualDependenciesReleaseWorker::class,
            SetNextMutualDependenciesReleaseWorker::class,
            UpdateBranchAliasReleaseWorker::class,

            // Add "-dev" again to the version
            BumpVersionForDevInMonorepoMetadataFileReleaseWorker::class,

            // Default workers
            PushNextDevReleaseWorker::class,
        ];
    }
}
