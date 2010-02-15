<?php

class sfSympalContentListUnknownFormatListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'sympal.content_renderer.unknown_format';
  }

  public function run(sfEvent $event)
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