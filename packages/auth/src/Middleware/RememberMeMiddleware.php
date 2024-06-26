<?php

namespace CodeigniterXtend\Auth\Middleware;

use CodeigniterXtend\Route\MiddlewareInterface;
use CodeigniterXtend\Auth\Auth;

/**
 * Special 'Remember me' feature
 */
class RememberMeMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \CodeigniterXtend\Route\MiddlewareInterface::run()
     */
    public function run($action = 'store')
    {
        if($action == 'store')
        {
            $this->storeAuthCookie();
        }
        elseif($action == 'restore')
        {
            $this->restoreAuthFromCookie();
        }
        elseif($action == 'destroy')
        {
            $this->destroyAuthCookie();
        }
        else
        {
            show_error('Unknown RememberMeMiddleware "' . $action . '" action');
        }
    }

    private function storeAuthCookie()
    {
        if(ci()->input->post(config_item('auth_remember_me_field')) === null)
        {
            return;
        }

        ci()->load->library('encryption');

        $rememberToken = bin2hex(ci()->encryption->create_key(32));

        ci()->db->update(
            config_item('auth_users_table'),
           [config_item('auth_remember_me_col') => $rememberToken],
           ['id' => Auth::user()->id]
        );

        ci()->input->set_cookie(config_item('auth_remember_me_cookie'), $rememberToken, 1296000); // 15 days
    }

    private function restoreAuthFromCookie()
    {
        if( !Auth::isGuest() || Auth::session('fully_authenticated') === true)
        {
            return;
        }

        ci()->load->database();
        ci()->load->helper('cookie');
        ci()->load->library('encryption');

        $rememberToken = get_cookie(config_item('auth_remember_me_cookie'));

        if( empty($rememberToken) )
        {
            return;
        }

        $storedUserFromToken = ci()->db->get_where(
             config_item('auth_users_table'),
            [config_item('auth_remember_me_col') => $rememberToken]
        )->result();

        if(empty($storedUserFromToken))
        {
            delete_cookie(config_item('auth_remember_me_cookie'));
            return;
        }

        $userProvider = Auth::loadUserProvider(config_item('auth_user_provider'));

        try
        {
            $user = Auth::bypass($storedUserFromToken[0]->{config_item('auth_username_col')}, $userProvider);
                    $userProvider->checkUserIsActive($user);

                    if(
                        config_item('auth_enable_email_verification')  === TRUE &&
                        config_item('auth_enforce_email_verification') === TRUE
                    )
                    {
                        $userProvider->checkUserIsVerified($user);
                    }
        }
        catch(\Exception $e)
        {
            delete_cookie(config_item('auth_remember_me_cookie'));
            return;
        }

        Auth::store($user, ['fully_authenticated' => false]);
    }

    private function destroyAuthCookie()
    {
        ci()->load->helper('cookie');
        delete_cookie(config_item('auth_remember_me_cookie'));
    }
}