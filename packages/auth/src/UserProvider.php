<?php

namespace CodeigniterXtend\Auth;

use CodeigniterXtend\Auth\UserInterface;
use CodeigniterXtend\Auth\UserProviderInterface;
use CodeigniterXtend\Auth\Exception\UserNotFoundException;
use CodeigniterXtend\Auth\Exception\InactiveUserException;
use CodeigniterXtend\Auth\Exception\UnverifiedUserException;

/**
 * user provider
 */
class UserProvider implements UserProviderInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\UserProviderInterface::getUserClass()
     */
    public function getUserClass()
    {
        return 'User';
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\UserProviderInterface::loadUserByUsername()
     */
    final public function loadUserByUsername($username, $password = null)
    {
        ci()->load->database();

        $user = ci()->db->get_where(
              config_item('auth_users_table'),
            [ config_item('auth_username_col') => $username ]
        )->result();

        if(empty($user) || ($password !== null && !$this->verifyPassword($password, $user[0]->{config_item('auth_password_col')})))
        {
            throw new UserNotFoundException();
        }

        $userClass = $this->getUserClass();

        $roles = [ $user[0]->{config_item('auth_role_col')} ];

        $permissions = [];

        if(config_item('auth_enable_acl') === true)
        {
            $databaseUserPermissions = ci()->db->get_where(
                  config_item('auth_users_acl_table'),
                [ 'user_id' => $user[0]->id ]
            )->result();

            if(!empty($databaseUserPermissions))
            {
                foreach($databaseUserPermissions as $permission)
                {
                    $permissionName = '';
                    Library::walkUpPermission($permission->category_id, $permissionName);
                    $permissions[$permission->category_id] = $permissionName;
                }
            }
        }

        return new $userClass($user[0], $roles, $permissions);
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\UserProviderInterface::checkUserIsActive()
     */
    final public function checkUserIsActive(UserInterface $user)
    {
        if($user->getEntity()->{config_item('auth_active_col')} == 0)
        {
            throw new InactiveUserException();
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\UserProviderInterface::checkUserIsVerified()
     */
    final public function checkUserIsVerified(UserInterface $user)
    {
        $enableCheck = config_item('auth_enable_email_verification')  === TRUE &&
                       config_item('auth_enforce_email_verification') === TRUE;

        if(!$enableCheck)
        {
            return;
        }

        if($user->getEntity()->{config_item('auth_verified_col')} == 0)
        {
            throw new UnverifiedUserException();
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\UserProviderInterface::hashPassword()
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\UserProviderInterface::verifyPassword()
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}