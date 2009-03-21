<?php
class sfSympalMenuNode extends sfSympalMenu
{
  protected
    $_name,
    $_route,
    $_current,
    $_options = array();

  public function __construct($name = null, $route = null, $options = array())
  {
    $this->_name = $name;
    $this->_route = $route;
    $this->_options = $options;
  }

  public function getLabel()
  {
    return (is_array($this->_options) && isset($this->_options['label'])) ? $this->_options['label']:$this->_name;
  }

  public function setLabel($label)
  {
    $this->_options['label'] = $label;
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

  public function isCurrent($bool = null)
  {
    if (!is_null($bool))
    {
      $this->_current = $bool;
    }
    return $this->_current;
  }

  public function _render()
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

        if ($menuItem && sfSympalTools::isEditMode())
        {
          $options['id'] = 'menu_item_'.$menuItem['id'];
          $html .= link_to($this->getLabel(), $menuItem->getItemRoute(), $options);
          $menu = new sfSympalMenuBackend();
          if (sfConfig::get('sf_debug'))
          {
            $menu->addNode('Debug')->addNode('<pre>'.sfYaml::dump($menuItem->toArray(true), 6).'</pre>');
          }
          $menu->addNode('Edit', '@sympal_menu_items_edit?id='.$menuItem['id']);
          if ($menuItem->getMainEntity())
          {
            $menu->addNode('Edit Entity', '@sympal_entities_edit?id='.$menuItem->getMainEntity()->getId());
          }
          $menu->addNode('Follow', $menuItem->getItemRoute());
          $menu->addNode('Close', null, array('id' => 'menu_item_'.$menuItem['id'].'_hide_control_menu'));

          $editor  = '';
          $editor .= '<div class="yui-skin-sam">';
          $editor .= '<div id="menu_item_'.$menuItem['id'].'_control_menu" class="yuimenu"><div class="bd">'.$menu.'</div></div>';
          $editor .= '<script type="text/javascript">';
          $editor .= 'var oMenu = new YAHOO.widget.Menu("menu_item_'.$menuItem['id'].'_control_menu", { underlay:"shadow",
        	close:true,
        	underlay:"matte",
        	draggable:true,
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
      if ($this->hasNodes() && $this->isRecursiveOutput())
      {
        $html .= '<ul>';
        foreach ($this->_nodes as $node)
        {
          $html .= $node;
        }
        $html .= '</ul>';
      }
      $html .= '</li>';
      return $html;
    }
  }

  public function getPathAsString()
  {
    $nodes = array();
    $obj = $this;

    do {
    	$nodes[] = $obj->getName();
    } while ($obj = $obj->getParent());

    return implode(' > ', array_reverse($nodes));
  }
}