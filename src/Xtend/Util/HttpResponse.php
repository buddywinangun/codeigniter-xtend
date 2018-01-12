<?php

namespace Xtend\Util;

final class HttpResponse
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
}
