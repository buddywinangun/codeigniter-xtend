<?php

/**
 * SimpleAuthMiddleware class
 *
 * This middleware is the most basic layer of security. It is responsible for verifying if
 * the user is authenticated and redirects it to the login screen otherwise, and
 * automatically manages the "Remember me" functionality for you.
 *
 * Probably more authentication logic is necessary, but that is at the discretion of
 * the developer :)
 */

defined('BASEPATH') OR exit('No direct script access allowed');

use CodeigniterXtend\Auth\SimpleAuth\Middleware\SimpleAuthMiddleware as BaseSimpleAuthMiddleware;

class SimpleAuthMiddleware extends BaseSimpleAuthMiddleware
{

}