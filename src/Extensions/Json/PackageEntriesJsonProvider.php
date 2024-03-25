<?php

declare(strict_types=1);

namespace Xtend\Monorepo\Extensions\Json;

use Xtend\Monorepo\Extensions\Package\CustomPackageProvider;
use Xtend\Monorepo\Extensions\Utils\PackageUtils;
use Xtend\Monorepo\Extensions\ValueObject\Option;
use MonorepoBuilderPrefix202311\Symplify\PackageBuilder\Parameter\ParameterProvider;
use MonorepoBuilderPrefix202311\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;

final class PackageEntriesJsonProvider
{
    /**
     * @var array<string,string>
     */
    private array $packageOrganizations = [];

    public function __construct(
        private CustomPackageProvider $customPackageProvider,
        ParameterProvider $parameterProvider,
        private PackageUtils $packageUtils
    ) {
        $this->packageOrganizations = $parameterProvider->provideArrayParameter(Option::PACKAGE_ORGANIZATIONS);
    }

    /**
     * @param string[] $fileListFilter
     * @param string[] $excludePackageRelativePaths
     * @return array<array<string,string>>
     */
    public function providePackageEntries(
        array $fileListFilter = [],
        array $excludePackageRelativePaths = [],
    ): array {
        $packageEntries = [];
        $packages = $this->customPackageProvider->provide();
        foreach ($packages as $package) {
            $packageRelativePath = $package->getRelativePath();
            if (in_array($packageRelativePath, $excludePackageRelativePaths)) {
                continue;
            }
            // If provided, filter the packages to the ones containing the list of files.
            // Useful to launch GitHub runners to split modified packages only
            if ($fileListFilter !== [] && !$this->packageUtils->isPackageInFileList($packageRelativePath, $fileListFilter)) {
                continue;
            }
            $packageDirectory = ($packageRelativePath == 'appstarter') ? $packageRelativePath : dirname($packageRelativePath);
            $organization = $this->packageOrganizations[$packageDirectory] ?? null;
            if ($organization === null) {
                throw new ShouldNotHappenException(sprintf(
                    "The organization has not been set for package dir '%s' (when processing package '%s')",
                    $packageDirectory,
                    $packageRelativePath
                ));
            }
            $packageEntries[] = [
                'name' => $package->getShortName(),
                'path' => $packageRelativePath,
                'organization' => $organization,
            ];
        }

        return $packageEntries;
    }
}
