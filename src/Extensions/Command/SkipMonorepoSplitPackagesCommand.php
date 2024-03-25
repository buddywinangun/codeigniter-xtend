<?php

declare(strict_types=1);

namespace Xtend\Extensions\Command;

use Xtend\Extensions\Json\SkipMonorepoSplitPackagesProvider;
use Xtend\Extensions\Command\CommandNaming;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202311\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;

final class SkipMonorepoSplitPackagesCommand extends AbstractSymplifyCommand
{
    public function __construct(private SkipMonorepoSplitPackagesProvider $skipMonorepoSplitPackagesProvider)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Provides packages to skip splitting the monorepo to, in json format. Useful for GitHub Actions Workflow');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $skipMonorepoSplitPackages = $this->skipMonorepoSplitPackagesProvider->provideSkipMonorepoSplitPackages();

        $this->symfonyStyle->writeln(implode(' ', $skipMonorepoSplitPackages));

        return self::SUCCESS;
    }
}
