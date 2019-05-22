<?php

/**
 * This file is part of Codeigniter Xtend.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Xtend\Security;

class Password
{
  public function checkPasswordSimilarity(string $newPassword, string $oldPassowrd)
  {
    $newPassword = strtolower($newPassword);
    $oldPassowrd = strtolower($oldPassowrd);

    // Check if the first three characters of the new password and the old password are the same.
    if (substr($newPassword, 0, 3) === substr($oldPassowrd, 0, 3))
      return false;

    // Check if the new password and the old password have the same second to third characters.
    if (substr($newPassword, 1, 3) === substr($oldPassowrd, 1, 3))
      return false;

    // Check if the last 3 characters of the new password and the old password are the same.
    if (substr($newPassword, -3) === substr($oldPassowrd, -3))
      return false;
    return true;
  }
}
