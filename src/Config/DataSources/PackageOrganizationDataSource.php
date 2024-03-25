<?php

declare(strict_types=1);

namespace Xtend\Monorepo\Config\DataSources;

class PackageOrganizationDataSource
{
    public function __construct(protected string $rootDir)
    {
    }

    /**
     * @return array<string,string>
     */
    public function getPackagePathOrganizations(): array
    {
        return [
            'packages' => 'buddywinangun',
            'appstarter' => 'buddywinangun',
        ];
    }

    /**
     * @return array<string>
     */
    public function getPackageDirectories(): array
    {
        return array_map(
            fn (string $packagePath) => $this->rootDir . '/' . $packagePath,
            array_keys($this->getPackagePathOrganizations())
        );
    }

    /**
     * @return array<string>
     */
    public function getPackageDirectoryExcludes(): array
    {
        return [];
    }
}
