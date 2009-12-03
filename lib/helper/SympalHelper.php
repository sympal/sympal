<?php

function sympal_link_to_site($site, $name, $path = null)
{
  $request = sfContext::getInstance()->getRequest();
  $env = sfConfig::get('sf_environment');
  $file = $env == 'dev' ? $site.'_dev.php' : ($site.'.php');
  return '<a href="'.$request->getRelativeUrlRoot().'/'.$file.($path ? '/'.$path:null).'">'.$name.'</a>';
}

/**
 * Get the Sympal User Interface
 *
 *  - The top admin bar menu
 *  - The extra sidebar
 *
 * @return string $html
 */
function get_sympal_ui()
{
  return get_sympal_admin_bar().get_sympal_side_bar();
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
 * Get the path to a YUI file
 *
 * @param string $type  Type of file (css/js)
 * @param string $name  The name of the file to get
 * @return string $path
 */
function get_sympal_yui_path($type, $name)
{
  $skin = sfSympalConfig::get('yui_skin', 'null', 'sam');
  $path = sfSympalConfig::get('yui_path', null, 'http://yui.yahooapis.com/2.7.0/build');

  $path .= '/'.$name;

  $minExceptions = array(
    'yahoo-dom-event/yahoo-dom-event'
  );
  if (!sfConfig::get('sf_debug') && !in_array($name, $minExceptions) && $type == 'js')
  {
    $path = $path.'-min';
  }

  $path = $path.($type == 'css' ? '.css':'.js');

  return $path;
}

/**
 * Use a Sympal YUI file
 *
 * @param string $type  The type (css, js)
 * @param string $name  The name of the file
 * @return void
 */
function use_sympal_yui($type, $name)
{
  $func = $type == 'js' ? 'use_javascript':'use_stylesheet';
  return $func(get_sympal_yui_path($type, $name));
}

/**
 * Use a YUI css file
 *
 * @param string $name 
 * @return void
 */
function use_sympal_yui_css($name)
{
  return use_sympal_yui('css', $name);
}

/**
 * Use a YUI js file
 *
 * @param string $name 
 * @return void
 */
function use_sympal_yui_js($name)
{
  return use_sympal_yui('js', $name);
}

/**
 * Get a sfSympalMenuBreadcrumbs instances for the given MenuItem 
 *
 * @param MenuItem $menuItem  The MenuItem instance to generate the breadcrumbs for
 * @param Content $content    The Content instance to add to the end of the breadcrumbs
 * @param string $subItem     A string to append to the end of the breadcrumbs  
 * @return string $html
 */
function get_sympal_breadcrumbs($menuItem, $content = null, $subItem = null)
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
    $breadcrumbs = $menuItem->getBreadcrumbs($content, $subItem);
  }

  sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($breadcrumbs, 'sympal.load_breadcrumbs', array('menuItem' => $menuItem, 'content' => $content, 'subItem' => $subItem)));

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
  $menuItem = $menuItem ? $menuItem:sfSympalToolkit::getCurrentMenuItem();
  $content = $content ? $content:sfSympalToolkit::getCurrentContent();

  $editor = '';

  if (sfSympalToolkit::isEditMode() && $content && $menuItem)
  {
    $editor .= get_component('sympal_editor', 'tools', array('content' => $content, 'menuItem' => $menuItem));
  }

  $editor .= get_slot('sympal_editors');

  $editor = sfProjectConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($content, 'sympal.filter_content_slot_editors'), $editor)->getReturnValue();

  return $editor;
}

/**
 * Get the Sympal admin bar at top of screen
 *
 * @return string $html
 */
function get_sympal_admin_bar()
{
  if (sfContext::getInstance()->getUser()->isAuthenticated())
  {
    return get_component('sympal_editor', 'admin_bar');
  }
}

/**
 * Get the Sympal sidebar
 *
 * @return string $html
 */
function get_sympal_side_bar()
{
  if (sfContext::getInstance()->getUser()->isAuthenticated())
  {
    return get_component('sympal_editor', 'side_bar');
  }
}

/**
 * Get a Sympal pager header <h3>
 *
 * @param sfDoctrinePager $pager
 * @param Content $content 
 * @return string $html
 */
function get_sympal_pager_header($pager, $content)
{
  $indice = $pager->getFirstIndice();
  return '<h3>Showing '.$indice.' to '.($indice + count($content) - 1).' of '.$pager->getNbResults().' total results.</h3>';
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
  if (is_null($renderFunction))
  {
    $renderFunction = 'get_sympal_content_property';
  }

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
  $user = sfContext::getInstance()->getUser();

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
          $type = Doctrine_Core::getTable('ContentSlotType')->findOneByName('ContentProperty');
        } else {
          $type = Doctrine_Core::getTable('ContentSlotType')->findOneByName($type);
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

  if (sfSympalToolkit::isEditMode() && $content->userHasLock($user))
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

    $editor = sfProjectConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($slot, 'sympal.filter_content_slot_editor'), $editor)->getReturnValue();

    slot('sympal_editors', get_slot('sympal_editors').$editor);

    $html = sfProjectConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($slot, 'sympal.filter_content_slot_html'), $html)->getReturnValue();

    return $html;
  } else {
    return $renderedValue;
  }
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