<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Xtend\Test;

class UnittestSample
{

  private $message;

  public function __construct(string $message)
  {
    $this->message = $message;
  }

  public function getMessage(): string
  {
    return $this->message;
  }

  public function setMessage(string $message): void
  {
    $this->message = $message;
  }
}
