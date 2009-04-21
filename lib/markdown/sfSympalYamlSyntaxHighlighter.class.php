<?php

class sfSympalYamlSyntaxHighlighter
{
  protected $_yaml;
  protected $_html;
  protected $_colorClasses = array(
      'top_dashes' => 'yaml_top_dashes',
      'keys' => 'yaml_keys',
      'colon' => 'yaml_colon',
      'string' => 'yaml_string',
      'integer' => 'yaml_integer',
      'float' => 'yaml_float',
      'decimal' => 'yaml_decimal',
      'boolean' => 'yaml_boolean',
      'array' => 'yaml_array',
      'comment' => 'yaml_comment',
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

        $colorClass = $this->_colorClasses['top_dashes'];
        $yaml = "<span class=\"".$colorClass."\">---</span>\n" . implode("\n", $e);
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
      $return = '<span class="' . $this->_colorClasses['keys'] . '">' . $left . '</span><span class="' . $this->_colorClasses['colon'] . '">:</span>';
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

    if ($type && isset($this->_colorClasses[$type]))
    {
      $colorClass = $this->_colorClasses[$type];

      $value = $this->_highlightComment($value);

      return '<span class="'.$colorClass.'">' . $value . '</span>';
    } else {
      return false;
    }
  }

  protected function _highlightComment($value)
  {
    $value = preg_replace("/#(.*)/", '<span class="' . $this->_colorClasses['comment'] . '">#$1</span>', $value);
    return $value;
  }
}