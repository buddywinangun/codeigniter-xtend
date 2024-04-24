<?php

namespace CodeigniterXtend\Auth\Middleware;

use CodeigniterXtend\Auth\Auth;
use CodeigniterXtend\Auth\ControllerInterface as AuthControllerInterface;
use CodeigniterXtend\Route\MiddlewareInterface;

/**
 * Basic security layer for routing that requires user authentication.
 */
class BaseMiddleware implements MiddlewareInterface
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

        if( config_item('auth_enable_remember_me') === true )
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