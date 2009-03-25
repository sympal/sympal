<?php
class sfSympalMenuSite extends sfSympalMenu
{
  protected 
    $_name = null,
    $_menuItem = null,
    $_route = null;

  public function __construct($name = null, $route = null, $options = array())
  {
    $this->_name = $name;
    $this->_route = $route;
    $this->_options = $options;
  }

  public function getRoute()
  {
    return $this->_route;
  }

  public function setRoute($route)
  {
    $this->_route = $route;
  }

  public function getOptions()
  {
    return $this->_options;
  }

  public function setOptions($options)
  {
    $this->_options = $options;
  }

  public function getOption($name, $default = null)
  {
    if (isset($this->_options[$name]))
    {
      return $this->_options[$name];
    }

    return $default;
  }

  public function setOption($name, $value)
  {
    $this->_options[$name] = $value;
  }

  public function getMenuItem()
  {
    return $this->_menuItem;
  }

  public function setMenuItem($menuItem)
  {
    $this->_menuItem = $menuItem;

    $this->requiresAuth($menuItem->requires_auth);
    $this->requiresNoAuth($menuItem->requires_no_auth);
    $this->setCredentials($menuItem->getAllPermissions());

    $currentMenuItem = sfSympalTools::getCurrentMenuItem();

    if ($currentMenuItem && $currentMenuItem->exists())
    {
      $this->isCurrent($menuItem->id == $currentMenuItem->id);
    }

    $this->setLevel($menuItem->level);
  }

  public function getMenuItemSubMenu($menuItem)
  {
    foreach ($this->_children as $child)
    {
      if ($child->getMenuItem()->id == $menuItem->id && $child->getChildren())
      {
        $result = $child;
      } else if ($n = $child->getMenuItemSubMenu($menuItem)) {
        $result = $n;
      }

      if (isset($result))
      {
        $class = get_class($result->getParent());
        $instance = new $class();
        $instance->setMenuItem($menuItem);
        $instance->setChildren($result->getChildren());

        return $instance;
      }
    }
  }

  protected function _render()
  {
    if ($this->checkUserAccess())
    {
      $html = '<li'.($this->isCurrent() ? ' class="current"':null).'>';
      if ($this->_route)
      {
        $options = $this->getOptions();
        if  ($this->isCurrent())
        {
          $options['class'] = 'current';
        }
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
        $menuItem = $this->getMenuItem();

        if ($menuItem && sfSympalTools::isEditMode() && sfSympalConfig::get('enable_menu_item_dropdown') && $this->debug())
        {
          $options['id'] = 'menu_item_'.$menuItem['id'];
          $html .= link_to($this->getLabel(), $menuItem->getItemRoute(), $options);
          $menu = new sfSympalMenuAdminBar();
          if (sfConfig::get('sf_debug'))
          {
            $menu->addChild('Debug')->addChild('<pre>'.sfYaml::dump($menuItem->toArray(true), 6).'</pre>');
          }
          $menu->addChild('Edit', '@sympal_menu_items_edit?id='.$menuItem['id']);
          $menu->addChild('Add Child', 'sympal_menu_items/ListNew?id='.$menuItem['id']);
          $menu->addChild('Follow', $menuItem->getItemRoute());
          $menu->addChild('Close', null, array('id' => 'menu_item_'.$menuItem['id'].'_hide_control_menu'));

          $editor  = '';
          $editor .= '<div class="yui-skin-sam">';
          $editor .= '<div id="menu_item_'.$menuItem['id'].'_control_menu" class="yuimenu"><div class="bd">'.$menu.'</div></div>';
          $editor .= '<script type="text/javascript">';
          $editor .= 'var oMenu = new YAHOO.widget.Menu("menu_item_'.$menuItem['id'].'_control_menu", { underlay:"shadow",
        	underlay:"matte",
        	context:[\'menu_item_'.$menuItem['id'].'\', \'tl\', \'bl\'] });';
          $editor .= 'oMenu.render();';
          $editor .= 'YAHOO.util.Event.addListener("menu_item_'.$menuItem['id'].'", "mouseover", oMenu.show, oMenu, true);';
          $editor .= 'YAHOO.util.Event.addListener("menu_item_'.$menuItem['id'].'_hide_control_menu", "mouseover", oMenu.hide, oMenu, true);';
          $editor .= '</script>';
          $editor .= '</div>';

          slot('sympal_editors', get_slot('sympal_editors').$editor);

        } else {
          $html .= link_to($this->getLabel(), $this->getRoute(), $options);
        }
      } else {
        $html .= $this->getLabel();
      }
      if ($this->hasChildren() && $this->showChildren())
      {
        $html .= '<ul>';
        foreach ($this->_children as $child)
        {
          $html .= $child->_render();
        }
        $html .= '</ul>';
      }
      $html .= '</li>';
      return $html;
    }
  }
}