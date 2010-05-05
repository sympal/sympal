<?php

/**
 * PluginSite form.
 *
 * @package    form
 * @subpackage sfSympalSite
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalSiteForm extends BasesfSympalSiteForm
{
  public function setup()
  {
    parent::setup();

    // site slug is used to generate symfony app
    // but symfony restricts allowed characters for app name
    if ($this->isNew())
    {
      $this->setValidator(
        'slug',
        new sfValidatorAnd(
          array(
            $this->getValidator('slug'),
            new sfValidatorRegex(
              array('pattern' => '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/'),
              array('invalid' => 'Site slug must consist of only letters, digits and underscores. '
                               . 'It also must not start with digit.')
            )
          ),
          array('required' => $this->getValidator('slug')->getOption('required'))
        )
      );
    }
    // Don't allow editing of slug if we're dealing with an already existing site/application
    else
    {
      $this->getWidget('slug')->setAttribute('readonly', 'readonly');
      
      $this->setValidator(
        'slug',
        new sfValidatorChoice(array('choices' => array(
          $this->getObject()->getSlug()
        )))
      );
    }
    
    $field = sfApplicationConfiguration::getActive()
      ->getPluginConfiguration('sfThemePlugin')
      ->getThemeToolkit()
      ->getThemeWidgetAndValidator();
    $this->widgetSchema['theme'] = $field['widget'];
    $this->validatorSchema['theme'] = $field['validator'];
  }

  /**
   * If slug wasn't filled in explicitly, then we will generate one from title.
   * Generated slug must much symfony application naming rules.
   *
   * If slug was filled in then this method does nothing because it was already
   * checked by the validator thus symfony won't have problems using it for app name.
   *
   * @todo configurable prefix for app name
   * @todo smarter generation of unique name (current may lead to situation where)
   *       filename will be too long for filesystem
   */
  public function updateSlugColumn($value, $recursiveCall = false)
  {
    if (false === $recursiveCall)
    {
      if (null !== $value) return $value;

      // first, remove all "bad" characters (note that underscores ain't removed)
      $value = preg_replace('/[\W\d]/', null, $this['title']->getValue());
    }

    // if such app already exists, then we will suffix it with extra underscore
    if (file_exists(sfConfig::get('sf_apps_dir') . DIRECTORY_SEPARATOR . $value))
    {
      return $this->updateSlugColumn($value . '_', true);
    }
    else
    {
      return $value;
    }
  }
}