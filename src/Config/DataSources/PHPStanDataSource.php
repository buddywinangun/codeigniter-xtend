<?php

declare(strict_types=1);

namespace Xtend\Monorepo\Config\DataSources;

class PHPStanDataSource
{
    public function getLevel(): int
    {
        return 8;
    }
}
