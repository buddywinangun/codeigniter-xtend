<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * Adapted from WordPress Hook Class
 * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/plugin.php
 *
 * The API, which allows for creating actions and filters and hooking functions, and methods.
 * The functions or methods will then be run when the action or filter is called.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeigniterXtend\Framework\Events\Hook;

// Defines filters global variables.
global $filter, $actions, $filters, $current_filter;

/**
 * If was have filters that were defined earlier, we make sure to
 * build them so we can use them.
 */
$filter = ($filter)
  ? Hook::build_preinitialized_hooks($filter)
  : array();

// We make sure actions are always array.
isset($actions) or $actions = array();

// We make sure filters are always array.
isset($filters) or $filters = array();

// We make sure current filter are always array.
isset($current_filter) or $current_filter = array();

/**
 * Adds a callback function to a filter hook.
 *
 * @param string   $hook_name     The name of the filter to add the callback to.
 * @param callable $callback      The callback to be run when the filter is applied.
 * @param int      $priority      Optional. Used to specify the order in which the functions
 *                                associated with a particular filter are executed.
 *                                Lower numbers correspond with earlier execution,
 *                                and functions with the same priority are executed
 *                                in the order in which they were added to the filter. Default 10.
 * @param int      $accepted_args Optional. The number of arguments the function accepts. Default 1.
 * @return true Always returns true.
 */
function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
	global $x_filter;

	if ( ! isset( $x_filter[ $hook_name ] ) ) {
		$x_filter[ $hook_name ] = new Hook();
	}

	$x_filter[ $hook_name ]->add_filter( $hook_name, $callback, $priority, $accepted_args );

	return true;
}

/**
 * Calls the callback functions that have been added to a filter hook.
 *
 * @param string $hook_name The name of the filter hook.
 * @param mixed  $value     The value to filter.
 * @param mixed  ...$args   Optional. Additional parameters to pass to the callback functions.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters( $hook_name, $value, ...$args ) {
	global $x_filter, $x_filters, $x_current_filter;

	if ( ! isset( $x_filters[ $hook_name ] ) ) {
		$x_filters[ $hook_name ] = 1;
	} else {
		++$x_filters[ $hook_name ];
	}

	// Do 'all' actions first.
	if ( isset( $x_filter['all'] ) ) {
		$x_current_filter[] = $hook_name;

		$all_args = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		_x_call_all_hook( $all_args );
	}

	if ( ! isset( $x_filter[ $hook_name ] ) ) {
		if ( isset( $x_filter['all'] ) ) {
			array_pop( $x_current_filter );
		}

		return $value;
	}

	if ( ! isset( $x_filter['all'] ) ) {
		$x_current_filter[] = $hook_name;
	}

	// Pass the value to Hook.
	array_unshift( $args, $value );

	$filtered = $x_filter[ $hook_name ]->apply_filters( $value, $args );

	array_pop( $x_current_filter );

	return $filtered;
}

/**
 * Calls the callback functions that have been added to a filter hook, specifying arguments in an array.
 *
 * @param string $hook_name The name of the filter hook.
 * @param array  $args      The arguments supplied to the functions hooked to `$hook_name`.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters_ref_array( $hook_name, $args ) {
	global $x_filter, $x_filters, $x_current_filter;

	if ( ! isset( $x_filters[ $hook_name ] ) ) {
		$x_filters[ $hook_name ] = 1;
	} else {
		++$x_filters[ $hook_name ];
	}

	// Do 'all' actions first.
	if ( isset( $x_filter['all'] ) ) {
		$x_current_filter[] = $hook_name;
		$all_args            = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		_x_call_all_hook( $all_args );
	}

	if ( ! isset( $x_filter[ $hook_name ] ) ) {
		if ( isset( $x_filter['all'] ) ) {
			array_pop( $x_current_filter );
		}

		return $args[0];
	}

	if ( ! isset( $x_filter['all'] ) ) {
		$x_current_filter[] = $hook_name;
	}

	$filtered = $x_filter[ $hook_name ]->apply_filters( $args[0], $args );

	array_pop( $x_current_filter );

	return $filtered;
}

/**
 * Checks if any filter has been registered for a hook.
 *
 * @param string                      $hook_name The name of the filter hook.
 * @param callable|string|array|false $callback  Optional. The callback to check for.
 *                                               This function can be called unconditionally to speculatively check
 *                                               a callback that may or may not exist. Default false.
 * @return bool|int If `$callback` is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority
 *                  of that hook is returned, or false if the function is not attached.
 */
function has_filter( $hook_name, $callback = false ) {
	global $x_filter;

	if ( ! isset( $x_filter[ $hook_name ] ) ) {
		return false;
	}

	return $x_filter[ $hook_name ]->has_filter( $hook_name, $callback );
}

