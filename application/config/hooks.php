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

$hook['pre_system'][] = [new Xtend\Autoloader\Autoloader, 'register'];

$hook['pre_controller_constructor'][] = [new Xtend\Package\Package, 'init'];
$hook['pre_controller_constructor'][] = [new Xtend\View\Template, 'init'];