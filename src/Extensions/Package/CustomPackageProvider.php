<?php

declare(strict_types=1);

namespace Xtend\Monorepo\Extensions\Package;

use Xtend\Monorepo\Extensions\ValueObject\CustomPackage;
use Symplify\MonorepoBuilder\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use MonorepoBuilderPrefix202311\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;

final class CustomPackageProvider
{
    public function __construct(
        private ComposerJsonProvider $composerJsonProvider,
        private JsonFileManager $jsonFileManager
    ) {
    }

    /**
     * @return CustomPackage[]
     */
    public function provideWithTests(): array
    {
        return array_filter($this->provide(), function (CustomPackage $package): bool {
            return $package->hasTests();
        });
    }

    /**
     * @return CustomPackage[]
     */
    public function provide(): array
    {
        $packages = [];
        foreach ($this->composerJsonProvider->getPackagesComposerFileInfos() as $packagesComposerFileInfo) {
            /** @var array<string,mixed> */
            $json = $this->jsonFileManager->loadFromFileInfo($packagesComposerFileInfo);
            $packageName = $this->getPackageName($json);
            $packages[] = new CustomPackage($json, $packageName, $packagesComposerFileInfo);
        }

        usort($packages, function (CustomPackage $firstPackage, CustomPackage $secondPackage): int {
            return $firstPackage->getShortName() <=> $secondPackage->getShortName();
        });

        return $packages;
    }

    /**
     * @param array<string,mixed> $json
     */
    private function getPackageName(array $json): string
    {
        if (! isset($json[ComposerJsonSection::NAME])) {
            throw new ShouldNotHappenException();
        }

        return (string) $json[ComposerJsonSection::NAME];
    }
}
