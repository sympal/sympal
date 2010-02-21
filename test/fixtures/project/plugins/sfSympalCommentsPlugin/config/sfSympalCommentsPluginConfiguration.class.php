<?php

class sfSympalCommentsPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfSympalPlugin',
      'sfSympalUserPlugin',
      'sfFormExtraPlugin'
    );

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.content_renderer.filter_content', array($this, 'filterSympalContent'));
  }

  public function filterSympalContent(sfEvent $event, $content)
  {
    if (sfSympalConfig::get('sfSympalCommentsPlugin', 'installed') && sfSympalConfig::get('sfSympalCommentsPlugin', 'enabled') && sfSympalConfig::get($event['content']->getType()->getSlug(), 'enable_comments'))
    {
      use_helper('Comments');
      $content .= get_sympal_comments($event['content']);
    }
    return $content;
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();

    if (sfSympalConfig::get('sfSympalCommentsPlugin', 'installed', false) && sfSympalConfig::get('sfSympalCommentsPlugin', 'enabled'))
    {
      $commentTable = Doctrine::getTable('sfSympalComment')->getNumPending();
      $menu->getChild('Content')->addChild('Comments ('.$commentTable.')', '@sympal_comments');
    }
  }

  public function loadConfigForm(sfEvent $event)
  {
    $form = $event->getSubject();

    $contentTypes = Doctrine::getTable('sfSympalContentType')->findAll();
    foreach ($contentTypes as $contentType)
    {
      $form->addSetting($contentType['slug'], 'enable_comments', 'Enable Comments', 'InputCheckbox', 'Boolean');
    }

    $choices = array(
      'Approved'  => 'Approved',
      'Pending'   => 'Pending',
      'Denied'    => 'Denied',
    );
    $widget = new sfWidgetFormChoice(array('choices' => $choices));
    $validator = new sfValidatorChoice(array('choices' => array_keys($choices)));
    $form->addSetting('sfSympalCommentsPlugin', 'default_status', 'Default Status', $widget, $validator);

    $form->addSetting('sfSympalCommentsPlugin', 'default_status', 'Default Status');
    $form->addSetting('sfSympalCommentsPlugin', 'enabled', 'Enabled', 'InputCheckbox', 'Boolean');
    $form->addSetting('sfSympalCommentsPlugin', 'requires_auth', 'Commenting Requires Authentication', 'InputCheckbox', 'Boolean');
    $form->addSetting('sfSympalCommentsPlugin', 'allow_websites', 'Allow Websites', 'InputCheckbox', 'Boolean');
    $form->addSetting('sfSympalCommentsPlugin', 'websites_no_follow', 'Websites No Follow', 'InputCheckbox', 'Boolean');
  }
}