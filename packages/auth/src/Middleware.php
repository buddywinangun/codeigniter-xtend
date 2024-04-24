<?php

namespace CodeigniterXtend\Auth;

use CodeigniterXtend\Auth\AuthBaseMiddleware;
use CodeigniterXtend\Auth\UserInterface;
use CodeigniterXtend\Auth\Middleware\RememberMeMiddleware;
use CodeigniterXtend\Route\Route;

/**
 * implementation of the Controller-based authentication
 */
class Middleware extends AuthBaseMiddleware
{
    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\AuthBaseMiddleware::preLogin()
     */
    public function preLogin(Route $route)
    {
        if(
            $route->getName()     == config_item('auth_login_route') &&
            $route->requestMethod == 'POST' &&
            config_item('auth_enable_brute_force_protection') === true
        )
        {
            ci()->load->database();

            $loginAttemptCount = ci()->db->where('ip', $_SERVER['REMOTE_ADDR'])
                ->where('created_at >=', date('Y-m-d H:i:s', time() - (60 * 30)) ) // 30 minutes
                ->where('created_at <=', date('Y-m-d H:i:s', time()))
                ->count_all_results(config_item('auth_login_attempts_table'));

            if($loginAttemptCount >= 4)
            {
                ci()->session->set_flashdata('_auth_messages', [ 'danger' =>  'ERR_LOGIN_ATTEMPT_BLOCKED' ]);

                return redirect(route(config_item('auth_login_route')));
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\AuthBaseMiddleware::onLoginSuccess()
     */
    public function onLoginSuccess(UserInterface $user)
    {
        if( config_item('auth_enable_remember_me') === true )
        {
            ci()->middleware->run( new RememberMeMiddleware(), 'store');
        }

        return redirect(
            route_exists(config_item('auth_login_route_redirect'))
                ? route(config_item('auth_login_route_redirect'))
                : base_url()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\AuthBaseMiddleware::onLoginFailed()
     */
    public function onLoginFailed($username)
    {
        ci()->load->database();

        if( config_item('auth_enable_brute_force_protection') === true )
        {
            ci()->db->insert(
                config_item('auth_login_attempts_table'),
                [
                    'username'   => $username,
                    'ip'         => $_SERVER['REMOTE_ADDR']
                ]
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\AuthBaseMiddleware::onLoginInactiveUser()
     */
    public function onLoginInactiveUser(UserInterface $user)
    {
        return;
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\AuthBaseMiddleware::onLoginUnverifiedUser()
     */
    public function onLoginUnverifiedUser(UserInterface $user)
    {
        return;
    }

    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Auth\AuthBaseMiddleware::onLogout()
     */
    public function onLogout()
    {
        ci()->middleware->run( new RememberMeMiddleware(), 'destroy');
    }
}