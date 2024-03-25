<?php

declare(strict_types=1);

namespace Xtend\Extensions\ValueObject;

final class Option
{
    /**
     * @var string
     */
    public final const PACKAGE_ORGANIZATIONS = 'package-organizations';
    /**
     * @var string
     */
    public final const SKIP_MONOREPO_SPLIT_PACKAGE_PATHS = 'skip-monorepo-split-package-paths';
    /**
     * @var string
     */
    public final const ENVIRONMENT_VARIABLES = 'environment-variables';
    /**
     * @var string
     */
    public final const ENVIRONMENT_VARIABLE_NAME = 'environment-variable-name';
    /**
     * @var string
     */
    public final const JSON = 'json';
    /**
     * @var string
     */
    public final const PSR4_ONLY = 'psr4-only';
    /**
     * @var string
     */
    public final const LEVEL = 'level';
    /**
     * @var string
     */
    public final const SUBFOLDER = 'subfolder';
    /**
     * @var string
     */
    public final const FILTER = 'filter';
    /**
     * @var string
     */
    public final const EXCLUDE_PACKAGE_PATH = 'exclude-package-path';
    /**
     * @var string
     */
    public final const OUTPUT_FILE = 'output-file';
    /**
     * @var string
     */
    public final const SCOPED_ONLY = 'scoped-only';
}
