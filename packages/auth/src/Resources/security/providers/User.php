<?php

/**
 * SimpleAuth default User Instance
 *
 * This User instance returns the User object obtained by the SimpleAuth User Provider
 *
 * The behavior can be configured by editing the application/config/auth.php file or
 * overriding the inherited methods.
 *
 */

use CodeigniterXtend\Auth\SimpleAuth\User as SimpleAuthUser;

class User extends SimpleAuthUser
{

}
