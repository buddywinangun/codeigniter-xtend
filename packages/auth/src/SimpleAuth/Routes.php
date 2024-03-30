<?php

namespace CodeigniterXtend\Auth\SimpleAuth;

use CodeigniterXtend\Route\RouteBuilder as Route;

/**
 * SimpleAuth User class
 */
class Routes
{
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
}