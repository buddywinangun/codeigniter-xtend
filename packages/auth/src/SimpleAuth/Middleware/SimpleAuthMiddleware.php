<?php

namespace CodeigniterXtend\Auth\SimpleAuth\Middleware;

use CodeigniterXtend\Auth\Auth;
use CodeigniterXtend\Route\MiddlewareInterface;
use CodeigniterXtend\Auth\ControllerInterface as AuthControllerInterface;

/**
 * Basic security layer for routing that requires user authentication.
 */
class SimpleAuthMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Route\MiddlewareInterface::run()
     */
    public function run($args)
    {
        if(ci() instanceof AuthControllerInterface)
        {
            return;
        }

        if( config_item('simpleauth_enable_remember_me') === true )
        {
            ci()->middleware->run(new RememberMeMiddleware(), 'restore');
        }

        if( Auth::isGuest() )
        {
            if(ci()->route->getName() != config_item('auth_login_route'))
            {
                redirect( route(config_item('auth_login_route')) );
                exit;
            }
        }
    }
}