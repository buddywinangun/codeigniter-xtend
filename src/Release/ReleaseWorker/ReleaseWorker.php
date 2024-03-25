<?php

declare(strict_types=1);

namespace Xtend\Monorepo\Release\ReleaseWorker;

use Xtend\Monorepo\Concerns\ConcreteFactory;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

abstract class ReleaseWorker implements ReleaseWorkerInterface
{
    use ConcreteFactory;
}
