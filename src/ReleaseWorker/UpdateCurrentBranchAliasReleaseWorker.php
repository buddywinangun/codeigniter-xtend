<?php

declare(strict_types=1);

namespace Xtend\ReleaseWorker;

use PharIo\Version\Version;
use Xtend\Extensions\Utils\VersionUtils;
use Symplify\MonorepoBuilder\DevMasterAliasUpdater;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

final class UpdateCurrentBranchAliasReleaseWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private DevMasterAliasUpdater $devMasterAliasUpdater,
        private ComposerJsonProvider $composerJsonProvider,
        private VersionUtils $versionUtils
    ) {
    }

    public function work(Version $version): void
    {
        $nextAlias = $this->versionUtils->getCurrentAliasFormat($version);

        $this->devMasterAliasUpdater->updateFileInfosWithAlias(
            $this->composerJsonProvider->getPackagesComposerFileInfos(),
            $nextAlias
        );
    }

    public function getDescription(Version $version): string
    {
        $nextAlias = $this->versionUtils->getCurrentAliasFormat($version);

        return sprintf('Set branch alias "%s" to all packages', $nextAlias);
    }
}
