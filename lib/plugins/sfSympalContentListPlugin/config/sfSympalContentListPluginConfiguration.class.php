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
    if (isset($variables['sfSympalContentList']))
    {
      $content = $variables['content'];
      $contentList = $variables['sfSympalContentList'];

      $request = sfContext::getInstance()->getRequest();
      $page = $request->getParameter('page', 1);
      $dataGrid = $contentList->buildDataGrid($page, $request);
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