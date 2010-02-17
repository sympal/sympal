<?php
class sfSympalContentListFilterVariablesListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'sympal.content_renderer.filter_variables';
  }

  public static function run(sfEvent $event, $variables)
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
}