<?php

declare(strict_types=1);

namespace Xtend\Release\ReleaseWorker;

use Xtend\Release\Concerns\ConcreteFactory;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

abstract class ReleaseWorker implements ReleaseWorkerInterface
{
    use ConcreteFactory;
}
