<?php

namespace CodeigniterXtend\Auth;

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
   * Loads an User class and his related User Provider class
   *
   * @param  string $userClass User class name
   *
   * @return \CodeigniterXtend\Auth\UserProviderInterface
   */
  public static function loadUserProvider($userProviderClass)
  {
    if (isset(self::$providers[$userProviderClass])) {
      return self::$providers[$userProviderClass];
    }

    if (substr($userProviderClass, -8) != 'Provider') {
      $userProviderClass .= 'Provider';
    }

    if (file_exists(APPPATH . '/security/providers/' . $userProviderClass . '.php')) {
      require_once APPPATH . '/security/providers/' . $userProviderClass . '.php';

      if (!class_exists($userProviderClass)) {
        show_error('User provider class "' . $userProviderClass . '" not found');
      }
    } else {
      show_error('Unable to find "' . $userProviderClass . '" User Provider class file');
    }

    $userProviderInstance = new $userProviderClass();
    $userClass = $userProviderInstance->getUserClass();

    if (!file_exists(APPPATH . '/security/providers/' . $userClass . '.php')) {
      show_error('Unable to find "' . $userClass . '" attached User class file');
    }

    require_once APPPATH . '/security/providers/' . $userClass . '.php';

    if (!class_exists($userClass)) {
      show_error('User attached class "' . $userClass . '" not found');
    }

    self::$providers[$userClass] = $userProviderInstance;
    return $userProviderInstance;
  }

  /**
   * Checks if the current user is guest (not authenticated)
   *
   * @return bool
   */
  public static function isGuest()
  {
    return self::user() === null;
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

  /**
   * Gets the current authentication messages (useful for validations, etc)
   *
   * @return array
   */
  public static function messages()
  {
    $messages = ci()->session->flashdata('_auth_messages');

    return !empty($messages)
      ? $messages
      : [];
  }
}
