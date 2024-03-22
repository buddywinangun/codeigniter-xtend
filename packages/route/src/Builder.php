<?php

/**
 * This file is part of Codeigniter Xtend Route.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Route;

use CodeigniterXtend\Route\Route;

class Builder
{
	private $pre_from;
	private $from;
	private $to;
	private $options;
	private $nested;
	private $prefix;

	private $optional_parameters = array();
	private $parameters = array();
	private $optional_objects = array();

	/**
	 * @param string $from
	 * @param boolean $nested
	 */
	function __construct($from, $to, $options, $nested)
	{
		$this->pre_from = $from;
		$this->to = $to;
		$this->options = $options;
		$this->nested = $nested;

		$this->prefix = Route::get_prefix();

		$this->pre_from = $this->prefix . $this->pre_from;

		//check for route parameters
		$this->_check_parameters();
	}

	public function make()
	{
		// Due to bug stated in https://github.com/Patroklo/codeigniter-static-laravel-routes/issues/11
		// we will make a cleanup of the parameters not used in the optional cases
		$parameter_positions = array_flip(array_keys($this->parameters));

		//first of all, we check for optional parameters. If they exist,
		//we will make another route without the optional parameter
		foreach ($this->optional_parameters as $parameter) {
			$from = $this->pre_from;
			$to = $this->to;

			//we get rid of prefix in case it exists
			if (!empty($this->prefix) && strpos($from, $this->prefix) === 0) {
				$from = substr($from, strlen($this->prefix));
			};


			foreach ($parameter as $p) {

				// Create the new $from without some of the optional routes
				$from = str_replace('/{' . $p . '}', '', $from);

				// Create the new $to without some of the optional destiny routes
				if (array_key_exists($p, $parameter_positions)) {
					$to = str_replace('/$' . ($parameter_positions[$p] + 1), '', $to);
				}
			}

			// Save the optional routes in case we will need them for where callings
			$this->optional_objects[] = Route::createRoute($from, $to, $this->options, $this->nested);
		}

		// Do we have a nested function?
		if ($this->nested && is_callable($this->nested)) {
			$name = rtrim($this->pre_from, '/');
			if (array_key_exists('subdomain', $this->options)) {
				Route::prefix(array('name' => $name, 'subdomain' => $this->options['subdomain']), $this->nested);
			} else {
				Route::prefix($name, $this->nested);
			}
		}
	}

	private function _check_parameters()
	{
		preg_match_all('/\{(.+?)\}/', $this->pre_from, $matches);

		if (array_key_exists(1, $matches) && !empty($matches[1])) {
			//we make the parameters that the route could have and, if
			//it's an optional parameter, we add it into the optional parameters array
			//to make later the new route without it

			$uris = array();
			foreach ($matches[1] as $parameter) {
				if (substr($parameter, -1) == '?') {
					$new_key = str_replace('?', '', $parameter);

					//$this->optional_parameters[$parameter] = $new_key;
					$uris[] = $new_key;

					$this->pre_from = str_replace('{' . $parameter . '}', '{' . $new_key . '}', $this->pre_from);

					$parameter = $new_key;
				}

				$this->parameters[$parameter] = array('value' => NULL);
			}

			if (!empty($uris)) {
				$num = count($uris);

				//The total number of possible combinations
				$total = pow(2, $num);

				//Loop through each possible combination
				for ($i = 0; $i < $total; $i++) {

					$sub_list = array();

					for ($j = 0; $j < $num; $j++) {
						//Is bit $j set in $i?
						if (pow(2, $j) & $i) {
							$sub_list[] = $uris[$j];
						}
					}

					$this->optional_parameters[] = $sub_list;
				}

				if (!empty($this->optional_parameters)) {
					array_shift($this->optional_parameters);
				}
			}


			$uri_list = explode('/', $this->pre_from);

			foreach ($uri_list as $key => $uri) {
				$new_uri = str_replace(array('{', '}'), '', $uri);

				if (array_key_exists($new_uri, $this->parameters)) {
					$this->parameters[$new_uri]['uri'] = ($key + 1);
				}
			}
		}
	}

	public function get_from()
	{
		//check if parameters of the from have a regex pattern to put in their place
		//if not, they will be a (:any)

		if (is_null($this->from)) {
			$pattern_list = array();
			$substitution_list = array();
			$named_route_substitution_list = array();

			$pattern_num = 1;

			foreach ($this->parameters as $parameter => $data) {
				$value = $data['value'];

				//if there is a question mark in the parameter
				//we will add a scape \ for the regex
				$pattern_list[] = '/\{' . $parameter . '\}/';

				//if parameter is null will check if there is a global parameter, if not,
				//we will put an (:any)
				if (is_null($value)) {
					$pattern_value = Route::get_pattern($parameter);

					if (!is_null($pattern_value)) {
						if ($pattern_value[0] != '(' && $pattern_value[strlen($pattern_value) - 1] != ')') {
							$pattern_value = '(' . $pattern_value . ')';
						}

						$substitution_list[] = $pattern_value;
					} else {
						$substitution_list[] = '(:any)';
					}
				} else {
					if ($value[0] != '(' && $value[strlen($value) - 1] != ')') {
						$value = '(' . $value . ')';
					}

					$substitution_list[] = $value;
				}

				$named_route_substitution_list[] = '\$' . $pattern_num;
				$pattern_num += 1;
			}

			// check for named subdomains
			if (array_key_exists('subdomain', $this->options)) {
				$i = preg_match('/^\{(.+)\}$/', $this->options['subdomain']);

				if ($i > 0) {
					preg_match('/^\{(.+)\}$/', $this->options['subdomain'], $check);

					$subdomain = $check[1];

					if (!array_key_exists($subdomain, $this->parameters)) {
						$pattern_value = Route::get_pattern($subdomain);

						if (!is_null($pattern_value)) {
							$this->options['subdomain'] = $pattern_value;
						} else {
							$this->options['subdomain'] = '(:any)';
						}
					} else {
						$value = $this->parameters[$subdomain]['value'];
						$this->options['subdomain'] = $value;
					}
				} else {
					$this->options['checked_subdomain'] = $this->options['subdomain'];
					unset($this->options['subdomain']);
				}
			}

			// make substitutions to make codeigniter comprensible routes
			$this->from = preg_replace($pattern_list, $substitution_list, $this->pre_from);

			// make substitutions in case there is a named route
			// Are we saving the name for this one?
			if (isset($this->options['as']) && !empty($this->options['as'])) {
				$named_route = preg_replace($pattern_list, $named_route_substitution_list, $this->pre_from);

				Route::set_name($this->options['as'], $named_route);
			}
		}
		return $this->from;
	}

	public function get_to()
	{
		return $this->to;
	}

	public function where($parameter, $pattern = NULL)
	{
		if (is_array($parameter)) {
			foreach ($parameter as $key => $value) {
				$this->where($key, $value);
			}
		} else {
			//calling all the optional routes to send them the where
			foreach ($this->optional_objects as $ob) {
				$ob->where($parameter, $pattern);
			}

			$this->parameters[$parameter]['value'] = $pattern;
		}

		return $this;
	}

	public function get_parameters()
	{
		$return_parameters = array();

		foreach ($this->parameters as $key => $parameter) {
			if (array_key_exists('uri', $parameter)) {
				$return_parameters[$key] = $parameter['uri'];
			}
		}

		return $return_parameters;
	}

	public function get_filters($type = 'before')
	{
		if (isset($this->options[$type]) && !empty($this->options[$type])) {
			$filters = $this->options[$type];

			if (is_string($filters)) {
				$filters = explode('|', $filters);
			}

			if (!is_array($filters)) {
				$filters = array($filters);
			}

			return $filters;
		}

		return array();
	}

	public function get_options($option = NULL)
	{
		if ($option == NULL) {
			return $this->options;
		} else {
			if (array_key_exists($option, $this->options)) {
				return $this->options[$option];
			}
		}

		return FALSE;
	}
}