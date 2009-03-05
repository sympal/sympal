<?php

function get_menu_item_breadcrumbs(MenuItem $menuItem)
{
  return get_component('sympal_menu', 'breadcrumbs', array('menuItem' => $menuItem));
}

function get_cmf_menu($name)
{
  return get_component('sympal_menu', 'menu', array('name' => $name));
}

function get_cmf_editor($menuItem = null, $entity = null)
{
  $menuItem = $menuItem ? $menuItem:sfSympalTools::getCurrentMenuItem();
  $entity = $entity ? $entity:sfSympalTools::getCurrentEntity();

  if (sfSympalConfig::isEditMode() && $entity && $menuItem)
  {
    $editor  = get_component('sympal_editor', 'tools', array('entity' => $entity, 'menuItem' => $menuItem));
    $editor .= get_slot('sympal_editors');

    return $editor;
  }
}

function get_cmf_admin_bar()
{
  if (sfContext::getInstance()->getUser()->isAuthenticated())
  {
    return get_component('sympal_editor', 'admin_bar');
  }
}

function pager_navigation($pager, $uri)
{
  $navigation = '';
 
  if ($pager->haveToPaginate())
  {  
    $uri .= (preg_match('/\?/', $uri) ? '&' : '?').'page=';
 
    // First and previous page
    if ($pager->getPage() != 1)
    {
      $navigation .= link_to(image_tag('/sf/sf_admin/images/first.png', 'align=absmiddle'), $uri.'1');
      $navigation .= link_to(image_tag('/sf/sf_admin/images/previous.png', 'align=absmiddle'), $uri.$pager->getPreviousPage()).' ';
    }
 
    // Pages one by one
    $links = array();
    foreach ($pager->getLinks() as $page)
    {
      $links[] = link_to_unless($page == $pager->getPage(), $page, $uri.$page);
    }
    $navigation .= join('  ', $links);
 
    // Next and last page
    if ($pager->getPage() != $pager->getLastPage())
    {
      $navigation .= ' '.link_to(image_tag('/sf/sf_admin/images/next.png', 'align=absmiddle'), $uri.$pager->getNextPage());
      $navigation .= link_to(image_tag('/sf/sf_admin/images/last.png', 'align=absmiddle'), $uri.$pager->getLastPage());
    }
 
  }
 
  return $navigation;
}