<?php

declare(strict_types=1);

namespace Xtend\Config\Symplify\MonorepoBuilder\DataSources;

class PHPStanDataSource
{
    public function getLevel(): int
    {
        return 8;
    }
}
