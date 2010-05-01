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
    $_template = false,
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
   * @param string $name The name of the slot
   * @param sfSympalContent $content The content record to get the slot from
   * @param array  $options An array of options for this slot
   * 
   * Available options include
   *  * type            The rendering type to use for this slot (e.g. Markdown)
   *  * default_value   A default value to give this slot the first time it's created
   *  * edit_mode       How to edit this slot (popup (default), inline)
   */
  public function renderSlotByName($name, $content, $options)
  {
    // Sets up the options, content, slot, and template
    $this->_configure($name, $content, $options);
    
    /**
     * Either render the raw value or the editor for the slot
     */
    if ($this->_configuration->getProjectConfiguration()->getPluginConfiguration('sfSympalEditorPlugin')->shouldLoadEditor())
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
   * Takes in the raw array of options, parses those options, and combines
   * them with global options
   * 
   * @param string $name The name of the slot
   * @param array $options 
   */
  protected function _configure($name, $content, $options)
  {
    $this->_content = $content;
    $this->_options = $options;
    
    // merge in options for this slot that may appear under this content type
    $slotOptions = sfSympalConfig::getDeep('content_types', $this->_content->Type->name, 'content_slots', array());
    if (isset($slotOptions[$name]))
    {
      $this->_options = array_merge($slotOptions[$name], $this->_options);
    }
    
    // Gets or creates the slot
    $this->_configureSlot($name);
    
    // Determines the template (if any) based on the options
    $this->_configureTemplate();
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
      ->getPluginConfiguration('sfSympalInlineObjectPlugin')
      ->getParser();
    $inlineObjectParser->setDoctrineRecord($this->getContentRenderedFor());
    
    // Run the slot through its filters
    $value = $this->_configuration
      ->getPluginConfiguration('sfSympalContentFilterPlugin')
      ->getParser()
      ->filter($value, $this->getOption('filters', array()));
    
    // Remove the Content record from the parser
    $inlineObjectParser->setDoctrineRecord(false);
    
    // If applicable, run the slot through a partial
    if ($this->_template)
    {
      $value = get_partial($this->_template, array('value' => $value, 'content' => $this->_content));
    }
    
    return $value;
  }

  protected function _configureSlot($name)
  {
    // retrieve the slot
    if ($name instanceof sfSympalContentSlot)
    {
      $this->_slot = $name;
      $name = $name->getName();
    }
    else
    {
      $this->_slot = $this->_content->getOrCreateSlot($name, $this->_options);
      unset($this->_options['default_value']);
    }
    $this->_slot->setContentRenderedFor($this->_content);
  }

  /**
   * Determines the template based on the options
   */
  protected function _configureTemplate()
  {
    if (isset($this->_options['template']))
    {
      $this->_template = $this->_options['template'];
      unset($this->_options['template']);
    }
    elseif ($template = sfSympalConfig::getDeep('content_slot_types', $this->_slot->type, 'template', false))
    {
      $this->_template = $template;
    }
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