/**
 * Removes a callback function from a filter hook.
 *
 * @param string                $hook_name The filter hook to which the function to be removed is hooked.
 * @param callable|string|array $callback  The callback to be removed from running when the filter is applied.
 *                                         This function can be called unconditionally to speculatively remove
 *                                         a callback that may or may not exist.
 * @param int                   $priority  Optional. The exact priority used when adding the original
 *                                         filter callback. Default 10.
 * @return bool Whether the function existed before it was removed.
 */
function remove_filter( $hook_name, $callback, $priority = 10 ) {
	global $x_filter;

	$r = false;

	if ( isset( $x_filter[ $hook_name ] ) ) {
		$r = $x_filter[ $hook_name ]->remove_filter( $hook_name, $callback, $priority );

		if ( ! $x_filter[ $hook_name ]->callbacks ) {
			unset( $x_filter[ $hook_name ] );
		}
	}

	return $r;
}

/**
 * Removes all of the callback functions from a filter hook.
 *
 * @param string    $hook_name The filter to remove callbacks from.
 * @param int|false $priority  Optional. The priority number to remove them from.
 *                             Default false.
 * @return true Always returns true.
 */
function remove_all_filters( $hook_name, $priority = false ) {
	global $x_filter;

	if ( isset( $x_filter[ $hook_name ] ) ) {
		$x_filter[ $hook_name ]->remove_all_filters( $priority );

		if ( ! $x_filter[ $hook_name ]->has_filters() ) {
			unset( $x_filter[ $hook_name ] );
		}
	}

	return true;
}

/**
 * Retrieves the name of the current filter hook.
 *
 * @return string Hook name of the current filter.
 */
function current_filter() {
	global $x_current_filter;

	return end( $x_current_filter );
}

/**
 * Returns whether or not a filter hook is currently being processed.
 *
 * @param string|null $hook_name Optional. Filter hook to check. Defaults to null,
 *                               which checks if any filter is currently being run.
 * @return bool Whether the filter is currently in the stack.
 */
function doing_filter( $hook_name = null ) {
	global $x_current_filter;

	if ( null === $hook_name ) {
		return ! empty( $x_current_filter );
	}

	return in_array( $hook_name, $x_current_filter, true );
}

/**
 * Retrieves the number of times a filter has been applied during the current request.
 *
 * @param string $hook_name The name of the filter hook.
 * @return int The number of times the filter hook has been applied.
 */
function did_filter( $hook_name ) {
	global $x_filters;

	if ( ! isset( $x_filters[ $hook_name ] ) ) {
		return 0;
	}

	return $x_filters[ $hook_name ];
}

/**
 * Adds a callback function to an action hook.
 *
 * @param string   $hook_name       The name of the action to add the callback to.
 * @param callable $callback        The callback to be run when the action is called.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action. Default 10.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true Always returns true.
 */
function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
	return add_filter( $hook_name, $callback, $priority, $accepted_args );
}

/**
 * Calls the callback functions that have been added to an action hook.
 *
 * @global Hook[] $x_filter         Stores all of the filters and actions.
 * @global int[]     $x_actions        Stores the number of times each action was triggered.
 * @global string[]  $x_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the action to be executed.
 * @param mixed  ...$arg    Optional. Additional arguments which are passed on to the
 *                          functions hooked to the action. Default empty.
 */
function do_action( $hook_name, ...$arg ) {
	global $x_filter, $x_actions, $x_current_filter;

	if ( ! isset( $x_actions[ $hook_name ] ) ) {
		$x_actions[ $hook_name ] = 1;
	} else {
		++$x_actions[ $hook_name ];
	}

	// Do 'all' actions first.
	if ( isset( $x_filter['all'] ) ) {
		$x_current_filter[] = $hook_name;
		$all_args            = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		_x_call_all_hook( $all_args );
	}

	if ( ! isset( $x_filter[ $hook_name ] ) ) {
		if ( isset( $x_filter['all'] ) ) {
			array_pop( $x_current_filter );
		}

		return;
	}

	if ( ! isset( $x_filter['all'] ) ) {
		$x_current_filter[] = $hook_name;
	}

	if ( empty( $arg ) ) {
		$arg[] = '';
	} elseif ( is_array( $arg[0] ) && 1 === count( $arg[0] ) && isset( $arg[0][0] ) && is_object( $arg[0][0] ) ) {
		// Backward compatibility for PHP4-style passing of `array( &$this )` as action `$arg`.
		$arg[0] = $arg[0][0];
	}

	$x_filter[ $hook_name ]->do_action( $arg );

	array_pop( $x_current_filter );
}

