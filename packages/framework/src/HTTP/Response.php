<?php

/**
 * This file is part of Codeigniter Xtend Framework.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeigniterXtend\Framework\HTTP;

final class Response
{
  private $data = [];
  private $status;
  private $CI;

  public function __construct()
  {
    $this->CI = &\get_instance();
  }

  /**
   * Set data
   *
   * @param  mixed $key
   * @param  mixed $value
   * @return object
   */
  public function set($key, $value = null)
  {
    if (func_num_args() === 2) {
      if (!is_array($this->data)) {
        $this->data = [];
      }
      $this->data[$key] = $value;
    } else if (func_num_args() === 1) {
      $this->data = $key;
    }
    return $this;
  }

  /**
   * Response JSON
   *
   * @throws LogicException
   * @param  bool $forceObject
   * @param  bool $prettyrint
   * @return void
   */
  public function json(bool $forceObject = false, bool $prettyrint = false)
  {
    $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if ($forceObject) {
      $options = $options | JSON_FORCE_OBJECT;
    }
    if ($prettyrint) {
      $options = $options | JSON_PRETTY_PRINT;
    }
    $json = json_encode($this->data, $options);
    if ($json === false) {
      throw new \LogicException(sprintf('Failed to parse json string \'%s\', error: \'%s\'', $this->data, json_last_error_msg()));
    }
    ob_clean();
    // $this->setCorsHeader('*');
    $this->CI->output
      ->set_status_header($this->status ?? 200)
      ->set_content_type('application/json', 'UTF-8')
      ->set_output($json);
  }

  /**
   * Response HTML.
   */
  public function html(string $html)
  {
    // $this->setCorsHeader('*');
    $this->CI->output
      ->set_content_type('text/html', 'UTF-8')
      ->set_output($html);
  }

  /**
   * Response template.
   */
  public function view(string $path)
  {
    static $template;
    $template = $template ?? new \CodeigniterXtend\Framework\View\Template();
    self::html($template->load($path, $this->data));
  }

  /**
   * Response error.
   */
  public function error(string $message, int $status = 500, bool $forceJsonResponse = false)
  {
    if ($forceJsonResponse || $this->CI->input->is_ajax_request()) {
      $json = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      if ($json === false)
        throw new \LogicException(sprintf('Failed to parse json string \'%s\', error: \'%s\'', $this->data, json_last_error_msg()));

      ob_clean();
      // $this->setCorsHeader('*');
      $this->CI->output
        ->set_header('Cache-Control: no-cache, must-revalidate')
        ->set_status_header($status)
        ->set_content_type('application/json', 'UTF-8')
        ->set_output($json);
    } else {
      show_error($message, $status);
    }
  }

  /**
   * Sets the CORS header
   *
   * eg.
   *   // Allow all origins
   *   $httpResponse->setCorsHeader('*');
   *
   *   // Allow a specific single origin
   *   $httpResponse->setCorsHeader('http://www.example.jp');
   *
   *   // Allow specific multiple origins
   *   $httpResponse->setCorsHeader('http://www.example.jp https://www.example.jp http://sub.example.jp');
   *
   * @param string $origin
   * @return void
   */
  public function setCorsHeader(string $origin) {
    if ($origin === '*') {
      if (!empty($_SERVER['HTTP_ORIGIN'])) {
        $origin = $_SERVER['HTTP_ORIGIN'];
      } else if (!empty($_SERVER['HTTP_REFERER'])) {
        $origin = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME) . '://' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
      }
    }
    // Logger::debug('$origin=', $origin);
    $this->CI->output
      ->set_header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization')
      ->set_header('Access-Control-Allow-Methods: GET, POST, OPTIONS')
      ->set_header('Access-Control-Allow-Credentials: true')
      ->set_header('Access-Control-Allow-Origin: ' . $origin);
  }
}
