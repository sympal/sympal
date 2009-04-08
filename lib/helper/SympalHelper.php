<?php

function get_sympal_yui_path($type, $name)
{
  $skin = sfSympalConfig::get('yui_skin', 'null', 'sam');
  $path = sfSympalConfig::get('yui_path', null, '/sfSympalPlugin/yui');

  $path .= '/'.$name;

  if (sfConfig::get('sf_debug'))
  {
    $fullPath = sfConfig::get('sf_web_dir').$path.($type == 'css' ? '.css':'.js');
    if (!file_exists($fullPath))
    {
      throw new sfException('YUI path does not exist: "'.$fullPath.'"');
    }
  } else {
    $minPath = $path.'-min';
    if (file_exists(sfConfig::get('sf_web_dir').$minPath))
    {
      $path = $minPath;
    }
  }

  return $path;
}

function use_sympal_yui($type, $name)
{
  $func = $type == 'js' ? 'use_javascript':'use_stylesheet';
  return $func(get_sympal_yui_path($type, $name));
}

function use_sympal_yui_css($name)
{
  return use_sympal_yui('css', $name);
}

function use_sympal_yui_js($name)
{
  return use_sympal_yui('js', $name);
}

function get_sympal_breadcrumbs($menuItem, $content = null, $subItem = null)
{
  if (!$menuItem)
  {
    return false;
  }

  // If we were passed an array then generate manual breacrumbs from it
  if (is_array($menuItem))
  {
    $breadcrumbs = sfSympalToolkit::generateBreadcrumbs($menuItem);
  } else {
    $breadcrumbs = $menuItem->getBreadcrumbs($content, $subItem);
  }

  sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($breadcrumbs, 'sympal.load_breadcrumbs', array('menuItem' => $menuItem, 'content' => $content, 'subItem' => $subItem)));

  $title = $breadcrumbs->getPathAsString();
  sfContext::getInstance()->getResponse()->setTitle($title);

  if ($html = (string) $breadcrumbs)
  {
    return $html;
  } else {
    return false;
  }
}

function set_sympal_title($title = null)
{
  sfContext::getInstance()->getResponse()->setTitle($title);
}

function get_sympal_menu($name, $showChildren = true, $class = null)
{
  return sfSympalMenuSiteManager::getMenu($name, $showChildren, $class);
}

function get_sympal_split_menus($name, $showChildren = true, $max = null, $split = false)
{
  $menu = sfSympalMenuSiteManager::getMenu($name, $showChildren);

  return sfSympalMenuSiteManager::split($menu, $max, $split);
}

function get_sympal_editor($menuItem = null, $content = null)
{
  $menuItem = $menuItem ? $menuItem:sfSympalToolkit::getCurrentMenuItem();
  $content = $content ? $content:sfSympalToolkit::getCurrentContent();

  $editor = '';

  if (sfSympalToolkit::isEditMode() && $content && $menuItem)
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

function get_sympal_side_bar()
{
  if (sfContext::getInstance()->getUser()->isAuthenticated())
  {
    return get_component('sympal_editor', 'side_bar');
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

function get_sympal_content_property($content, $name)
{
  return $content->$name;
}

function get_sympal_column_content_slot($content, $name, $renderFunction = null, $type = 'ContentProperty')
{
  if (is_null($renderFunction))
  {
    $renderFunction = 'get_sympal_content_property';
  }

  return get_sympal_content_slot($content, $name, $type, true, $renderFunction);
}

function get_sympal_content_slot($content, $name, $type = 'Text', $isColumn = false, $renderFunction = null)
{
  $defaultValue = '[Double click to edit slot content]';
  $slotsCollection = $content->getSlots();
  $slots = array();
  foreach ($slotsCollection as $slot)
  {
    $slots[$slot['name']] = $slot;
  }

  if ($name instanceof ContentSlot)
  {
    $slot = $name;
    $type = $slot['Type'];
  } else {
    if (!isset($slots[$name]))
    {
      $slot = new ContentSlot();
      $slot->content_id = $content->id;
      $slot->render_function = $renderFunction;
      if ($isColumn)
      {
        $slot->is_column = true;
      }

      if (!$slot->exists())
      {
        if ($isColumn)
        {
          $type = Doctrine::getTable('ContentSlotType')->findOneByName('ContentProperty');
        } else {
          $type = Doctrine::getTable('ContentSlotType')->findOneByName($type);
        }

        $slot->setType($type);
        $slot->setName($name);
      }

      $slot->save();
    } else {
      $slot = $slots[$name];
    }
  }

  if (sfSympalToolkit::isEditMode() && !$slot->hasValue()) {
    $renderedValue = $defaultValue;
  } else {
    $renderedValue = $slot->render();
  }

  if (sfSympalToolkit::isEditMode() && $content->userHasLock(sfContext::getInstance()->getUser()))
  {
    $html  = '<span class="sympal_editable_content_slot" onMouseOver="javascript: highlight_sympal_content_slot(\''.$slot['id'].'\');" onMouseOut="javascript: unhighlight_sympal_content_slot(\''.$slot['id'].'\');" title="Double click to edit this slot named `'.$name.'`" id="edit_content_slot_button_'.$slot['id'].'" style="cursor: pointer;" onClick="javascript: edit_sympal_content_slot(\''.$slot['id'].'\');">';
    $html .= $renderedValue;
    $html .= '</span>';

    if ($isColumn)
    {
      $html .= '<input type="hidden" id="content_slot_'.$slot['id'].'_is_column" value="'.$slot['name'].'" />';
    }

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
  constraintoviewport: false,
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
    return $renderedValue;
  }
}