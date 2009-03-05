<?php
class sfSympalYamlSyntaxHighlighter
{
  protected $_yaml;
  protected $_html;
  protected $_colors = array(
      'top_dashes' => '#CC8865',
      'keys' => '#ffffdd',
      'colon' => '#5598EE',
      'string' => '#9EE665',
      'integer' => '#57AAFF',
      'float' => '#57AAFF',
      'decimal' => '#57AAFF',
      'boolean' => '#57AAFF',
      'array' => '#ffffff',
      'comment' => '#ddd',
  );

  public function __construct($yaml)
  {
    $this->_yaml = $yaml;
  }

  public function getHtml()
  {
    if (!$this->_html)
    {
      $this->_html = $this->_highlightYaml($this->_yaml);
    }
    return $this->_html;
  }

  public static function highlight($yaml)
  {
    $highlighter = new sfSympalYamlSyntaxHighlighter($yaml);
    return $highlighter->getHtml();
  }

  protected function _isYaml($yaml)
  {
    try {
      $array = @sfYaml::load($yaml);
      $e = explode("\n", $yaml);
      return ((!empty($array) && count($array, true) > 1 && count($e) > 1) || trim($e[0]) == '---') ? true : false;
    } catch (Exception $e) {
      return false;
    }
  }

  protected function _highlightYaml($yaml)
  {
    try {
      if ($this->_isYaml($yaml))
      {
        $e = explode("\n", $yaml);
        if (trim($e[0]) == '---')
        {
          unset($e[0]);
        }
        foreach ($e as $key => $line)
        {
          $e[$key] = $this->_highlightLine($line);
        }

        $color = $this->_colors['top_dashes'];
        $yaml = "<span style=\"color: $color;\">---</span>\n" . implode("\n", $e);
        return '<pre><code class="yaml">' . $yaml . '</code></pre>';
      }
    } catch (Exception $e) {}
    return false;
  }

  protected function _highlightLine($line)
  {
    $line = trim($line, "\n");
    $el = explode(':', $line);

    $return = '';

    if (substr(trim($el[0]), 0, 1) == '#')
    {
      return $this->_highlightComment($line);
    }

    if (isset($el[0]) && $el[0])
    {
      $left = $el[0];
      $return = '<span style="color: ' . $this->_colors['keys'] . ';">' . $left . '</span><span style="color: ' . $this->_colors['colon'] . ';">:</span>';
      if (isset($el[1]))
      {
        $value = isset($el[1]) ? $el[1]:null;
        $highlightedValue = $this->_highlightValue($value);
        $return .= $highlightedValue;
      }
    }
    
    return $return;
  }

  protected function _highlightValue($value)
  {
    $testYaml = 'Test: ' . $value;
    $array = sfYaml::load($testYaml);
    $type = gettype($array['Test']);

    if ($type && isset($this->_colors[$type]))
    {
      $color = $this->_colors[$type];

      $value = $this->_highlightComment($value);

      return '<span style="color: ' . $color . ';">' . $value . '</span>';
    } else {
      return false;
    }
  }

  protected function _highlightComment($value)
  {
    $value = preg_replace("/#(.*)/", '<span style="color: ' . $this->_colors['comment'] . ';">#$1</span>', $value);
    return $value;
  }
}