<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/

$hook['pre_controller_constructor'][] = [new Xtend\Util\Package, 'init'];
$hook['pre_controller_constructor'][] = [new Xtend\Util\Template, 'init'];

/*
| -------------------------------------------------------------------
|  Auto-load All Classes with PSR-4
| -------------------------------------------------------------------
| After registering \Xtend\Composer\Psr4Autoload, you could auto-load every
| classes in the whole Codeigniter application with `app` PSR-4
| prefix by default.
 */
$hook['pre_system'][] = [new Xtend\Composer\Psr4Autoload, 'register'];