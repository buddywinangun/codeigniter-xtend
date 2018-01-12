<?php

namespace Xtend\Library;

class SessionDatabaseDriver extends \CI_Session_database_driver
{
  public function __construct(&$params)
  {
    parent::__construct($params);
  }
}
