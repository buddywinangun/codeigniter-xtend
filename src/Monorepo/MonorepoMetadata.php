<?php

declare(strict_types=1);

namespace Xtend\Monorepo;

final class MonorepoMetadata
{
    /**
     * This const will reflect the current version of the monorepo.
     *
     * Important: This code is read-only! A ReleaseWorker
     * will search for this pattern using a regex, to update the
     * version when creating a new release
     * (i.e. via `composer release-major|minor|patch`).
     *
     */
    public const VERSION = '4.1.0-dev';
    /**
     * This const will reflect the latest published tag in GitHub.
     *
     * Important: This code is read-only! A ReleaseWorker
     * will search for this pattern using a regex, to update the
     * version when creating a new release
     * (i.e. via `composer release-major|minor|patch`).
     *
     */
    public const LATEST_PROD_VERSION = '4.1.0';

    public const GIT_BASE_BRANCH = 'master';
    public const GIT_USER_NAME = 'buddywinangun';
    public const GIT_USER_EMAIL = 'buddywinangun@gmail.com';

    public const GITHUB_REPO_OWNER = 'buddywinangun';
    public const GITHUB_REPO_NAME = 'codeigniter-xtend';
}
