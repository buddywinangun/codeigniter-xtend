<?php

declare(strict_types=1);

namespace Xtend\Monorepo\Extensions\Command;

use Xtend\Monorepo\Extensions\ValueObject\Option;
use Xtend\Monorepo\Extensions\Command\CommandNaming;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Input\InputArgument;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202311\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use MonorepoBuilderPrefix202311\Symplify\PackageBuilder\Parameter\ParameterProvider;

final class EnvVarCommand extends AbstractSymplifyCommand
{
    /**
     * @var array<string,string>
     */
    private array $environmentVariables = [];

    public function __construct(
        ParameterProvider $parameterProvider
    ) {
        parent::__construct();
        $this->environmentVariables = $parameterProvider->provideArrayParameter(Option::ENVIRONMENT_VARIABLES);
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Get the value for an environment variable set via the Monorepo Builder config');
        $this->addArgument(
            Option::ENVIRONMENT_VARIABLE_NAME,
            InputArgument::REQUIRED,
            'The name of the environment variable'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string */
        $environmentVariable = $input->getArgument(Option::ENVIRONMENT_VARIABLE_NAME);

        // If not set, return an empty string
        $environmentVariableValue = (string) ($this->environmentVariables[$environmentVariable] ?? '');

        // If the value is boolean, it gets converted to string:
        // true => "1"
        // false => ""
        $this->symfonyStyle->writeln($environmentVariableValue);

        return self::SUCCESS;
    }
}
