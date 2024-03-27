<?php

declare(strict_types=1);

namespace Xtend\Extensions\ValueObject;

final class Option
{
    /**
     * @var string
     */
    public const PACKAGE_ORGANIZATIONS = 'package-organizations';
    /**
     * @var string
     */
    public const SKIP_MONOREPO_SPLIT_PACKAGE_PATHS = 'skip-monorepo-split-package-paths';
    /**
     * @var string
     */
    public const ENVIRONMENT_VARIABLES = 'environment-variables';
    /**
     * @var string
     */
    public const ENVIRONMENT_VARIABLE_NAME = 'environment-variable-name';
    /**
     * @var string
     */
    public const JSON = 'json';
    /**
     * @var string
     */
    public const PSR4_ONLY = 'psr4-only';
    /**
     * @var string
     */
    public const LEVEL = 'level';
    /**
     * @var string
     */
    public const SUBFOLDER = 'subfolder';
    /**
     * @var string
     */
    public const FILTER = 'filter';
    /**
     * @var string
     */
    public const EXCLUDE_PACKAGE_PATH = 'exclude-package-path';
    /**
     * @var string
     */
    public const OUTPUT_FILE = 'output-file';
    /**
     * @var string
     */
    public const SCOPED_ONLY = 'scoped-only';
}
