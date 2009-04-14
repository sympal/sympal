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

    $diff = str_replace("<ins>\n", '<ins>', $diff);
    $diff = str_replace("<del>\n", '<del>', $diff);

    $e = explode("\n", $diff);
    unset($e[count($e) - 1]);

    $table = '<table>';
    foreach ($e as $key => $value)
    {
      $line = $key + 1;
      $table .= '<tr>';
      $table .= '<th>'.$line.'</th>';
      $table .= '<td>'.$value.'</td>';
      $table .= '</tr>';
    }
    $table .= '</table>';

    return $table;
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