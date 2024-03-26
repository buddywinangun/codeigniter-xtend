<?php

declare(strict_types=1);

namespace Xtend\Extensions\Command;

use Xtend\Extensions\Json\PackageEntriesJsonProvider;
use Xtend\Extensions\ValueObject\Option;
use Xtend\Extensions\Command\CommandNaming;
use MonorepoBuilderPrefix202311\Nette\Utils\Json;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Input\InputOption;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202311\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;

final class PackageEntriesJsonCommand extends AbstractSymplifyCommand
{
    public function __construct(private PackageEntriesJsonProvider $packageEntriesJsonProvider)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Provides package entries in json format. Useful for GitHub Actions Workflow');
        $this->addOption(
            Option::FILTER,
            null,
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Filter the packages to those from the list of files. Useful to split monorepo on modified packages only',
            []
        );
        $this->addOption(
            Option::EXCLUDE_PACKAGE_PATH,
            null,
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Exclude the packages with the provided relative paths. Useful to not split monorepo on packages without their own repo',
            []
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string[] $fileFilter */
        $fileFilter = $input->getOption(Option::FILTER);
        /** @var string[] $excludePackagePaths */
        $excludePackagePaths = $input->getOption(Option::EXCLUDE_PACKAGE_PATH);

        $packageEntries = $this->packageEntriesJsonProvider->providePackageEntries(
            $fileFilter,
            $excludePackagePaths,
        );

        // must be without spaces, otherwise it breaks GitHub Actions json
        $json = Json::encode($packageEntries);
        $this->symfonyStyle->writeln($json);

        return self::SUCCESS;
    }
}
