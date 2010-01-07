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
    $_format;

  public function __construct(sfSympalContext $sympalContext, sfSympalContent $content, $format = 'html')
  {
    $this->_symfonyContext = $sympalContext->getSymfonyContext();
    $this->_sympalContext = $sympalContext;
    $this->_configuration = $this->_symfonyContext->getConfiguration();
    $this->_dispatcher = $this->_configuration->getEventDispatcher();
    $this->_configuration->loadHelpers(array('Tag', 'Url', 'Partial'));
    $this->_content = $content;
    $this->_sympalContext->setCurrentContent($this->_content);
    $this->_format = $format;

    if ($this->_menuItem = $this->_content->getMenuItem())
    {
      $this->_sympalContext->setCurrentMenuItem($this->_menuItem);
    }
  }

  public function setResponseTitle(sfWebResponse $response)
  {
    if ($this->_menuItem)
    {
      $breadcrumbs = $this->_menuItem->getBreadcrumbs();
      $children = array();
      foreach ($breadcrumbs->getChildren() as $child)
      {
        $children[] = $child->renderLabel();
      }
      array_shift($children);

      $title = implode(sfSympalConfig::get('breadcrumbs_separator', null, ' / '), $children);
    } else {
      $title = (string) $this->_content;
    }
    $format = sfSympalConfig::get('auto_seo', 'title_format');
    $find = array(
      '%site_title%',
      '%content_title%',
      '%menu_item_label%',
      '%content_id%',
      '%separator%',
      '%ancestors%'
    );
    $replace = array(
      $this->_content->getSite()->getTitle(),
      (string) $this->_content,
      ($this->_menuItem ? $this->_menuItem->getLabel() : (string) $this->_content),
      $this->_content->getId(),
      sfSympalConfig::get('breadcrumbs_separator'),
      ($title ? $title : (string) $this->_content)
    );
    $title = str_replace($find, $replace, $format);
    $response->setTitle($title);
    return $title;
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