<?php

function sympal_link_to_site($site, $name, $path = null)
{
  $request = sfContext::getInstance()->getRequest();
  $env = sfConfig::get('sf_environment');
  $file = $env == 'dev' ? $site.'_dev.php' : ($site.'.php');
  return '<a href="'.$request->getRelativeUrlRoot().'/'.$file.($path ? '/'.$path:null).'">'.$name.'</a>';
}

function get_sympal_admin_menu()
{
  return get_component('sympal_admin', 'menu');
}

/**
 * Get the Sympal flash boxes
 *
 * @return string $html
 */
function get_sympal_flash()
{
  return get_partial('sympal_default/flash');
}

/**
 * Get a sfSympalMenuBreadcrumbs instances for the given MenuItem 
 *
 * @param MenuItem $menuItem  The MenuItem instance to generate the breadcrumbs for
 * @param string $subItem     A string to append to the end of the breadcrumbs  
 * @return string $html
 */
function get_sympal_breadcrumbs($menuItem, $subItem = null)
{
  if (!$menuItem)
  {
    return false;
  }

  // If we were passed an array then generate manual breacrumbs from it
  if (is_array($menuItem))
  {
    $breadcrumbs = sfSympalMenuBreadcrumbs::generate($menuItem);
  } else {
    $breadcrumbs = $menuItem->getBreadcrumbs($subItem);
  }

  sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($breadcrumbs, 'sympal.load_breadcrumbs', array('menuItem' => $menuItem, 'subItem' => $subItem)));

  $title = $breadcrumbs->getPathAsString();
  set_sympal_title($title);

  if ($html = (string) $breadcrumbs)
  {
    return $html;
  } else {
    return false;
  }
}

/**
 * Set the response title
 *
 * @param string $title 
 * @return void
 */
function set_sympal_title($title = null)
{
  $response = sfContext::getInstance()->getResponse();
  if (!$response->getTitle())
  {
    $response->setTitle($title);
  }
}

/**
 * Get a sfSympalMenu instance for the given menu root
 *
 * @param string $name  The slug of the root menu item you wish to retrieve
 * @param bool $showChildren Whether or not it should show the children when rendering
 * @param string $class The menu class to return an instance of
 */
function get_sympal_menu($name, $showChildren = true, $class = null)
{
  return sfSympalMenuSiteManager::getMenu($name, $showChildren, $class);
}

/**
 * Get a menu split into 2 instances, a primary and submenu
 *
 * @param string $name  The slug of the root menu item you wish to retrieve
 * @param string $showChildren  Whether or not it should show the children when rendering
 * @param string $max The max menu items to include in the first menu
 * @param string $split Whether to return a 2nd menu item with the remaining menu items in it
 * @return mixed Either one sfSympalMenu instance of an array with 2 sfSympalMenu instances
 */
function get_sympal_split_menus($name, $showChildren = true, $max = null, $split = false)
{
  $menu = sfSympalMenuSiteManager::getMenu($name, $showChildren);
  if ($menu)
  {
    return sfSympalMenuSiteManager::split($menu, $max, $split);
  } else {
    return false;
  }
}

/**
 * Get the floating sympal editor for the given MenuItem and Content instances
 *
 * @param MenuItem $menuItem 
 * @param Content $content 
 * @return string $html
 */
function get_sympal_editor($menuItem = null, $content = null)
{
  $user = sfContext::getInstance()->getUser();
  $sympalContext = sfSympalContext::getInstance();
  $menuItem = $menuItem ? $menuItem : $sympalContext->getCurrentMenuItem();
  $content = $content ? $content : $sympalContext->getCurrentContent();

  $editor = '';

  if ($user->isEditMode() && $content && $menuItem)
  {
    $editor .= get_component('sympal_editor', 'editor', array('content' => $content, 'menuItem' => $menuItem));
  }

  $editor .= get_slot('sympal_editors');

  $editor = sfProjectConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($content, 'sympal.filter_content_slot_editors'), $editor)->getReturnValue();

  return $editor;
}

/**
 * Get a Sympal pager header <h3>
 *
 * @param sfDoctrinePager $pager
 * @return string $html
 */
function get_sympal_pager_header($pager)
{
  use_stylesheet('/sfSympalPlugin/css/pager.css');

  $indice = $pager->getFirstIndice();
  return '<div class="sympal_pager_header"><h3>Showing '.$indice.' to '.($pager->getLastIndice()).' of '.$pager->getNbResults().' total results.</h3></div>';
}

