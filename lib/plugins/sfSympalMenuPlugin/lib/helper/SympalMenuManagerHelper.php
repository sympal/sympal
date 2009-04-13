<?php

function get_sympal_menu_manager($menuItem)
{
  $menu = get_sympal_menu($menuItem['slug'], true, 'sfSympalMenuManager');
  if ($menu)
  {
    $menu->callRecursively('setRoute', null);

    return $menu;
  } else {
    return false;
  }
}

function get_sympal_menu_manager_html($menuItem)
{
  $menu = get_sympal_menu_manager($menuItem);
  return '<div id="sympal_menu_manager_tree">'.$menu.'</div>';
}

function get_sympal_menu_manager_js($menuItem)
{
  return get_partial('sympal_menu_items/manager_js', array('menuItem' => $menuItem));
}