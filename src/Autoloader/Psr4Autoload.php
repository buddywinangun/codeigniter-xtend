<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * PSR-4 Autoloader for Codeiginiter 3 application
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Xtend\Autoloader;

class Psr4Autoload
{
    /**
     * @var string Nampsapce prefix refered to application root
     */
    const DEFAULT_PREFIX = "app";

    /**
     * Register Autoloader
     *
     * @param string $prefix PSR-4 namespace prefix
     */
    public static function register($prefix = null)
    {
        $prefix = ($prefix) ? (string)$prefix : self::DEFAULT_PREFIX;

        spl_autoload_register(function ($classname) use ($prefix) {
            // Prefix check
            if (strpos(strtolower($classname), "{$prefix}\\") === 0) {
                // Locate class relative path
                $classname = str_replace("{$prefix}\\", "", $classname);
                $filepath = APPPATH .  str_replace('\\', DIRECTORY_SEPARATOR, ltrim($classname, '\\')) . '.php';

                if (file_exists($filepath)) {
                    require $filepath;
                }
            }
        });
    }
}
