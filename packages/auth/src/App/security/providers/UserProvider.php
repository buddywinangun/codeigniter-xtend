<?php

/**
 * User Provider
 *
 * This User Provider uses the native functionalities of CodeIgniter to obtain users from
 * a database.
 *
 * The behavior can be configured by editing the application/config/auth.php file or
 * overriding the inherited methods.
 *
 * By default, the password hashing uses the PHP native functions password_hash /
 * password_verify,  but this can be changed by overriding the hashPassword()
 * and verifyPassword() methods of the parent class.
 *
 */

use CodeigniterXtend\Auth\UserProvider as BaseUserProvider;

class UserProvider extends BaseUserProvider
{

}
