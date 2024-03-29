<?php

declare(strict_types=1);

namespace Xtend\Extensions\Json;

use Xtend\Extensions\Package\CustomPackageProvider;
use Xtend\Extensions\Utils\PackageUtils;
use Xtend\Extensions\ValueObject\Option;
use MonorepoBuilderPrefix202311\Symplify\PackageBuilder\Parameter\ParameterProvider;

final class SkipMonorepoSplitPackagesProvider
{
    /**
     * @var string[]
     */
    private array $skipMonorepoSplitPackagePaths = [];

    public function __construct(
        private CustomPackageProvider $customPackageProvider,
        ParameterProvider $parameterProvider,
        private PackageUtils $packageUtils
    ) {
        $this->skipMonorepoSplitPackagePaths = $parameterProvider->provideArrayParameter(Option::SKIP_MONOREPO_SPLIT_PACKAGE_PATHS);
    }

    /**
     * @return string[]
     */
    public function provideSkipMonorepoSplitPackages(): array
    {
        if ($this->skipMonorepoSplitPackagePaths === []) {
            return [];
        }

        $packageEntries = [];
        $packages = $this->customPackageProvider->provide();
        foreach ($packages as $package) {
            $packageRelativePath = $package->getRelativePath();
            if (!$this->packageUtils->doesPackageContainAnyPath($packageRelativePath, $this->skipMonorepoSplitPackagePaths)) {
                continue;
            }
            $packageEntries[] = $packageRelativePath;
        }

        return $packageEntries;
    }
}