/**
 * Calls the callback functions that have been added to an action hook, specifying arguments in an array.
 *
 * @global Hook[] $x_filter         Stores all of the filters and actions.
 * @global int[]     $x_actions        Stores the number of times each action was triggered.
 * @global string[]  $x_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the action to be executed.
 * @param array  $args      The arguments supplied to the functions hooked to `$hook_name`.
 */
function do_action_ref_array( $hook_name, $args ) {
	global $x_filter, $x_actions, $x_current_filter;

	if ( ! isset( $x_actions[ $hook_name ] ) ) {
		$x_actions[ $hook_name ] = 1;
	} else {
		++$x_actions[ $hook_name ];
	}

	// Do 'all' actions first.
	if ( isset( $x_filter['all'] ) ) {
		$x_current_filter[] = $hook_name;
		$all_args            = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		_x_call_all_hook( $all_args );
	}

	if ( ! isset( $x_filter[ $hook_name ] ) ) {
		if ( isset( $x_filter['all'] ) ) {
			array_pop( $x_current_filter );
		}

		return;
	}

	if ( ! isset( $x_filter['all'] ) ) {
		$x_current_filter[] = $hook_name;
	}

	$x_filter[ $hook_name ]->do_action( $args );

	array_pop( $x_current_filter );
}

/**
 * Checks if any action has been registered for a hook.
 *
 * @param string                      $hook_name The name of the action hook.
 * @param callable|string|array|false $callback  Optional. The callback to check for.
 *                                               This function can be called unconditionally to speculatively check
 *                                               a callback that may or may not exist. Default false.
 * @return bool|int If `$callback` is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority
 *                  of that hook is returned, or false if the function is not attached.
 */
function has_action( $hook_name, $callback = false ) {
	return has_filter( $hook_name, $callback );
}

/**
 * Removes a callback function from an action hook.
 *
 * @param string                $hook_name The action hook to which the function to be removed is hooked.
 * @param callable|string|array $callback  The name of the function which should be removed.
 *                                         This function can be called unconditionally to speculatively remove
 *                                         a callback that may or may not exist.
 * @param int                   $priority  Optional. The exact priority used when adding the original
 *                                         action callback. Default 10.
 * @return bool Whether the function is removed.
 */
function remove_action( $hook_name, $callback, $priority = 10 ) {
	return remove_filter( $hook_name, $callback, $priority );
}

/**
 * Removes all of the callback functions from an action hook.
 *
 * @param string    $hook_name The action to remove callbacks from.
 * @param int|false $priority  Optional. The priority number to remove them from.
 *                             Default false.
 * @return true Always returns true.
 */
function remove_all_actions( $hook_name, $priority = false ) {
	return remove_all_filters( $hook_name, $priority );
}

/**
 * Retrieves the name of the current action hook.
 *
 * @return string Hook name of the current action.
 */
function current_action() {
	return current_filter();
}

/**
 * Returns whether or not an action hook is currently being processed.
 *
 * @param string|null $hook_name Optional. Action hook to check. Defaults to null,
 *                               which checks if any action is currently being run.
 * @return bool Whether the action is currently in the stack.
 */
function doing_action( $hook_name = null ) {
	return doing_filter( $hook_name );
}

/**
 * Retrieves the number of times an action has been fired during the current request.
 *
 * @param string $hook_name The name of the action hook.
 * @return int The number of times the action hook has been fired.
 */
function did_action( $hook_name ) {
	global $x_actions;

	if ( ! isset( $x_actions[ $hook_name ] ) ) {
		return 0;
	}

	return $x_actions[ $hook_name ];
}

/**
 * Calls the 'all' hook, which will process the functions hooked into it.
 *
 * @access private
 *
 * @global Hook[] $x_filter Stores all of the filters and actions.
 *
 * @param array $args The collected parameters from the hook that was called.
 */
function _x_call_all_hook( $args ) {
	global $x_filter;

	$x_filter['all']->do_all_hook( $args );
}

/**
 * Builds a unique string ID for a hook callback function.
 *
 * @access private
 *
 * @param string                $hook_name Unused. The name of the filter to build ID for.
 * @param callable|string|array $callback  The callback to generate ID for. The callback may
 *                                         or may not exist.
 * @param int                   $priority  Unused. The order in which the functions
 *                                         associated with a particular action are executed.
 * @return string Unique function ID for usage as array key.
 */
function _x_filter_build_unique_id( $hook_name, $callback, $priority ) {
	if ( is_string( $callback ) ) {
		return $callback;
	}

	if ( is_object( $callback ) ) {
		// Closures are currently implemented as objects.
		$callback = array( $callback, '' );
	} else {
		$callback = (array) $callback;
	}

	if ( is_object( $callback[0] ) ) {
		// Object class calling.
		return spl_object_hash( $callback[0] ) . $callback[1];
	} elseif ( is_string( $callback[0] ) ) {
		// Static calling.
		return $callback[0] . '::' . $callback[1];
	}
}