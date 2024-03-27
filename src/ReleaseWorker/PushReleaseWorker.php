<?php

declare (strict_types=1);
namespace Xtend\ReleaseWorker;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;
use Symplify\MonorepoBuilder\Utils\VersionUtils;
use Symplify\MonorepoBuilder\ValueObject\Option;
use MonorepoBuilderPrefix202311\Symplify\PackageBuilder\Parameter\ParameterProvider;
final class PushReleaseWorker implements ReleaseWorkerInterface
{
    /**
     * @var \Symplify\MonorepoBuilder\Release\Process\ProcessRunner
     */
    private $processRunner;
    /**
     * @var \Symplify\MonorepoBuilder\Utils\VersionUtils
     */
    private $versionUtils;
    /**
     * @var string
     */
    private $branchName;
    public function __construct(ProcessRunner $processRunner, VersionUtils $versionUtils, ParameterProvider $parameterProvider)
    {
        $this->processRunner = $processRunner;
        $this->versionUtils = $versionUtils;
        $this->branchName = $parameterProvider->provideStringParameter(Option::DEFAULT_BRANCH_NAME);
    }
    public function work(Version $version) : void
    {
        $versionInString = $this->getVersion($version);
        $gitAddCommitCommand = \sprintf('git add --force --ignore-errors . && git commit -m "chore(release): open %s" && git push --tags origin HEAD:%s', $versionInString, $this->branchName);
        $this->processRunner->run($gitAddCommitCommand);
    }
    public function getDescription(Version $version) : string
    {
        $versionInString = $this->getVersion($version);
        return \sprintf('Push "%s" open to remote repository', $versionInString);
    }
    private function getVersion(Version $version) : string
    {
        return $this->versionUtils->getCurrentAliasFormat($version);
    }
}
