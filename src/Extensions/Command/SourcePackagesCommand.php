<?php

declare(strict_types=1);

namespace Xtend\Release\Extensions\Command;

use Xtend\Release\Extensions\Json\SourcePackagesProvider;
use Xtend\Release\Extensions\ValueObject\Option;
use Xtend\Release\Extensions\Command\CommandNaming;
use MonorepoBuilderPrefix202311\Nette\Utils\Json;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Input\InputOption;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202311\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;

final class SourcePackagesCommand extends AbstractSymplifyCommand
{
    public function __construct(
        private SourcePackagesProvider $sourcePackagesProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Provides source packages (i.e. packages with code under src/ and tests/), in json format. Useful for GitHub Actions Workflow');
        $this->addOption(
            Option::JSON,
            null,
            InputOption::VALUE_NONE,
            'Print with encoded JSON format.'
        );
        $this->addOption(
            Option::PSR4_ONLY,
            null,
            InputOption::VALUE_NONE,
            'Skip the non-PSR-4 packages.'
        );
        $this->addOption(
            Option::SUBFOLDER,
            null,
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Add paths to a subfolder from the package.',
            []
        );
        $this->addOption(
            Option::FILTER,
            null,
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Filter the packages to those from the list of files. Useful to split monorepo on modified packages only',
            []
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $asJSON = (bool) $input->getOption(Option::JSON);
        $psr4Only = (bool) $input->getOption(Option::PSR4_ONLY);

        $packagesToSkip = [];

        /** @var string[] $subfolders */
        $subfolders = $input->getOption(Option::SUBFOLDER);
        /** @var string[] $fileFilter */
        $fileFilter = $input->getOption(Option::FILTER);

        $sourcePackages = $this->sourcePackagesProvider->provideSourcePackages(
            $psr4Only,
            $packagesToSkip,
            $fileFilter
        );

        // Point to some subfolder?
        if ($subfolders !== []) {
            $sourcePackagePaths = [];
            foreach ($sourcePackages as $sourcePackage) {
                foreach ($subfolders as $subfolder) {
                    $sourcePackageSubfolder = $sourcePackage . DIRECTORY_SEPARATOR . $subfolder;
                    if (!file_exists($sourcePackageSubfolder)) {
                        continue;
                    }
                    $sourcePackagePaths[] = $sourcePackageSubfolder;
                }
            }
        } else {
            $sourcePackagePaths = $sourcePackages;
        }

        // JSON: must be without spaces, otherwise it breaks GitHub Actions json
        $response = $asJSON ? Json::encode($sourcePackagePaths) : implode(' ', $sourcePackagePaths);
        $this->symfonyStyle->writeln($response);

        return self::SUCCESS;
    }
}
