<?php

namespace Xtend\Core;

use Xtend\Util\HttpResponse;
use Xtend\Util\Loader;

/**
 * Controller Class
 *
 * Adapted from the CodeIgniter Core Classes
 * @link		https://codeigniter.com/userguide3/general/controllers.html
 *
 * Description:
 * This library extends the CI_Controller class.
 */
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
    $this->httpResponse = new HttpResponse();
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
