<?php

/**
 * --------------------------------------------------------------------------------------
 *                                General Auth configuration
 * --------------------------------------------------------------------------------------
 */

//
// Basic routing
//

$config['auth_login_route']  = 'login';

$config['auth_logout_route'] = 'logout';

$config['auth_login_route_redirect'] = 'dashboard';

$config['auth_logout_route_redirect'] = 'homepage';

$config['auth_route_auto_redirect'] = [

    # The following routes will redirect to the 'auth_login_route_redirect' if
    # the user is logged in:

    'login',
    'signup',
    'password_reset',
    'password_reset_form'
];

//
// Main login form
//

$config['auth_form_username_field'] = 'email';

$config['auth_form_password_field'] = 'password';

//
// Session & Cookies configuration
//

$config['auth_session_var'] = 'auth';


/**
 * --------------------------------------------------------------------------------------
 *                                Auth configuration
 * --------------------------------------------------------------------------------------
 */

//
// Enable/disable features
//

$config['auth_enable_signup'] = TRUE;

$config['auth_enable_password_reset'] = TRUE;

$config['auth_enable_remember_me'] = TRUE;

$config['auth_enable_email_verification'] = TRUE;

$config['auth_enforce_email_verification'] = FALSE;

$config['auth_enable_brute_force_protection'] = TRUE;

$config['auth_enable_acl'] = TRUE ;

//
// Views configuration
//

$config['auth_skin'] = 'default';

$config['auth_assets_dir'] = 'assets/auth';

//
// ACL Configuration
//

$config['auth_acl_map'] = [

    // If you are worried about performance, you can fill this array with $key => $value
    // pairs of known permissions/permissions groups ids, reducing drastically the
    // amount of executed database queries
    //
    // Example
    //    [ permission full name ]       [ permission id ]
    //    'general.blog.read'        =>         1
    //    'general.blog.edit'        =>         2
    //    'general.blog.delete'      =>         3
];

//
// Email configuration
//

$config['auth_email_configuration'] = null;

$config['auth_email_address'] = 'noreply@example.com';

$config['auth_email_name'] = 'Example';

$config['auth_email_verification_message'] = NULL;

$config['auth_password_reset_message'] = NULL;

//
// Remember me configuration
//

$config['auth_remember_me_field'] = 'remember_me';

$config['auth_remember_me_cookie'] = 'remember_me';

//
// Database configuration
//

$config['auth_user_provider'] = 'User';

$config['auth_users_table']  = 'users';

$config['auth_users_email_verification_table']  = 'email_verifications';

$config['auth_password_resets_table']  = 'password_resets';

$config['auth_login_attempts_table']  = 'login_attempts';

$config['auth_users_acl_table']  = 'user_permissions';

$config['auth_users_acl_categories_table']  = 'user_permissions_categories';

$config['auth_id_col'] = 'id';

$config['auth_username_col'] = 'email';

$config['auth_email_col']  = 'email';

$config['auth_email_first_name_col'] = 'first_name';

$config['auth_password_col'] = 'password';

$config['auth_role_col'] = 'role';

$config['auth_active_col'] = 'active';

$config['auth_verified_col'] = 'verified';

$config['auth_remember_me_col'] = 'remember_token';
