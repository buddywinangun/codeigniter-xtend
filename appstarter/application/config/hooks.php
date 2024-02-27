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

$hook['pre_system'][] = [new CodeigniterXtend\Framework\Autoloader\Autoloader, 'register'];

$hook['pre_controller_constructor'][] = [new CodeigniterXtend\Framework\Package\Package, 'init'];
$hook['pre_controller_constructor'][] = [new CodeigniterXtend\Framework\View\Template, 'init'];