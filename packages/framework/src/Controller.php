<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * Adapted from the CodeIgniter Core Classes
 * @link https://codeigniter.com/userguide3/general/controllers.html
 *
 * Description:
 * This library extends the CI_Controller class.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Frameworks;

use CodeigniterXtend\Framework\HTTP\Response;
use CodeigniterXtend\Framework\Config\Loader;

abstract class Controller extends \CI_Controller
{
	/**
	 * Holds the redirection URL.
	 * @var string
	 */
	protected $redirect = '';

	/**
	 * Array of data to pass to views.
	 * @var array
	 */
	protected $data = array();

  protected $httpResponse;

  public function __construct()
  {
    parent::__construct();

		// Always hold the redirection URL for eventual use.
		if ($this->input->get_post('next')) {
			$raw = rawurldecode($this->input->get_post('next'));
			$this->session->set_flashdata('redirect', $raw);
		}

		// redirection URL
		$this->redirect = $this->session->flashdata('redirect');

    $this->httpResponse = new Response();
  }

  /**
   * Sets the CORS header.
   * ```php
   * // Allow all origins
   * parent::setCorsHeader('*');
   *
   * // Allow a specific single origin
   * parent::setCorsHeader('http://www.example.jp');
   *
   * // Allow specific multiple origins
   * parent::setCorsHeader('http://www.example.jp https://www.example.jp http://sub.example.jp');
   *
   * // To set the same Access-Control-Allow-Origin for all responses, use the hook point called before the response
   * // core/AppController.php:
   * abstract class AppController extends \X\Controller\Controller {
   *   protected function beforeResponse(string $referer) {
   *     $this->setCorsHeader('*');
   *   }
   * }
   * ```
   */
  protected function setCorsHeader(string $origin = '*') {
    $this->httpResponse->setCorsHeader($origin);
    return $this;
  }

  /**
   * Set response
   *
   * @param  mixed $key
   * @param  mixed $value
   * @return object
   */
  protected function set($key, $value = null) {
    func_num_args() === 1 ? $this->httpResponse->set($key) : $this->httpResponse->set($key, $value);
    return $this;
  }

  /**
   * Response JSON
   *
   * @param  bool $forceObject
   * @param  bool $pretty
   * @return void
   */
  protected function json(bool $forceObject = false, bool $prettyrint = false) {
    $this->beforeResponse($this->getReferer());
    $this->beforeResponseJson($this->getReferer());
    $this->httpResponse->json($forceObject, $prettyrint);
  }

  /**
   * Response template.
   */
  protected function view(string $path)
  {
    $this->beforeResponse($this->getReferer());
    $this->beforeResponseView($this->getReferer());
    $this->httpResponse->view($path);
  }

  /**
   * Response error.
   */
  protected function error(string $message, int $status = 500, bool $forceJsonResponse = false) {
    $this->beforeResponse($this->getReferer());
    $this->httpResponse->error($message, $status, $forceJsonResponse);
  }

  /**
   * Get referrer.
   */
  private function getReferer()
  {
    return !empty($_SERVER['HTTP_REFERER'])
      ? $_SERVER['HTTP_REFERER']
      : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  /**
   * Before response.
   */
  protected function beforeResponse(string $referer)
  {
  }

  /**
   * Before response Template.
   */
  protected function beforeResponseView(string $referer)
  {
  }

  /**
   * Before response JSON
   *
   * @param  string $referer
   * @return void
   */
  protected function beforeResponseJson(string $referer) {}
}
