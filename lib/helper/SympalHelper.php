<?php

function get_sympal_breadcrumbs($menuItem, $content = null, $subItem = null, $setTitle = false)
{
  if (!$menuItem)
  {
    return false;
  }

  // If we were passed an array then generate manual breacrumbs from it
  if (is_array($menuItem))
  {
    return sfSympalTools::generateBreadcrumbs($menuItem);
  }

  if ($setTitle)
  {
    $breadcrumbs = $menuItem->getBreadcrumbs($content, $subItem);
    $title = $breadcrumbs->getPathAsString();

    sfContext::getInstance()->getResponse()->setTitle($title);
  }

  $breadcrumbs = $menuItem->getBreadcrumbs();

  if ($html = (string) $breadcrumbs)
  {
    return '<div id="sympal_breadcrumbs">'.$html.'</div>';
  } else {
    return false;
  }
}

function set_sympal_title($title = null)
{
  sfContext::getInstance()->getResponse()->setTitle($title);
}

function get_sympal_menu($name, $showChildren = true)
{
  return sfSympalMenuSiteManager::getMenu($name, $showChildren);
}

function get_sympal_split_menus($name, $showChildren = true, $max = null, $split = false)
{
  $menu = sfSympalMenuSiteManager::getMenu($name, $showChildren);

  return sfSympalMenuSiteManager::split($menu, $max, $split);
}

function get_sympal_comments($content)
{
  if (sfSympalConfig::get('sfSympalCommentsPlugin', 'enabled') && sfSympalConfig::get($content['Type']['name'], 'enable_comments'))
  {
    return get_component('sympal_comments', 'for_content', array('content' => $content));
  }
}

function get_sympal_editor($menuItem = null, $content = null)
{
  $menuItem = $menuItem ? $menuItem:sfSympalTools::getCurrentMenuItem();
  $content = $content ? $content:sfSympalTools::getCurrentContent();

  $editor = '';

  if (sfSympalTools::isEditMode() && $content && $menuItem)
  {
    $editor .= get_component('sympal_editor', 'tools', array('content' => $content, 'menuItem' => $menuItem));
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

function get_sympal_pager_header($pager, $content)
{
  $indice = $pager->getFirstIndice();
  return '<h3>Showing '.$indice.' to '.($indice + count($content) - 1).' of '.$pager->getNbResults().' total results.</h3>';
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

function get_sympal_content_slot($content, $name, $type = 'Text', $defaultValue = '[Double click to edit slot content]')
{
  $user = sfContext::getInstance()->getUser();

  $slotsCollection = $content->getSlots();
  $slots = array();
  foreach ($slotsCollection as $slot)
  {
    $slots[$slot['name']] = $slot;
  }

  if (!isset($slots[$name]))
  {
    $slot = new ContentSlot();
    $slot->content_id = $content->id;

    if (!$slot->exists())
    {
      $type = Doctrine::getTable('ContentSlotType')->findOneByName($type);
      if (!$type)
      {
        $type = new ContentSlotType();
        $type->setName($type);
      }
      $slot->setType($type);
      $slot->setName($name);
    }

    $slot->save();
  } else {
    $slot = $slots[$name];
  }

  if (sfSympalTools::isEditMode() && $content->userHasLock(sfContext::getInstance()->getUser()))
  {
    if ($slot->getValue())
    {
      $contentSlot = $slot->render();
    } else {
      $contentSlot = $defaultValue;
    }

    $html  = '<div class="sympal_editable_content_slot" onMouseOver="javascript: highlight_sympal_content_slot(\''.$slot['id'].'\');" onMouseOut="javascript: unhighlight_sympal_content_slot(\''.$slot['id'].'\');" title="Double click to edit this slot named `'.$name.'`" id="edit_content_slot_button_'.$slot['id'].'" style="cursor: pointer;" onClick="javascript: edit_sympal_content_slot(\''.$slot['id'].'\');">';
    $html .= $contentSlot;
    $html .= '</div>';

    $editor  = '<div class="sympal_edit_slot_box yui-skin-sam">';
    $editor .= '<div id="edit_content_slot_'.$slot['id'].'">';
    $editor .= '<div class="hd">Edit Slot: '.$slot['name'].'</div>';
    $editor .= '<div class="bd" id="edit_content_slot_content_'.$slot['id'].'"></div>';
    $editor .= '</div>';
    $editor .= '</div>';

    $editor .= sprintf(<<<EOF
<script type="text/javascript">
myPanel = new YAHOO.widget.Panel('edit_content_slot_%s', {
	underlay:"shadow",
	close:true,
	visible:true,
	context:['edit_content_slot_button_%s', 'tl', 'tl'],
  autofillheight: "body",
  constraintoviewport: true,
	draggable:true} );

myPanel.cfg.setProperty("underlay", "matte");
myPanel.render();
myPanel.hide();

YAHOO.util.Event.addListener("edit_content_slot_button_%s", "dblclick", myPanel.show, myPanel, true);
YAHOO.util.Event.addListener("edit_content_slot_editor_panel_button_%s", "click", myPanel.show, myPanel, true);
</script>
EOF
    ,
      $slot['id'],
      $slot['id'],
      $slot['id'],
      $slot['id'],
      $slot['id'],
      $slot['id']
    );

    slot('sympal_editors', get_slot('sympal_editors').$editor);

    return $html;
  } else {
    return $slot->render();
  }
}