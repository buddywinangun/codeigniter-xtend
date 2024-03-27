<?php

declare(strict_types=1);

namespace Xtend\ReleaseWorker;

use PharIo\Version\Version;

/**
 * Set the PROD version (release tag) on the MonorepoMetadata (and remove "-dev")
 */
class ConvertStableTagVersionForProdInPackageReleaseWorker extends AbstractConvertStableTagVersionInPackageReleaseWorker
{
    public function work(Version $version): void
    {
        // Use the incoming provided version, so it also works for a downstream monorepo
        $replacements = [
            "/(\s+)const(\s+)VERSION(\s+)?=(\s+)?['\"][a-z0-9.-]+['\"](\s+)?;/" => " const VERSION = '" . $version->getVersionString() . "';",
        ];
        $this->fileContentReplacerSystem->replaceContentInFiles(
            $this->getPackages(),
            $replacements,
            true,
        );
    }

    public function getDescription(Version $version): string
    {
        return 'Have the "Stable tag" point to the PROD version in the package';
    }
}
