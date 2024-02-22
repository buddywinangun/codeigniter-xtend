<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Core;

abstract class AppException extends Exception
{
  public function __construct(string $message, int $code = 0, Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }

  // custom string representation of object
  public function __toString(): string
  {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n"; //edit this to your need
  }
}
