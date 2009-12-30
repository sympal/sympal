<?php

/*
 * This file is part of the sympal_assets package.
 *
 * (c) 2009 Vincent Agnano <vincent.agnano@particul.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInput represents an HTML input file browser tag.
 *
 * @package    sympal_assets
 * @subpackage widget
 * @author     Vincent Agnano <vincent.agnano@particul.es>
 */
class sfWidgetFormInputSympalAssets extends sfWidgetForm
{

  protected $context;
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * type: The widget type (text by default)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('type', 'text');
    
    $this->setOption('is_hidden', false);
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $this->context = sfContext::getInstance();

    $attributes = array_merge(array('type' => $this->getOption('type'), 'name' => $name, 'value' => $value), $attributes);
    $attributes = $this->fixFormId($attributes);
    $url = $this->context->getRouting()->generate('sympal_assets_select');

    $tag = $this->renderTag('input', $attributes);

    $tag .= $this->includeView();
    $tag .= $this->includeDelete();

    // add
    if(!isset($attributes['load_javascript']) || $attributes['load_javascript'] !== false)
    {
      $tag .= $this->loadJavascript(array_merge($attributes, array('url' => $url)));
    }
    if(!isset($attributes['load_stylesheet']) || $attributes['load_stylesheet'] !== false)
    {
      $this->context->getResponse()->addStylesheet('/sfSympalAssetsPlugin/css/form_widget.css');
    }


    $tag = $this->wrapTag($tag);
    return $tag;
  }
  

  /**
   * Insert dependant javascripts and include a <script> with sympal_assetsWindowManager.addListerner
   * @return string HTML formatted js code
   */
  protected function loadJavascript(array $params)
  {
    $this->context->getResponse()->addJavascript('/sfSympalAssetsPlugin/js/WindowManager.js');
    $this->context->getResponse()->addJavascript('/sfSympalAssetsPlugin/js/form_widget.js');
    return <<<EOF
    <script type="text/javascript">
      sympal_assetsWindowManager.addListerner({target: '{$params['id']}', url: '{$params['url']}'});
    </script>
EOF;
  }


  /**
   * Includes a delete tag
   * @return string HTML formatted span class="delete"
   */
  protected function includeDelete()
  {
    $tag = '<a class="delete">delete</a>';
    return $tag;
  }

  /**
   * Includes a view tag
   * @return string HTML formatted span class="view"
   */
  protected function includeView()
  {
    $tag = '<a class="view">view</a>';
    return $tag;
  }

  /**
   * Wraps a tag within a <span class="sympal_assets_input_file"></span>
   * @param string $tag tag to wrap
   * @return string HTML string
   */
  protected function wrapTag($tag)
  {
    return '<span class="sympal_assets_input_file">'.$tag.'</span>';
  }
}