/**
 * Get the navigation links for given sfDoctrinePager instance
 *
 * @param sfDoctrinePager $pager
 * @param string $uri  The uri to prefix to the links
 * @return string $html
 */
function get_sympal_pager_navigation($pager, $uri)
{
  use_stylesheet('/sfSympalPlugin/css/pager.css');

  $navigation = '<div class="sympal_pager_navigation">';
 
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
      $links[] = '<span>'.link_to_unless($page == $pager->getPage(), $page, $uri.$page).'</span>';
    }
    $navigation .= join('  ', $links);
 
    // Next and last page
    if ($pager->getPage() != $pager->getLastPage())
    {
      $navigation .= ' '.link_to(image_tag('/sf/sf_admin/images/next.png', 'align=absmiddle'), $uri.$pager->getNextPage());
      $navigation .= link_to(image_tag('/sf/sf_admin/images/last.png', 'align=absmiddle'), $uri.$pager->getLastPage());
    }
  }
  $navigation .= '</div>';

  return $navigation;
}

/**
 * Get a Sympal Content instance property
 *
 * @param Content $content 
 * @param string $name 
 * @return mixed $value
 */
function get_sympal_content_property($content, $name)
{
  return $content->$name;
}

/**
 * Get Sympal content slot value which is just a column value on the Content
 *
 * @param Content $content  The Content instance
 * @param string $name The name of the slot
 * @param string $renderFunction The function to use to render
 * @param string $type The type of slot
 * @return string $value
 */
function get_sympal_column_content_slot($content, $name, $renderFunction = null, $type = 'ContentProperty')
{
  return get_sympal_content_slot($content, $name, $type, true, $renderFunction);
}

/**
 * Get Sympal content slot value
 *
 * @param Content $content  The Content instance
 * @param string $name The name of the slot
 * @param string $type The type of slot
 * @param string $isColumn  Whether it is a column property
 * @param string $renderFunction The function to use to render the value
 * @return void
 * @author Jonathan Wage
 */
function get_sympal_content_slot($content, $name, $type = 'Text', $isColumn = false, $renderFunction = null)
{
  if ($content->hasField($name))
  {
    $isColumn = true;
  }

  if ($isColumn && is_null($renderFunction))
  {
    $renderFunction = 'get_sympal_content_property';
  }

  $slots = $content->getSlots();

  if ($name instanceof sfSympalContentSlot)
  {
    $slot = $name;
  } else {
    $slot = $content->getOrCreateSlot($name, $type, $isColumn, $renderFunction);
  }

  $user = sfContext::getInstance()->getUser();
  if ($user->isEditMode())
  {
    return get_sympal_content_slot_editor($slot);
  } else {
    return $slot->render();
  }
}

function get_sympal_content_slot_editor(sfSympalContentSlot $slot)
{
  $name = $slot->getName();
  $isColumn = $slot->getIsColumn();
  $defaultValue = '[Double click to edit slot content]';

  $user = sfContext::getInstance()->getUser();

  if ($user->isEditMode() && !$slot->hasValue())
  {
    $renderedValue = $defaultValue;
  } else {
    $renderedValue = $slot->render();
  }

  return '
<span title="Double click to edit the '.$name.' slot" id="sympal_content_slot_'.$slot->getId().'" class="sympal_content_slot">
  <input type="hidden" class="content_slot_id" value="'.$slot->getId().'" />
  <input type="hidden" class="content_id" value="'.$slot->getContentRenderedFor()->getId().'" />
  <span class="editor"></span>
  <span class="value">'.$renderedValue.'</span>
</span>';
}

/**
 * Get icons for changing languages
 *
 * @return string $html
 */
function get_change_language_icons()
{
  $icons = array();
  foreach (sfSympalConfig::get('language_codes') as $code)
  {
    if (sfContext::getInstance()->getUser()->getCulture() == $code)
    {
      $icons[] = image_tag('/sfSympalPlugin/images/flags/'.strtolower($code).'.png');
    } else {
      $icons[] = link_to(image_tag('/sfSympalPlugin/images/flags/'.strtolower($code).'.png'), '@sympal_change_language?language='.$code, 'title=Switch to '.format_language($code));
    }
  }
  return implode(' ', $icons);
}

function get_gravatar_url($emailAddress, $size = 40)
{
  $default = "http://www.somewhere.com/homestar.jpg";

  $url = 'http://www.gravatar.com/avatar.php?gravatar_id='.md5(strtolower($emailAddress)).'&default='.urlencode($default).'&size='.$size;
  return $url;
}