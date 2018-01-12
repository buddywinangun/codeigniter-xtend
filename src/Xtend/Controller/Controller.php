<?php

namespace Xtend\Controller;

use Xtend\Util\HttpResponse;
use Xtend\Util\Loader;

abstract class Controller extends \CI_Controller
{
  protected $model;
  protected $library;
  protected $httpResponse;

  public function __construct()
  {
    parent::__construct();
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
