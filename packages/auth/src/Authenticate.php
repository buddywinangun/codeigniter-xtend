<?php

/**
 * This file is part of Codeigniter Auth.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Xtend\HTTP\Response;

class Authenticate
{
  public static function hook()
  {
    // load ci instance
    $ci = &get_instance();
    $httpResponse = new Response();

    // check if the public variable is_token_verify_hookable in the controller is defined and set to TRUE
    if (!isset($ci->is_token_verify_hookable) or !$ci->is_token_verify_hookable) {
      return;
    }

    $authenticationHeader = $ci->input->get_request_header('Authorization');

    try {
      $encodedToken = self::getJWTFromRequest($authenticationHeader);
      $ci->token_data = self::validateJWTFromRequest($encodedToken);
    } catch (\Exception $e) {
      $httpResponse->setCorsHeader('*');
      $httpResponse->error($e->getMessage(), 200, true);
    }
  }

  public static function getJWTFromRequest($authenticationHeader): string
  {
    if (is_null($authenticationHeader)) { //JWT is absent
      throw new \Exception('Missing or invalid JWT in request');
    }
    //JWT is sent from client in the format Bearer XXXXXXXXX
    return explode(' ', $authenticationHeader)[1];
  }

  public static function validateJWTFromRequest(string $token)
  {
    // load ci instance
    $ci = &get_instance();
    $key = $ci->config->item('encryption_key'); //$ci->config->item('JWT_SECRET_KEY');
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    return $decoded;
  }

  public static function getSignedJWTForUser(string $email, $time = 3600)
  {
    // load ci instance
    $ci = &get_instance();
    $issuedAtTime = time();

    #JWT_TIME_TO_LIVE indicates the validity period of a signed JWT (in milliseconds)
    $tokenTimeToLive = $time; //$ci->config->item('JWT_TIME_TO_LIVE');

    #JWT_SECRET_KEY key is the secret key used by the application to sign JWTS. Pick a stronger one for production.
    $key = $ci->config->item('encryption_key'); //$ci->config->item('JWT_SECRET_KEY');

    $payload = [
      'email' => $email,
      'iat' => $issuedAtTime,
      'exp' => $issuedAtTime + $tokenTimeToLive,
    ];

    $jwt = JWT::encode($payload, $key, 'HS256');
    return $jwt;
  }
}
