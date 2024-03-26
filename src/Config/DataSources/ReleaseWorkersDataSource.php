<?php

declare(strict_types=1);

namespace Xtend\Release\Config\DataSources;

use Xtend\Release\ReleaseWorker\GuardOnDefaultBranchReleaseWorker;
use Xtend\Release\ReleaseWorker\ConvertVersionForProdInMonorepoMetadataFileReleaseWorker;
use Xtend\Release\ReleaseWorker\UpdateCurrentBranchAliasReleaseWorker;
use Xtend\Release\ReleaseWorker\UpdateChangelogViaPhpReleaseWorker;
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
            ConvertVersionForProdInMonorepoMetadataFileReleaseWorker::class,

            /**
             * When doing a major release, the current alias must also be updated,
             * or otherwise there'll be conflicts with the "conflict" entries.
             */
            UpdateCurrentBranchAliasReleaseWorker::class,

            // Default workers
            UpdateReplaceReleaseWorker::class,
            SetCurrentMutualDependenciesReleaseWorker::class,
            // AddTagToChangelogReleaseWorker::class,
            // UpdateChangelogViaPhpReleaseWorker::class,
            // SetNextMutualDependenciesReleaseWorker::class,
            // UpdateBranchAliasReleaseWorker::class,
            // PushNextDevReleaseWorker::class,
        ];
    }
}
