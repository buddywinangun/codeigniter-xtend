<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;
use Xtend\Config\Symplify\MonorepoBuilder\Configurators\ContainerConfigurationService;

require_once __DIR__ . '/vendor/autoload.php';

return static function (MBConfig $mbConfig): void {
    $containerConfigurationService = new ContainerConfigurationService(
        $mbConfig,
        __DIR__
    );
    $containerConfigurationService->configureContainer();
};
