<?php

namespace CodeigniterXtend\Route;

interface MiddlewareInterface
{
  /**
   * Middleware entry point
   *
   * @param mixed $args Middleware arguments
   *
   * @return mixed
   */
  public function run($args);
}
