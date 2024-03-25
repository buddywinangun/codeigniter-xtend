<?php

declare(strict_types=1);

namespace Xtend\Monorepo\Extensions\SmartFile;

use MonorepoBuilderPrefix202311\Nette\Utils\Strings;
use MonorepoBuilderPrefix202311\Symplify\SmartFileSystem\SmartFileInfo;
use MonorepoBuilderPrefix202311\Symplify\SmartFileSystem\SmartFileSystem;

final class FileContentReplacerSystem
{
    public function __construct(
        private SmartFileSystem $smartFileSystem,
    ) {
    }

    /**
     * @param string[] $files
     * @param array<string,string> $stringOrRegexPatternReplacements a string or regex pattern to search, and its replacement
     */
    public function replaceContentInFiles(
        array $files,
        array $stringOrRegexPatternReplacements,
        bool $useRegex
    ): void {
        foreach ($files as $file) {
            $fileContent = $this->smartFileSystem->readFile($file);
            foreach ($stringOrRegexPatternReplacements as $stringOrRegexPattern => $replacement) {
                if ($useRegex) {
                    $fileContent = Strings::replace(
                        $fileContent,
                        $stringOrRegexPattern,
                        $replacement
                    );
                } else {
                    $fileContent = str_replace(
                        $stringOrRegexPattern,
                        $replacement,
                        $fileContent
                    );
                }
            }
            $this->smartFileSystem->dumpFile($file, $fileContent);
        }
    }

    /**
     * @param SmartFileInfo[] $smartFileInfos
     * @param array<string,string> $stringOrRegexPatternReplacements a string or regex pattern to search, and its replacement
     */
    public function replaceContentInSmartFileInfos(
        array $smartFileInfos,
        array $stringOrRegexPatternReplacements,
        bool $useRegex
    ): void {
        $files = array_map(
            fn (SmartFileInfo $smartFileInfo) => $smartFileInfo->getRealPath(),
            $smartFileInfos
        );
        $this->replaceContentInFiles($files, $stringOrRegexPatternReplacements, $useRegex);
    }
}
