<?php

/**
 * PluginTranslation form.
 *
 * @package    form
 * @subpackage Translation
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginTranslationForm extends BaseTranslationForm
{
  public function setup()
  {
    parent::setup();
    $this->embedI18n(Doctrine::getTable('Language')->getLanguageCodes());
  }
}