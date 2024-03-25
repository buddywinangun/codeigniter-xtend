<?php

declare(strict_types=1);

namespace Xtend\Monorepo\Contracts;

/**
 * @see https://github.com/marcocesarato/php-conventional-changelog
 */
interface ChangelogContract
{
    public static function getChangelog(): string;
}
