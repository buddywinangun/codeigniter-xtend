<?php

namespace CodeigniterXtend\Auth;

use CodeigniterXtend\Auth\UserInterface;

/**
 * User class
 */
class User implements UserInterface
{
    /**
     * @var object
     */
    private $user;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @param object $entity
     * @param array  $roles
     * @param array  $permissions
     */
    public function __construct($entity, $roles, $permissions)
    {
        $this->user        = $entity;
        $this->roles       = $roles;
        $this->permissions = $permissions;
    }

    public function __get($name)
    {
        if(isset($this->getEntity()->{$name}))
        {
            return $this->getEntity()->{$name};
        }
    }

    public function getEntity()
    {
        return $this->user;
    }

    public function getUsername()
    {
        return $this->user->email;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }
}