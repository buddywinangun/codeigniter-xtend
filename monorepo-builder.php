<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;
use Xtend\Config\ContainerConfigurationService;

return static function (MBConfig $mbConfig): void {
    require_once __DIR__ . '/vendor/autoload.php';
    $containerConfigurationService = new ContainerConfigurationService(
        $mbConfig,
        __DIR__
    );
    $containerConfigurationService->configureContainer();
};
