<?php

declare(strict_types=1);

namespace Xtend\Extensions\Neon;

use Nette\Neon\Encoder;
use Nette\Neon\Neon;
use MonorepoBuilderPrefix202311\Nette\Utils\Strings;

/**
 * @api
 */
final class NeonPrinter
{
    /**
     * @see https://regex101.com/r/r8DGyV/1
     * @var string
     */
    private const TAGS_REGEX = '#tags:\s+\-\s+(?<tag>.*?)$#ms';

    /**
     * @see https://regex101.com/r/KjekIe/1
     * @var string
     */
    private const ARGUMENTS_DOUBLE_SPACE_REGEX = '#\n(\n\s+arguments:)#ms';

    /**
     * @param mixed[] $phpStanNeon
     */
    public function printNeon(array $phpStanNeon): string
    {
        $neonContent = Neon::encode($phpStanNeon, Encoder::BLOCK, '    ');

        // inline single tags, dummy
        $neonContent = $this->inlineSingleTags($neonContent);

        $neonContent = $this->fixDoubleSpaceInArguments($neonContent);

        return rtrim($neonContent) . PHP_EOL;
    }

    private function inlineSingleTags(string $neonContent): string
    {
        return Strings::replace($neonContent, self::TAGS_REGEX, 'tags: [$1]');
    }

    private function fixDoubleSpaceInArguments(string $neonContent): string
    {
        return Strings::replace($neonContent, self::ARGUMENTS_DOUBLE_SPACE_REGEX, '$1');
    }
}
