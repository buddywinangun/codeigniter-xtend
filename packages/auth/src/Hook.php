<?php

namespace CodeigniterXtend\Auth;

use CodeigniterXtend\Route\Utils;
use CodeigniterXtend\Route\RouteBuilder as Route;
use CodeigniterXtend\Auth\Dispatcher as AuthDispatcher;
use CodeigniterXtend\Route\Debug;
use DebugBar\DataCollector\MessagesCollector;

class Hook
{
  public static function getHooks($hooks = [], $config = null)
  {
    if (empty($config)) {
      $config = [
        'modules' => [],
      ];
    }

    $hooks['pre_system'][] = function () use ($config) {
      self::preSystemHook($config);
    };

    $hooks['post_controller_constructor'][] = function () {
      self::postControllerConstructorHook();
    };

    $hooks['post_controller'][] = function () {
      self::postControllerHook();
    };

    return $hooks;
  }

  /**
   * "pre_system" hook
   *
   * @param array $config
   *
   * @return void
   */
  private static function preSystemHook($config)
  {
    define('CI_DIR', __DIR__);

    $isAjax =  isset($_SERVER['HTTP_X_REQUESTED_WITH'])
      && (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    $isCli  =  is_cli();

    Utils::rcopy(__DIR__ . '/Resources', APPPATH);

    require_once __DIR__ . '/Facades/Auth.php';

    Route::middleware(new AuthDispatcher());

    // Debug module
    if (ENVIRONMENT != 'production' && !$isCli && !$isAjax && in_array('debug', $config['modules'])) {
      Debug::init();
      Debug::addCollector(new MessagesCollector('auth'));
    }
  }

  /**
   * "post_controller" hook
   *
   * @return void
   */
  private static function postControllerConstructorHook()
  {
    if (!is_cli()) {
      ci()->load->library('session');
      ci()->load->config('auth');
      Auth::init();
      Auth::user(true);

      Debug::log('>>> CURRENT AUTH SESSION:', 'info', 'auth');
      Debug::log(Auth::session(), 'info', 'auth');
      Debug::log('>>> CURRENT USER:', 'info', 'auth');
      Debug::log(Auth::user(), 'info', 'auth');
    }
  }

  /**
   * "post_controller" hook
   *
   * @return void
   */
  private static function postControllerHook()
  {
    if (!is_cli()) {
      Auth::session('validated', false);
    }
  }
}
