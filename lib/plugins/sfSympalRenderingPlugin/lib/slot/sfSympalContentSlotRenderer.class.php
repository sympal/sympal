<?php

/**
 * Utility class that renders an sfSympalContentSlot object
 * 
 * The main job of this class is to get the raw value of the slot in
 * the correct way. This means looking at the render_function value
 * on the slot.
 * 
 * Normally, the return value will just be the value of the slot. In the
 * case of column slots, this will be the value of the column sharing
 * the slot's name on the actual content type object.
 * 
 * Transformations on the raw value (e.g. converting markdown to html)
 * should be done via transformers
 * 
 * @package     sfSympalRenderingPlugin
 * @subpackage  renderer
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-02-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalContentSlotRenderer
{
  protected
    $_contentSlot;

  public function __construct(sfSympalContentSlot $contentSlot)
  {
    $this->_contentSlot = $contentSlot;
    $this->_content = $contentSlot->getContentRenderedFor();
  }

  public function render()
  {
    $value = $this->getRenderedValue();

    return $value;
  }

  public function getRawValue()
  {
    $value = $this->_contentSlot->getRawValue();

    return $value;
  }

  public function getRenderedValue()
  {
    if ($this->_contentSlot->render_function)
    {
      $renderFunction = $this->_contentSlot->render_function;
      if (method_exists($this->_content, $renderFunction))
      {
        return $this->_content->$renderFunction($this);
      }
      else if (method_exists($this->_contentSlot, $renderFunction))
      {
        return $this->_contentSlot->$renderFunction($this);
      }
      else
      {
        sfSympalToolkit::autoloadHelper($renderFunction);

        return $renderFunction($this->_content, $this->_contentSlot->name);
      }
    }
    else
    {
      return $this->getRawValue();
    }
  }

  public function __toString()
  {
    try
    {
      return (string) $this->render();
    }
    catch (Exception $e)
    {
      return sfSympalToolkit::renderException($e);
    }
  }
}