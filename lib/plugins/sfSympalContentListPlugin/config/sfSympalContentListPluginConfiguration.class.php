<?php

/**
 * sfSympalContentListPlugin configuration.
 * 
 * @package     sfSympalContentListPlugin
 * @subpackage  config
 * @author      Your name here
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class sfSympalContentListPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.0-DEV';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('sympal.content_renderer.filter_variables', array($this, 'listenForFilterVariables'));
    $this->dispatcher->connect('sympal.content_renderer.unknown_format', array($this, 'listenForUnknownFormat'));
  }

  public function listenForFilterVariables(sfEvent $event, $variables)
  {
    $content = $variables['content'];
    if ($content->getType()->getName() == 'sfSympalContentList')
    {
      $contentList = $content->getRecord();

      $request = sfContext::getInstance()->getRequest();
      $dataGrid = $contentList->buildDataGrid($request);
      $pager = $dataGrid->getPager();

      $variables['pager'] = $pager;
      $variables['dataGrid'] = $dataGrid;
    }

    return $variables;
  }

  public function listenForUnknownFormat(sfEvent $event)
  {
    if (isset($event['pager']))
    {
      $pager = $event['pager'];
      $context = sfContext::getInstance();
      $response = $context->getResponse();
      $request = $context->getRequest();

      $className = 'sf'.ucfirst($event['format']).'Feed';
      if (!class_exists($className))
      {
        return false;
      }

      $feed = sfFeedPeer::newInstance($event['format']);

      $feed->initialize(array(
        'title'       => $response->getTitle(),
        'link'        => $request->getUri()
      ));

      $items = sfFeedPeer::convertObjectsToItems($pager->getResults());
      $feed->addItems($items);

      $event->setProcessed(true);
      $event->setReturnValue($feed->asXml());

      return true;
    } else {
      return false;
    }
  }
}