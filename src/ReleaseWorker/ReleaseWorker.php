<?php

declare(strict_types=1);

namespace Xtend\ReleaseWorker;

use Xtend\Concerns\ConcreteFactory;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

abstract class ReleaseWorker implements ReleaseWorkerInterface
{
    use ConcreteFactory;
}
