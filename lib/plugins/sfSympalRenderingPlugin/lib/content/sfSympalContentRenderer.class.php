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

  public function __construct(sfSympalContext $sympalContext, Content $content, $format = null)
  {
    $this->_symfonyContext = $sympalContext->getSymfonyContext();
    $this->_sympalContext = $sympalContext;
    $this->_configuration = $this->_symfonyContext->getConfiguration();
    $this->_dispatcher = $this->_configuration->getEventDispatcher();
    $this->_configuration->loadHelpers(array('Tag', 'Url', 'Partial'));

    $request = $this->_symfonyContext->getRequest();
    $response = $this->_symfonyContext->getResponse();

    $this->_content = $content;
    $this->_menuItem = $this->_content->getMainMenuItem();
    $format = $format ? $format : $request->getRequestFormat('html');
    $format = $format ? $format : 'html';

    $this->_sympalContext->setCurrentMenuItem($this->_menuItem);

    $this->_sympalContext->setCurrentContent($this->_content);
    sfSympalTheme::change($this->_content->getLayout());

    if (!$response->getTitle())
    {
      $title = $this->_menuItem->getBreadcrumbs()->getPathAsString();
      $title = $title ? $this->_menuItem->Site->title.sfSympalConfig::get('breadcrumbs_separator', null, ' / ').$title:$this->_menuItem->Site->title;
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

    $event = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_variables'), $variables);
    $variables = $event->getReturnValue();

    $return = null;

    if ($format == 'html')
    {
      $return = $this->_getContentViewHtml($content, $variables);
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
    
    if (!$return)
    {
      $this->_throwUnknownFormat404($this->_format);
    }

    return $return;
  }

  protected function _getContentViewHtml(Content $content, $variables = array())
  {
    if ($content->content_template_id)
    {
      $template = $content->getTemplate();
    } else {
      $template = $content->getType()->getTemplate();
    }

    $contentType = sfInflector::tableize($content->getType()->getName());

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.pre_render_'.$contentType.'_content', array('content' => $content, 'template' => $template)));

    if ($template && $templatePath = $template->getPath())
    {
      $return = sfSympalToolkit::getSymfonyResource($templatePath, $variables);
    }
    else if ($template && $body = $template->getBody())
    {
      $return = sfSympalTemplate::process($body, $variables);;
    } else {
      $return = get_sympal_breadcrumbs($this->_menuItem, $content).$this->_renderDoctrineData($content);
    }

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.post_render_'.$contentType.'_content', array('content' => $content, 'template' => $template)));

    $event = $this->_dispatcher->filter(new sfEvent($this, 'sympal.filter_content'), $return);
    $return = $event->getReturnValue();

    $event = $this->_dispatcher->filter(new sfEvent($this, 'sympal.filter_'.$contentType.'_content'), $return);
    $return = $event->getReturnValue();

    return $return;
  }

  protected function _renderDoctrineData($content)
  {
    $html  = '<h1>Content Data</h1>';
    $html .= $this->_renderData($content->toArray(), false);

    $html .= '<h1>'.get_class($content->getRecord()).' Data</h1>';
    $html .= $this->_renderData($content->getRecord()->toArray(), false);

    $html .= '<h1>Slots</h1>';
    $html .= '<table>';
    foreach ($content->getSlots() as $key => $slot)
    {
      $html .= '<tr><th>'.$key.'</th><td>'.get_sympal_content_slot($content, $slot['name']).'</td></tr>';
    }
    $html .= '</table>';

    return $html;
  }

  protected function _renderData(array $content, $deep = true)
  {
    $html  = '';
    $html .= '<table>';  
    foreach ($content as $key => $value)
    {
      if (strstr($key, '_id'))
      {
        continue;
      }
      $val = null;
      if (is_array($value) && $deep)
      {
        $val = '<td>' . $this->_renderData($value) . '</td>';
      } else if (!is_array($value)) {
        $val = '<td>' . $value . '</td>';
      }
      if (isset($val) && $val)
      {
        $html .= '<tr>';
        $html .= '<th>' . Doctrine_Inflector::classify(str_replace('_id', '', $key)) . '</th>';
        $html .= $val;
        $html .= '</tr>';
      }
    }
    $html .= '</table>';
    return $html;
  }

  protected function _throwUnknownFormat404($format)
  {
    sfContext::getInstance()->getController()->getActionStack()->getLastEntry()->getActionInstance()->forward404();
  }

  public function __call($method, $arguments)
  {
    return sfSympalExtendClass::extendEvent($this, $method, $arguments);
  }
}