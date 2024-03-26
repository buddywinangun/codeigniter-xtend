<?php

declare(strict_types=1);

namespace Xtend\Release\Concerns;

use MonorepoBuilderPrefix202311\Symfony\Component\Console\Input\ArgvInput;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Output\ConsoleOutput;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202311\Symfony\Component\Console\Style\SymfonyStyle;
use MonorepoBuilderPrefix202311\Symfony\Component\Process\ExecutableFinder;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;

trait ConcreteFactory
{
    /** @var null|ExecutableFinder */
    protected static $executableFinder;

    /** @var null|ProcessRunner */
    protected static $runner;

    /** @var null|SymfonyStyle */
    protected static $symfonyStyle;

    public static function createExecutableFinder(): ExecutableFinder
    {
        if (! self::$executableFinder instanceof ExecutableFinder) {
            self::$executableFinder = new ExecutableFinder();
        }

        return self::$executableFinder;
    }

    public static function createProcessRunner(?SymfonyStyle $symfonyStyle = null): ProcessRunner
    {
        if (! self::$runner instanceof ProcessRunner || $symfonyStyle instanceof SymfonyStyle) {
            self::$runner = new ProcessRunner($symfonyStyle ?: self::createSymfonyStyle());
        }

        return self::$runner;
    }

    public static function createSymfonyStyle(?InputInterface $input = null, ?OutputInterface $output = null): SymfonyStyle
    {
        if (
            ! self::$symfonyStyle instanceof SymfonyStyle
            || $input instanceof InputInterface
            || $output instanceof OutputInterface
        ) {
            self::$symfonyStyle = new SymfonyStyle(
                $input ?? new ArgvInput(),
                $output ?? new ConsoleOutput(OutputInterface::VERBOSITY_VERY_VERBOSE)
            );
        }

        return self::$symfonyStyle;
    }
}
