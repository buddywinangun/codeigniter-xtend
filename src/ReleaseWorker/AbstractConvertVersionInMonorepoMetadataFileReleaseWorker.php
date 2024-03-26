<?php

declare(strict_types=1);

namespace Xtend\ReleaseWorker;

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
        $this->monorepoMetadataFile = $this->getMonorepoMetadataFile();
    }

    protected function getMonorepoMetadataFile(): string
    {
        return dirname(__DIR__, 2) . '/src/Monorepo/MonorepoMetadata.php';
    }
}
