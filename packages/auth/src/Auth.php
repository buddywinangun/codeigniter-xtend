<?php

namespace CodeigniterXtend\Auth;

use CodeigniterXtend\Route\RouteBuilder as Route;
use CodeigniterXtend\Route\Debug;

class Auth
{
  private static $providers = [];

  /**
   * Returns the current authentication session name
   *
   * @return string
   */
  private static function getSessionName()
  {
    return config_item('auth_session_var') !== null
      ? config_item('auth_session_var')
      : 'auth';
  }

  /**
   * Sets the SimpleAuth default routing
   *
   * @param boolean $secureLogout Disable logout with GET requests
   *
   * @return void
   */
  public static function getRoutes($secureLogout = true)
  {
    Route::match(['get', 'post'], 'login', 'SimpleAuthController@login')->name('login');

    Route::match($secureLogout === true ? ['post'] : ['get', 'post'], 'logout', 'SimpleAuthController@logout')->name('logout');

    Route::get('email_verification/{token}', 'SimpleAuthController@emailVerification')->name('email_verification');

    Route::match(['get', 'post'], 'signup', 'SimpleAuthController@signup')->name('signup');

    Route::match(['get', 'post'], 'confirm_password', 'SimpleAuthController@confirmPassword')->name('confirm_password');

    Route::group('password-reset', function () {
      Route::match(['get', 'post'], '/', 'SimpleAuthController@passwordReset')->name('password_reset');
      Route::match(['get', 'post'], '{token}', 'SimpleAuthController@passwordResetForm')->name('password_reset_form');
    });
  }

  /**
   * Initializes the authentication session
   *
   * @return void
   */
  public static function init()
  {
    if (ci()->session->userdata(self::getSessionName()) === null) {
      ci()->session->set_userdata(self::getSessionName(), [
        'user'        => null,
        'validated'   => false,
        'fully_authenticated' => false
      ]);
    }
  }

  /**
   * Gets the current authenticated user
   *
   * @param  bool $refresh Force user refresh
   *
   * @return mixed
   */
  public static function user($refresh = false)
  {
    $sessionUser = self::session('user');

    if ($sessionUser === NULL) {
      return null;
    }

    $userInstance = null;
    $userProvider = self::loadUserProvider($sessionUser['class']);
    $userClass    = $sessionUser['class'];

    if (self::session('validated') === false || $refresh === true) {
      Debug::log('There is a stored user in session. Attempting to validate...', 'info', 'auth');

      try {
        $userInstance = self::bypass($sessionUser['username'], $userProvider);
      } catch (\Exception $e) {
        $userInstance = null;
        Debug::log('ERROR! User auth validation failed. Role set to "guest"', 'error', 'auth');
      }

      Debug::log('SUCCESS! User validated.', 'info', 'auth');
      self::session('validated', true);
    } else {
      $userInstance = new $userClass((object) $sessionUser['entity'], $sessionUser['roles'], $sessionUser['permissions']);
    }

    return $userInstance;
  }

  /**
   * Gets (or sets) an authentication session variable
   *
   * @param  string  $name
   * @param  mixed   $value
   *
   * @return mixed
   */
  public static function session($name = null, $value = null)
  {
    $authSession  = ci()->session->userdata(self::getSessionName());

    if ($name === null) {
      return $authSession;
    } else {
      if ($value === null) {
        return isset($authSession[$name]) ? $authSession[$name] : null;
      } else {
        $authSession[$name] = $value;
        ci()->session->set_userdata(self::getSessionName(), $authSession);
      }
    }
  }
}
