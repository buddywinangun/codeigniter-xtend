<?php

namespace CodeigniterXtend\Auth;

/**
 * Describes which methods must implement all the classes used as a representation
 * of an authenticated user in CodeIgniter
 */
interface UserInterface
{
    /**
     * @param mixed $entity       User data entity, usually an object
     * @param array $roles        User roles
     * @param array $permissions  User permissions
     */
    public function __construct($entity, $roles, $permissions);

    /**
     * Gets the user entity
     *
     * @return mixed
     */
    public function getEntity();

    /**
     * Gets the username from the user entity
     *
     * @return string
     */
    public function getUsername();

    /**
     * Gets the user roles
     *
     * @return array
     */
    public function getRoles();

    /**
     * Gets the user permissions
     *
     * @return array
     */
    public function getPermissions();
}

