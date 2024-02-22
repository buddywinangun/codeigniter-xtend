<?php

declare(strict_types=1);

namespace Xtend\Config\Symplify\MonorepoBuilder\Configurators;

use Xtend\Config\Symplify\MonorepoBuilder\DataSources\MonorepoSplitPackageDataSource;
use Xtend\Config\Symplify\MonorepoBuilder\DataSources\PackageOrganizationDataSource;
use Xtend\Config\Symplify\MonorepoBuilder\DataSources\EnvironmentVariablesDataSource;
use Xtend\Config\Symplify\MonorepoBuilder\DataSources\PHPStanDataSource;
use Xtend\Config\Symplify\MonorepoBuilder\DataSources\ReleaseWorkersDataSource;
use Xtend\Extensions\Symplify\MonorepoBuilder\ValueObject\Option as CustomOption;
use Xtend\Monorepo\MonorepoMetadata;
use Symplify\MonorepoBuilder\ValueObject\Option;
use Xtend\Extensions\Symplify\MonorepoBuilder\Neon\NeonPrinter;
use MonorepoBuilderPrefix202311\Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

class ContainerConfigurationService
{
    public function __construct(
        protected $containerConfigurator,
        protected string $rootDirectory,
    ) {
    }

    public function configureContainer(): void
    {
        $parameters = $this->containerConfigurator->parameters();

        $parameters->set(Option::DEFAULT_BRANCH_NAME, MonorepoMetadata::GIT_BASE_BRANCH);

        /**
         * Packages handled by the monorepo
         */
        if ($packageOrganizationConfig = $this->getPackageOrganizationDataSource()) {
            $parameters->set(
                CustomOption::PACKAGE_ORGANIZATIONS,
                $packageOrganizationConfig->getPackagePathOrganizations()
            );
            $parameters->set(
                Option::PACKAGE_DIRECTORIES,
                $packageOrganizationConfig->getPackageDirectories()
            );
            $parameters->set(
                Option::PACKAGE_DIRECTORIES_EXCLUDES,
                $packageOrganizationConfig->getPackageDirectoryExcludes()
            );
        }

        /**
         * Packages not to do the monorepo split
         */
        if ($skipMonorepoSplitPackageConfig = $this->getMonorepoSplitPackageDataSource()) {
            $parameters->set(
                CustomOption::SKIP_MONOREPO_SPLIT_PACKAGE_PATHS,
                $skipMonorepoSplitPackageConfig->getSkipMonorepoSplitPackagePaths()
            );
        }

        /**
         * Environment variables
         */
        if ($environmentVariablesConfig = $this->getEnvironmentVariablesDataSource()) {
            $parameters->set(
                CustomOption::ENVIRONMENT_VARIABLES,
                $environmentVariablesConfig->getEnvironmentVariables()
            );
        }

        /**
         * Temporary hack! PHPStan is currently failing for these packages,
         * because they have not been fully converted to PSR-4 (WIP),
         * and converting them will take some time. Hence, for the time being,
         * skip them from executing PHPStan, to avoid the CI from failing
         */
        if ($phpStanConfig = $this->getPHPStanDataSource()) {
            $parameters->set(
                CustomOption::LEVEL,
                $phpStanConfig->getLevel()
            );
        }

        /**
         * Configure services
         */
        $services = $this->containerConfigurator->services();
        $services->defaults()
            ->autowire()
            ->autoconfigure();

        /**
         * Set all custom services
         */
        $this->setServices($services);
    }

    protected function getPackageOrganizationDataSource(): ?PackageOrganizationDataSource
    {
        return new PackageOrganizationDataSource($this->rootDirectory);
    }

    protected function getMonorepoSplitPackageDataSource(): ?MonorepoSplitPackageDataSource
    {
        return new MonorepoSplitPackageDataSource($this->rootDirectory);
    }

    protected function getEnvironmentVariablesDataSource(): ?EnvironmentVariablesDataSource
    {
        return new EnvironmentVariablesDataSource();
    }

    protected function getPHPStanDataSource(): ?PHPStanDataSource
    {
        return new PHPStanDataSource();
    }

    protected function getReleaseWorkersDataSource(): ?ReleaseWorkersDataSource
    {
        return new ReleaseWorkersDataSource();
    }

    protected function setServices(ServicesConfigurator $services): void
    {
        /**
         * Set all custom services
         */
        $this->setCustomServices($services);

        /**
         * Release workers
         */
        $this->setReleaseWorkerServices($services);
    }

    protected function setCustomServices(ServicesConfigurator $services): void
    {
        $services
            ->set(NeonPrinter::class) // Required to inject into PHPStanNeonContentProvider
            ->load('Xtend\\Config\\', $this->rootDirectory . '/src/Config/*')
            ->load('Xtend\\Extensions\\', $this->rootDirectory . '/src/Extensions/*')
            ->load('Xtend\\Monorepo\\', $this->rootDirectory . '/src/Monorepo/*');
    }

    protected function setReleaseWorkerServices(ServicesConfigurator $services): void
    {
        /**
         * Release workers - in order to execute
         */
        if ($releaseWorkersConfig = $this->getReleaseWorkersDataSource()) {
            foreach ($releaseWorkersConfig->getReleaseWorkerClasses() as $releaseWorkerClass) {
                $services->set($releaseWorkerClass);
            }
        }
    }
}
