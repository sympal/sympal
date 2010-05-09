<?php
/**
 * Helps to render an sfSympalContentSlot object
 * 
 * @package     sfSympalCMFPlugin
 * @subpackage  service
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */

class sfSympalSlotRenderer
{
  /*
   * Render with the markup needed for the edit button
   */
  const SLOT_EDITOR = 1;

  /*
   * Render just the raw slot
   */
  const SLOT_VIEW = 2;
  
  /**
   * @var sfSympalConfiguration
   * @var boolean
   */
  protected
    $_configuration,
    $_shouldLoadEditor;

  protected
    $_slot,
    $_content,
    $_options = array();

  public function __construct(sfSympalConfiguration $configuration)
  {
    $this->_configuration = $configuration;
  }

  /**
   * Include a content slot in your template.
   * 
   * This replaces get_sympal_content_slot() and is intended to be easier
   * to use. This also taps into the app.yml config for its options
   * 
   * @param mixed $slot The name of the slot or the slot itself
   * @param sfSympalContent $content The content record to get the slot from
   * @param array  $options An array of options for this slot
   * @param integer $renderMode Optionally force to render in edit mode or view mode
   * 
   * Available options include
   *  * type            The rendering type to use for this slot (e.g. Markdown)
   *  * default_value   A default value to give this slot the first time it's created
   *  * edit_mode       How to edit this slot (popup (default), inline)
   */
  public function renderSlotByName($slot, $content, $options, $renderMode = null)
  {
    // Sets up the options, content, slot
    $this->_configure($slot, $content, $options);

    if ($renderMode === null)
    {
      $shouldLoadEditor = $this->_configuration
        ->getProjectConfiguration()
        ->getPluginConfiguration('sfSympalEditorPlugin')
        ->shouldLoadEditor();
      $renderMode = $shouldLoadEditor ? self::SLOT_EDITOR : self::SLOT_VIEW;
    }

    /**
     * Either render the raw value or the editor for the slot
     */
    if ($renderMode == self::SLOT_EDITOR)
    {
      return $this->_getSlotEditor();
    }
    else
    {
      return $this->_getRenderedSlot();
    }
    
    return $value;
  }

  /**
   * Renders a content slot
   * 
   * This class could be structured better so I don't have to go backwards
   * to render a slot (starting from an object, then breaking into pieces
   * so it can be put back together again
   */
  public function renderSlot(sfSympalContentSlot $slot, $renderMode = null)
  {
    return $this->renderSlotByName($slot, $slot->getContentRenderedFor(), array(), $renderMode);
  }

  /**
   * Takes in the raw array of options, parses those options, and combines
   * them with global options
   * 
   * @param mixed $slot The name of the slot or the slot itself
   * @param array $options 
   */
  protected function _configure($slot, $content, $options)
  {
    $this->setContent($content);
    $this->_options = $options;
    
    // determine the name of the slot
    $name = ($slot instanceof sfSympalContentSlot) ? $slot->name : $slot;
    
    /*
     * Priority of options is as follows (most important to least important)
     *   1) Any option passed in from the template
     *   2) Any option defined on the content type
     *   3) Any option defined on the slot type
     */
    
    // merge in options for this slot that may appear under this content type
    $contentTypeSlotOptions = sfSympalConfig::getDeep('content_types', $this->_content->Type->name, 'content_slots', array());
    if (isset($contentTypeSlotOptions[$name]))
    {
      $this->_options = array_merge($contentTypeSlotOptions[$name], $this->_options);
    }
    
    // Gets or creates the slot
    $this->_configureSlot($slot);
    
    // Merge in the #3 from the above priority list
    $slotOptions = sfSympalConfig::get('content_slot_types', $this->_slot->type, array());
    $this->_options = array_merge($slotOptions, $this->_options);
  }

  /**
   * Returns the slot in its rendered form, which takes into consideration
   * a possible template for rendering
   */
  protected function _getRenderedSlot()
  {
    $value = $this->_slot->render();
    
    /*
     * Set the Content record on the inline object parser so that any inline
     * doctrine objects try to retrieve those objects off of relationships
     */
    $inlineObjectParser = $this->_configuration
      ->getProjectConfiguration()
      ->getPluginConfiguration('sfInlineObjectPlugin')
      ->getParser();
    $inlineObjectParser->setDoctrineRecord($this->_content);
    
    // Run the slot through its filters
    $value = $this->_configuration
      ->getProjectConfiguration()
      ->getPluginConfiguration('sfContentFilterPlugin')
      ->getParser()
      ->filter($value, $this->getOption('filters', array()));
    
    // Remove the Content record from the parser
    $inlineObjectParser->setDoctrineRecord(false);
    
    // If applicable, run the slot through a partial
    if ($this->getOption('template'))
    {
      $value = get_partial($this->getOption('template'), array('value' => $value, 'content' => $this->_content));
    }
    
    return $value;
  }

  /**
   * Configures the given slot
   * 
   * @param mixed $slot The name of the slot or the slot itself
   */
  protected function _configureSlot($slot)
  {
    // retrieve the slot
    if ($slot instanceof sfSympalContentSlot)
    {
      $this->_slot = $slot;
    }
    else
    {
      $this->_slot = $this->_content->getOrCreateSlot($slot, $this->_options);
    }
    $this->_slot->setContentRenderedFor($this->_content);
    unset($this->_options['default_value']);
  }

  /**
   * Set the content that this is being rendered for
   */
  public function setContent(sfSympalContent $content)
  {
    $this->_content = $content;
    
    // mark this content record as having content slots
    $content->setEditableSlotsExistOnPage(true);
  }

  public function getOption($name, $default = null)
  {
    return isset($this->_options[$name]) ? $this->_options[$name] : $default;
  }

  /**
   * Returns all the markup necessary to render the slot editor
   * 
   * @return string
   */
  protected function _getSlotEditor()
  {
    $options = array();
    $options['edit_mode'] = $this->getOption('edit_mode') ? $this->getOption('edit_mode') : sfSympalConfig::get('inline_editing', 'default_edit_mode');
    $options['view_url'] = url_for('sympal_content_slot_view', array('id' => $this->_slot->id, 'content_id' => $this->_content->id));
    $options['type'] = $this->_slot->type;
    
    /*
     * Give the slot a default value if it's blank.
     * 
     * @todo Move this somewhere where it can be specified on a type-by-type
     * basis (e.g., if we had an "image" content slot, it might say
     * "Click to choose image"
     */
    $renderedValue = $this->_getRenderedSlot();
    if (!$renderedValue)
    {
      $renderedValue = __('[Hover over and click edit to change.]');
    }
    
    $inlineContent = sprintf(
      '<a href="%s" class="sympal_slot_button">'.__('Edit').'</a>',
      url_for('sympal_content_slot_form', array('id' => $this->_slot->id, 'content_id' => $this->_content->id))
    );
    
    $inlineContent .= sprintf('<span class="sympal_slot_content">%s</span>', $renderedValue);
    
    return sprintf(
      '<span class="sympal_slot_wrapper %s" id="sympal_slot_wrapper_%s">%s</span>',
      htmlentities(json_encode($options)),
      $this->_slot->id,
      $inlineContent
    );
  }
}
