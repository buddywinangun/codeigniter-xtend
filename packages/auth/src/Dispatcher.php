<?php

namespace CodeigniterXtend\Auth;

use CodeigniterXtend\Auth\AuthBaseMiddleware;
use CodeigniterXtend\Auth\Auth;
use CodeigniterXtend\Route\Middleware;
use CodeigniterXtend\Route\MiddlewareInterface;

/**
 * Internal middleware that dispatches the Controller-based authentication
 * when the ControllerInterface is detected in the framework singleton base object
 */
class Dispatcher implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Route\MiddlewareInterface::run()
     */
    public function run($args)
    {
        if(!ci() instanceof ControllerInterface)
        {
            return;
        }

        $authMiddleware = ci()->getMiddleware();

        if(is_string($authMiddleware))
        {
            $authMiddleware = Middleware::load($authMiddleware);
        }

        if(!$authMiddleware instanceof AuthBaseMiddleware)
        {
            show_error('The auth middleware must inherit the CodeigniterXtend\Auth\AuthBaseMiddleware class');
        }

        ci()->middleware->run($authMiddleware,  Auth::loadUserProvider(ci()->getUserProvider()));
    }
}