<?php

declare(strict_types=1);

namespace Xtend\Config\Symplify\MonorepoBuilder\DataSources;

use Xtend\OnDemand\Symplify\MonorepoBuilder\Release\ReleaseWorker\GuardOnDefaultBranchReleaseWorker;
use Xtend\OnDemand\Symplify\MonorepoBuilder\Release\ReleaseWorker\ConvertVersionForProdInMonorepoMetadataFileReleaseWorker;
use Xtend\OnDemand\Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateCurrentBranchAliasReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateReplaceReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualConflictsReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
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
            ConvertVersionForProdInMonorepoMetadataFileReleaseWorker::class,

            /**
             * When doing a major release, the current alias must also be updated,
             * or otherwise there'll be conflicts with the "conflict" entries.
             */
            UpdateCurrentBranchAliasReleaseWorker::class,

            // Default workers
            UpdateReplaceReleaseWorker::class,
            // SetCurrentMutualConflictsReleaseWorker::class,
            // SetCurrentMutualDependenciesReleaseWorker::class,
            // TagVersionReleaseWorker::class,
            // PushTagReleaseWorker::class,
            SetNextMutualDependenciesReleaseWorker::class,
            UpdateBranchAliasReleaseWorker::class,

            // Default workers
            // PushNextDevReleaseWorker::class,
        ];
    }
}
