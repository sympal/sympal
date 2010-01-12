<?php

class sfSympalContentRenderer
{
  protected
    $_symfonyContext,
    $_sympalContext,
    $_configuration,
    $_dispatcher,
    $_menuItem,
    $_content,
    $_format,
    $_renderVariables = array();

  public function __construct(sfSympalContext $sympalContext, sfSympalContent $content, $format = 'html')
  {
    $this->_symfonyContext = $sympalContext->getSymfonyContext();
    $this->_sympalContext = $sympalContext;
    $this->_configuration = $this->_symfonyContext->getConfiguration();
    $this->_dispatcher = $this->_configuration->getEventDispatcher();
    $this->_configuration->loadHelpers(array('Tag', 'Url', 'Partial'));
    $this->_content = $content;
    $this->_menuItem = $this->_content->getMenuItem();
    $this->_format = $format;
  }

  public function getFormat()
  {
    return $this->_format;
  }

  public function setFormat($format)
  {
    $this->_format = $format;
  }

  public function getRenderVariables()
  {
    if (!$this->_renderVariables)
    {
      $typeVarName = strtolower($this->_content['Type']['name'][0]).substr($this->_content['Type']['name'], 1, strlen($this->_content['Type']['name']));

      $this->_renderVariables = array(
        'format' => $this->_format,
        'content' => $this->_content,
        'menuItem' => $this->_menuItem,
        $typeVarName => $this->_content->getRecord(),
        'contentRecord' => $this->_content->getRecord()
      );

      $this->_renderVariables = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_variables'), $this->_renderVariables)->getReturnValue();
    }
    return $this->_renderVariables;
  }

  public function render()
  {
    $variables = $this->getRenderVariables();

    if ($this->_format == 'html')
    {
      $return = sfSympalToolkit::getSymfonyResource($this->_content->getTemplateToRenderWith(), $variables);
      $return = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_content', $variables), $return)->getReturnValue();
    } else {
      $return = $this->renderNonHtmlFormats();
    }
    return $return;
  }

  public function renderNonHtmlFormats()
  {
    switch ($this->_format)
    {
      case 'xml':
      case 'json':
      case 'yml':
        $return = $this->_content->exportTo($this->_format, true);
      default:
        $event = $this->_dispatcher->notifyUntil(new sfEvent($this, 'sympal.content_renderer.unknown_format', $this->getRenderVariables()));

        if ($event->isProcessed())
        {
          $this->setFormat($event['format']);
          $return = $event->getReturnValue();
        }
    }
    if (isset($return))
    {
      if ($return)
      {
        $response = $this->_symfonyContext->getResponse();
        $response->setContent($return);
        $response->send();
        exit;
      } else {
        return $this;
      }
    } else {
      return $this;
    }
  }

  public function __toString()
  {
    try
    {
      return (string) $this->render();
    }
    catch (Exception $e)
    {
      return sfSympalToolkit::renderException($e);
    }
  }
}