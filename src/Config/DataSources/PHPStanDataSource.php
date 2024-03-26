<?php

declare(strict_types=1);

namespace Xtend\Release\Config\DataSources;

class PHPStanDataSource
{
    public function getLevel(): int
    {
        return 8;
    }
}
