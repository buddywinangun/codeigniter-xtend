<?php

namespace Xtend\Event;

use function \Xtend\Event\do_action;

/**
 * Hooks Class
 *
 * Adapted from the CodeIgniter Core Classes
 * @link		https://codeigniter.com/userguide3/general/routing.html
 *
 * Description:
 * This library extends the CI_Hooks class
 * and adds features allowing use of events.
 */
abstract class Hooks extends \CI_Hooks
{
	/**
	 * Class constructor
	 *
	 * @return 	void
	 */
	public function __construct()
	{
		parent::__construct();

		log_message('info', 'MY_Hooks Class Initialized');
	}

	// ------------------------------------------------------------------------

	/**
	 * call_hook
	 *
	 * Calls a particular hook. Added for app in order to execute action
	 * using the package class.
	 *
	 * @access 	public
	 * @param 	string 	$which 	The hook's name.
	 * @return 	bool 	true on success, else false.
	 */
	public function call_hook($which = '')
	{
		// We do any action first.
		do_action($which);

		// Then we let the parent do the rest.
		return parent::call_hook($which);
	}
}