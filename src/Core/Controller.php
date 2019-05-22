<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * Adapted from the CodeIgniter Core Classes
 * @link		https://codeigniter.com/userguide3/general/controllers.html
 *
 * Description:
 * This library extends the CI_Controller class.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Xtend\Core;

use Xtend\HTTP\Response;
use Xtend\Util\Loader;

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

  protected $model;
  protected $library;
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

    Loader::model($this->model);
    Loader::library($this->library);
    $this->httpResponse = new Response();
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
}
