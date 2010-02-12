<?php

function get_sympal_search_route($area = 'frontend')
{
  $routes = array(
    'sympal_admin_search',
    'sympal_frontend_search'
  );
  $currentRouteName = sfContext::getInstance()->getRouting()->getCurrentRouteName();
  $currentRouteName = in_array($currentRouteName, $routes) ? $currentRouteName : 'sympal_'.$area.'_search';
  return '@'.$currentRouteName;
}