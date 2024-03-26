<?php

declare(strict_types=1);

namespace Xtend\ReleaseWorker;

use Xtend\Contracts\ChangelogContract;
use MonorepoBuilderPrefix202311\Webmozart\Assert\Assert;
use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;

/**
 * @see https://github.com/marcocesarato/php-conventional-changelog
 */
class UpdateChangelogViaPhpReleaseWorker extends ReleaseWorker implements ChangelogContract
{
    /** @var null|string */
    private static $changelog;

    /** @var ProcessRunner */
    private $processRunner;

    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    public static function check(): void
    {
        Assert::isEmpty(self::createProcessRunner()->run('git status --short'));
        self::createProcessRunner()->run('./vendor/bin/conventional-changelog -V');
    }

    public static function getChangelog(): string
    {
        if (empty(self::$changelog)) {
            return '';
        }

        $lines = array_filter(explode(PHP_EOL, self::$changelog), static function (string $line): bool {
            return str_starts_with($line, '+')
                && ! str_starts_with($line, '+++')
                && ! str_starts_with($line, '+# ')
                && ! str_starts_with($line, '+## ');
        });

        $lines = implode(PHP_EOL, array_map(static function (string $line): string {
            return ltrim($line, '+');
        }, $lines));
        if (! str_contains($lines, '### ') || ! str_contains($lines, '* ')) {
            return '';
        }

        return trim($lines);
    }

    public function work(Version $version): void
    {
        $originalString = $version->getOriginalString();
        $previousTag = $this->toPreviousTag($originalString);

        $this->processRunner->run(sprintf(
            "./vendor/bin/conventional-changelog %s --to-tag=$originalString --ver=$originalString --ansi -v",
            $previousTag ? "--from-tag=$previousTag" : '--first-release'
        ));
        $this->processRunner->run("git checkout -- *.json && git add CHANGELOG.md && git commit -m \"chore(release): $originalString\" --no-verify && git push");

        self::$changelog = $this->processRunner->run('git show');
    }

    public function getDescription(Version $version): string
    {
        return sprintf('Update changelog "%s (%s)"', $version->getOriginalString(), date('Y-m-d'));
    }

    protected function toPreviousTag(string $tag): string
    {
        $tags = (array) explode(PHP_EOL, $this->processRunner->run('git tag --sort=-committerdate'));
        $previousTagIndex = array_search($tag, $tags, true) + 1;

        return $tags[$previousTagIndex] ?? '';
    }
}
