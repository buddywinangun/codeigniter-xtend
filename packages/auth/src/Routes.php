<?php

namespace CodeigniterXtend\Auth;

use CodeigniterXtend\Route\RouteBuilder as Route;

/**
 * User class
 */
class Routes
{
  /**
   * Sets the Auth default routing
   *
   * @param boolean $secureLogout Disable logout with GET requests
   *
   * @return void
   */
  public static function getRoutes($secureLogout = true)
  {
    Route::match(['get', 'post'], 'login', 'AuthController@login')->name('login');

    Route::match($secureLogout === true ? ['post'] : ['get', 'post'], 'logout', 'AuthController@logout')->name('logout');

    Route::get('email_verification/{token}', 'AuthController@emailVerification')->name('email_verification');

    Route::match(['get', 'post'], 'signup', 'AuthController@signup')->name('signup');

    Route::match(['get', 'post'], 'confirm_password', 'AuthController@confirmPassword')->name('confirm_password');

    Route::group('password-reset', function () {
      Route::match(['get', 'post'], '/', 'AuthController@passwordReset')->name('password_reset');
      Route::match(['get', 'post'], '{token}', 'AuthController@passwordResetForm')->name('password_reset_form');
    });
  }
}