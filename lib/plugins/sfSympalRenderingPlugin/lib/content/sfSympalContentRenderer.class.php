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
    $_format = 'html';

  public function __construct(sfSympalContext $sympalContext, sfSympalContent $content, $format = null)
  {
    $this->_symfonyContext = $sympalContext->getSymfonyContext();
    $this->_sympalContext = $sympalContext;
    $this->_configuration = $this->_symfonyContext->getConfiguration();
    $this->_dispatcher = $this->_configuration->getEventDispatcher();
    $this->_configuration->loadHelpers(array('Tag', 'Url', 'Partial'));

    $request = $this->_symfonyContext->getRequest();
    $response = $this->_symfonyContext->getResponse();

    $this->_content = $content;
    $this->_menuItem = $this->_content->getMenuItem();
    $format = $format ? $format : $request->getRequestFormat('html');
    $format = $format ? $format : 'html';

    $this->_sympalContext->setCurrentMenuItem($this->_menuItem);

    $this->_sympalContext->setCurrentContent($this->_content);
    sfSympalTheme::change($this->_content->getLayout());

    if (!$response->getTitle())
    {
      $title = $this->_menuItem->getBreadcrumbs()->getPathAsString();
      $title = $title ? $this->_content->Site->title.sfSympalConfig::get('breadcrumbs_separator', null, ' / ').$title:$this->_content->Site->title;
      $response->setTitle($title);
    }
  }

  public function getFormat()
  {
    return $this->_format;
  }

  public function setFormat($format)
  {
    $this->_format = $format;
  }

  public function render()
  {
    $menuItem = $this->_menuItem;
    $content = $this->_content;
    $format = $this->_format;

    $typeVarName = strtolower($content['Type']['name'][0]).substr($content['Type']['name'], 1, strlen($content['Type']['name']));

    $variables = array(
      'format' => $format,
      'content' => $content,
      'menuItem' => $menuItem,
      $typeVarName => $content->getRecord(),
      'contentRecord' => $content->getRecord()
    );

    $variables = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_variables'), $variables)->getReturnValue();

    $return = null;

    if ($format == 'html')
    {
      $return = sfSympalToolkit::getSymfonyResource($content->getTemplateToRenderWith(), $variables);
      $return = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_content', $variables), $return)->getReturnValue();
    } else {
      switch ($format)
      {
        case 'xml':
        case 'json':
        case 'yml':
          $return = $content->exportTo($format, true);
        default:
          $event = $this->_dispatcher->notifyUntil(new sfEvent($this, 'sympal.content_renderer.unknown_format', $variables));

          if ($event->isProcessed())
          {
            $this->setFormat($event['format']);
            $return = $event->getReturnValue();
          }
      }
    }
    
    if ($return === null)
    {
      sfContext::getInstance()->getController()->getActionStack()->getLastEntry()->getActionInstance()->forward404();
    }

    return $return;
  }
}