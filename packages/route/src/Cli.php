<?php

namespace CodeigniterXtend\Route;

use CodeigniterXtend\Route\RouteBuilder as Route;

/**
 * CLI handler
 *
 * (Due security reasons, mostly commands defined here are disbled in 'production'
 * and 'testing' environments)
 */
class Cli
{
  /**
   * Registers all 'make' commands
   *
   * @return void
   */
  public static function maker()
  {
    if (ENVIRONMENT !== 'development') {
      return;
    }
  }

  /**
   * Registers the 'migrate' command
   *
   * @return void
   */
  public static function migrations()
  {
    if (ENVIRONMENT !== 'development') {
      return;
    }

    Route::group('migrate', function () {
      Route::cli('{version?}', function ($version = null) {
        self::migrate($version);
      });
    });
  }

  /**
   * Runs a migration
   *
   * @param  string  $version (Optional)
   *
   * @return void
   */
  private static function migrate($version = null)
  {
    if ($version == 'reverse') {
      self::migrate('0');
      return;
    }

    if ($version == 'refresh') {
      self::migrate('0');
      self::migrate();
      return;
    }

    ci()->load->library('migration');

    $migrations = ci()->migration->find_migrations();

    $_migrationsTable = new \ReflectionProperty('CI_Migration', '_migration_table');
    $_migrationsTable->setAccessible(true);
    $_migrationsTable = $_migrationsTable->getValue(ci()->migration);

    $old = ci()->db->get($_migrationsTable)->result()[0]->version;

    $migrate = function () use ($version) {
      if ($version === null) {
        return ci()->migration->latest();
      }

      return ci()->migration->version($version);
    };

    $result = $migrate();

    if ($result === FALSE) {
      show_error(ci()->migration->error_string());
    }

    $current = ci()->db->get($_migrationsTable)->result()[0]->version;

    echo "\n";

    if ($old == $current) {
      echo "Nothing to migrate. \n";
    } else {
      $migrated   = [];
      $index      = 0;
      $migrations = $old < $current ? $migrations : array_reverse($migrations, true);
      $ascendent  = $old < $current;

      foreach ($migrations as $name => $path) {
        if ($ascendent) {
          if ($current >=  $name) {
            echo 'MIGRATED: ' . basename($migrations[$name]) . "\n";
          }
        } else {
          if ($current <= $name) {
            echo 'REVERSED: ' . basename($migrations[$name]) . "\n";
          }
        }
      }
    }
  }
}
