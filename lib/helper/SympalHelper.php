<?php

function get_sympal_breadcrumbs($menuItem, $entity = null, $subItem = null, $setTitle = false)
{
  $breadcrumbs = $menuItem->getBreadcrumbs($entity, $subItem);

  if ($setTitle)
  {
    sfContext::getInstance()->getResponse()->setTitle($breadcrumbs->getPathAsString());
  }

  if ($html = (string) $breadcrumbs)
  {
    return '<div id="sympal_breadcrumbs">'.$html.'</div>';
  } else {
    return false;
  }
}

function get_sympal_menu($name, $recursive = true)
{
  return sfSympalMenuSite::getMenu($name, $recursive);
}

function get_sympal_truncated_menus($name, $recursive = true, $max = null, $split = false)
{
  return sfSympalMenuSite::getMenu($name, $recursive, $max, $split);
}

function get_sympal_comments($entity)
{
  if (sfSympalConfig::get($entity['Type']['name'], 'enable_comments'))
  {
    return get_component('sympal_comments', 'for_entity', array('entity' => $entity));
  }
}

function get_sympal_editor($menuItem = null, $entity = null)
{
  $menuItem = $menuItem ? $menuItem:sfSympalTools::getCurrentMenuItem();
  $entity = $entity ? $entity:sfSympalTools::getCurrentEntity();

  if (sfSympalTools::isEditMode() && $entity && $menuItem)
  {
    $editor  = get_component('sympal_editor', 'tools', array('entity' => $entity, 'menuItem' => $menuItem));
  }
  $editor .= get_slot('sympal_editors');
  return $editor;
}

function get_sympal_admin_bar()
{
  if (sfContext::getInstance()->getUser()->isAuthenticated())
  {
    return get_component('sympal_editor', 'admin_bar');
  }
}

function get_sympal_pager_header($pager, $entities)
{
  $indice = $pager->getFirstIndice();
  return '<h3>Showing '.$indice.' to '.($indice + count($entities) - 1).' of '.$pager->getNbResults().' total results.</h3>';
}

function get_sympal_pager_navigation($pager, $uri)
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