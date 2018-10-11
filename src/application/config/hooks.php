<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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

$hook['pre_system'][] = array(
	'class'    => 'AppAutoloadHook',
	'filename' => 'AppAutoloadHook.php',
	'function' => 'init',
	'filepath' => 'hooks',
);

$hook['pre_controller_constructor'][] = array(
	'class'    => 'AppPackageHook',
	'filename' => 'AppPackageHook.php',
	'function' => 'init',
	'filepath' => 'hooks',
);