<?php

class sfSympalDiff
{
  protected
    $_from,
    $_to;

  public function __construct($from, $to)
  {
    $this->_from = $from;
    $this->_to = $to;
  }

  protected function _generateDiffHtml($from, $to)
  {
    set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/../vendor/text_diff');

    $diff = @new Text_Diff('auto', array(explode("\n", $from), explode("\n", $to)));
    $renderer = @new Text_Diff_Renderer_inline();
    $diff = (string) @$renderer->render($diff);

    return '<pre>'.$diff.'</pre>';
  }

  public static function diff($from, $to)
  {
    $diff = new self($from, $to);
    return $diff;
  }

  public function __toString()
  {
    return (string) $this->_generateDiffHtml($this->_from, $this->_to);
  }
}