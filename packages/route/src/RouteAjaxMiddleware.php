<?php

namespace CodeigniterXtend\Route;

class RouteAjaxMiddleware implements MiddlewareInterface
{
  /**
   * {@inheritDoc}
   *
   * @see \CodeigniterXtend\Route\MiddlewareInterface::run()
   */
  public function run($args = [])
  {
    if (!get_instance()->input->is_ajax_request()) {
      trigger_404();
    }
  }
}
