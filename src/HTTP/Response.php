<?php

namespace Xtend\HTTP;

final class Response
{
  private $data = [];
  private $CI;

  public function __construct()
  {
    $this->CI = &\get_instance();
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
    $template = $template ?? new \Xtend\Util\Template();
    self::html($template->load($path, $this->data));
  }

  /**
   * Response error.
   */
  public function error(string $message, int $status = 500, bool $forceJsonResponse = false)
  {
    if ($forceJsonResponse || $this->CI->input->is_ajax_request()) {
      ob_clean();
      // $this->setCorsHeader('*');
      $json = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      if ($json === false)
        throw new \LogicException(sprintf('Failed to parse json string \'%s\', error: \'%s\'', $this->data, json_last_error_msg()));
      $this->CI->output
        ->set_header('Cache-Control: no-cache, must-revalidate')
        ->set_status_header($status, rawurlencode($message))
        ->set_content_type('application/json', 'UTF-8')
        ->set_output($json);
    } else {
      show_error($message, $status);
    }
  }
}
