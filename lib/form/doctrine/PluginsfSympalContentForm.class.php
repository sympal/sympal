<?php

/**
 * PluginContent form.
 *
 * @package    form
 * @subpackage sfSympalContent
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalContentForm extends BasesfSympalContentForm
{
  public function setup()
  {
    parent::setup();

    $this->validatorSchema->setOption('allow_extra_fields', true);

    unset(
      $this['site_id'],
      $this['created_at'],
      $this['updated_at'],
      $this['last_updated_by_id'],
      $this['slots_list'],
      $this['links_list'],
      $this['assets_list'],
      $this['comments_list']  // this should actually not be here - think of something better later
    );

    $field = sfApplicationConfiguration::getActive()
      ->getPluginConfiguration('sfThemePlugin')
      ->getThemeToolkit()
      ->getThemeWidgetAndValidator();
    $this->widgetSchema['theme'] = $field['widget'];
    $this->validatorSchema['theme'] = $field['validator'];

    // Sets up the template widget
    sfSympalFormToolkit::changeTemplateWidget($this);

    // Sets up the module widget
    sfSympalFormToolkit::changeModuleWidget($this);

    if (!$this->object->content_type_id)
    {
      $this->object->Type = Doctrine_Core::getTable('sfSympalContentType')->findOneBySlug('page');
    }
    else
    {
      $this->object->Type;
    }

    $this->configureMenuSection();

    $this->_embedTypeForm();
  }

  protected function _embedTypeForm()
  {
    $typeModelClass = $this->object->Type->name ? $this->object->Type->name:'sfSympalPage';
    $typeFormClass = $typeModelClass . 'Form';

    $record = $this->object->getRecord();
    $record->mapValue('contentForm', $this);
    $typeForm = new $typeFormClass($record);

    unset($typeForm['id'], $typeForm['content_id']);

    if (count($typeForm))
    {
      $this->embedForm('TypeForm', $typeForm);
      $this->widgetSchema['TypeForm']->setLabel($this->object->Type->label);
    }
  }

  protected function configureMenuSection()
  {
    if (!$this->isNew()) return false;

    $this->widgetSchema['menu_create'] = new sfWidgetFormInputCheckbox();
    $this->widgetSchema['menu_publish'] = new sfWidgetFormInputCheckbox();

    $this->widgetSchema['menu_parent_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'sfSympalMenuItem',
      'add_empty' => true
    ));
    $this->validatorSchema['menu_parent_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'sfSympalMenuItem',
      'required' => false
    ));

    return $this;
  }

  public function save($con = null)
  {
    $content = parent::save($con);

    // then menu creation has been requested
    if (array_key_exists('menu_create', $this->getTaintedValues()))
    {
      $menuItem = $content->getMenuItem();
      $menuItem->bindSympalContent($content);

      if (array_key_exists('menu_publish', $this->getTaintedValues())) {
        $menuItem->setDatePublished(date('Y-m-d H:i:s'));
      }

      if (strlen($this['menu_parent_id']->getValue()) > 0)
      {
        $menuItem->getNode()->insertAsLastChildOf(
          Doctrine_Core::getTable('sfSympalMenuItem')->find($this['menu_parent_id']->getValue())
        );
      }
      else
      {
        $menuItem->save();
      }
    }

    return $content;
  }

}
