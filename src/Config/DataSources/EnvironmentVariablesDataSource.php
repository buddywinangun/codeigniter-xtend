<?php

declare(strict_types=1);

namespace Xtend\Config\DataSources;

use Xtend\Monorepo\MonorepoMetadata;

class EnvironmentVariablesDataSource
{
    public final const GIT_BASE_BRANCH = 'GIT_BASE_BRANCH';
    public final const GIT_USER_NAME = 'GIT_USER_NAME';
    public final const GIT_USER_EMAIL = 'GIT_USER_EMAIL';

    /**
     * @return array<string,string>
     */
    public function getEnvironmentVariables(): array
    {
        return [
            self::GIT_BASE_BRANCH => MonorepoMetadata::GIT_BASE_BRANCH,
            self::GIT_USER_NAME => MonorepoMetadata::GIT_USER_NAME,
            self::GIT_USER_EMAIL => MonorepoMetadata::GIT_USER_EMAIL,
        ];
    }
}
