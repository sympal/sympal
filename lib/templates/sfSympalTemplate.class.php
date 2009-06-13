<?php

class sfSympalTemplate
{
  protected
    $_code,
    $_variables = array(),
    $_rendered;

  public function __construct($code, $variables = array())
  {
    $this->_code = $code;
    $this->_variables = $variables;
  }

  public static function process($code, $variables = array())
  {
    $template = new self($code, $variables);
    return (string) $template;
  }

  public function __toString()
  {
    return (string) $this->render();
  }

  public function render()
  {
    if (!$this->_rendered)
    {
      $sf_context = sfContext::getInstance();
      $vars = array(
        'sf_request' => $sf_context->getRequest(),
        'sf_response' => $sf_context->getResponse(),
        'sf_user' => $sf_context->getUser()
      );
      $variables = array_merge($this->_variables, $vars);
      sfSympalConfig::set('template_vars', $variables);

      foreach ($variables as $name => $variable)
      {
        $$name = $variable;
      }

      $code = $this->_code;
      ob_start();
      $code = str_replace('[?php', '<?php', $code);
      $code = str_replace('?]', '?>', $code);
      eval("?>" . $code);
      $rendered = ob_get_contents();
      ob_end_clean();

      $this->_rendered = preg_replace_callback("/##(.*)\/(.*)##/", array($this, '_replaceSymfonyResources'), $rendered);
    }

    return $this->_rendered;
  }

  protected function _replaceSymfonyResources($matches)
  {
    list($match, $module, $action) = $matches;
    $variables = sfSympalConfig::get('template_vars');
    
    return sfSympalToolkit::getSymfonyResource($module, $action, $variables);
  }
}