<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Xtend\Autoloader;

/*
 * ---------------------------------------------------------------
 * SETUP OUR PATH CONSTANTS
 * ---------------------------------------------------------------
 *
 * The path constants provide convenient access to the folders
 * throughout the application. We have to setup them up here
 * so they are available in the config files that are loaded.
 */

// The path to the project root directory. Just above APPPATH.
if (! defined('ROOTPATH')) {
    define('ROOTPATH', realpath(APPPATH . '../') . DIRECTORY_SEPARATOR);
}

/*
 * ---------------------------------------------------------------
 * Auto-load All Classes with PSR-4
 * ---------------------------------------------------------------
 *
 * PSR-4 Autoloader for Codeiginiter 3 application
 * After registering \Xtend\Autoloader\Autoloader, you could auto-load every
 * classes in the whole Codeigniter application with `app` PSR-4 prefix by default.
 */
class Autoloader
{
    /**
     * Stores namespaces as key, and path as values.
     *
     * @var array<string, array<string>>
     */
    protected $prefixes = [];

    /**
     * This maps the locations of any namespaces in your application to
     * their location on the file system. These are used by the autoloader
     * to locate files the first time they have been instantiated.
     *
     * @var array<string, string>
     */
    protected $corePsr4 = [
        'App' => APPPATH
    ];

    /**
     * Register the loader with the SPL autoloader stack.
     */
    public function register()
    {
		if (file_exists(APPPATH.'config/autoload.php'))
		{
			include(APPPATH.'config/autoload.php');
		}

		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/autoload.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/autoload.php');
		}

        $autoload['psr4'] = array_merge($this->corePsr4, $autoload['psr4']);

        // We have to have one or the other, though we don't enforce the need
        // to have both present in order to work.
        if ($autoload['psr4'] === []) {
            throw new InvalidArgumentException('Config array must contain either the \'psr4\' key or the \'classmap\' key.');
        }

        if ($autoload['psr4'] !== []) {
            $this->addNamespace($autoload['psr4']);
        }

        // Prepend the PSR4  autoloader for maximum performance.
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    /**
     * Registers namespaces with the autoloader.
     *
     * @param array<string, array<int, string>|string>|string $namespace
     * @phpstan-param array<string, list<string>|string>|string $namespace
     *
     * @return $this
     */
    public function addNamespace($namespace, ?string $path = null)
    {
        if (is_array($namespace)) {
            foreach ($namespace as $prefix => $namespacedPath) {
                $prefix = trim($prefix, '\\');

                if (is_array($namespacedPath)) {
                    foreach ($namespacedPath as $dir) {
                        $this->prefixes[$prefix][] = rtrim($dir, '\\/') . DIRECTORY_SEPARATOR;
                    }

                    continue;
                }

                $this->prefixes[$prefix][] = rtrim($namespacedPath, '\\/') . DIRECTORY_SEPARATOR;
            }
        } else {
            $this->prefixes[trim($namespace, '\\')][] = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
        }

        return $this;
    }

    /**
     * Loads the class file for a given class name.
     *
     * @internal For `spl_autoload_register` use.
     *
     * @param string $class The fully qualified class name.
     */
    public function loadClass(string $class): void
    {
        $this->loadInNamespace($class);
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name
     *
     * @return false|string The mapped file name on success, or boolean false on fail
     */
    protected function loadInNamespace(string $class)
    {
        if (strpos($class, '\\') === false) {
            return false;
        }

        foreach ($this->prefixes as $namespace => $directories) {
            foreach ($directories as $directory) {
                $directory = rtrim($directory, '\\/');

                if (strpos($class, $namespace) === 0) {
                    $filePath = $directory . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($namespace))) . '.php';
                    $filename = $this->includeFile($filePath);

                    if ($filename) {
                        return $filename;
                    }
                }
            }
        }

        // never found a mapped file
        return false;
    }

    /**
     * A central way to include a file. Split out primarily for testing purposes.
     *
     * @return false|string The filename on success, false if the file is not loaded
     */
    protected function includeFile(string $file)
    {
        if (is_file($file)) {
            include_once $file;

            return $file;
        }

        return false;
    }
}
