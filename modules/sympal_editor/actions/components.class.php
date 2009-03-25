<?php
class sympal_editorComponents extends sfComponents
{
  public function executeEdit_panel()
  {
    $this->content = $this->menuItem->getContent();
  }

  public function executeLanguage(sfWebRequest $request)
  {
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => sfSympalConfig::get('language_codes', null, array($this->getUser()->getCulture()))));
    unset($this->form[$this->form->getCSRFFieldName()]);
  }

  public function executeTools()
  {
    $contentTypes = array(null => null);
    foreach (Doctrine::getTable('ContentType')->findAll() as $contentType)
    {
      $contentTypes[$this->generateUrl('sympal_content_create_type', array('type' => $contentType->getName()))] = $contentType->getName();
    }

    $this->newContentWidget = new sfWidgetFormSelect(array('choices' => $contentTypes));
    $this->newContentWidget->setAttribute('onChange', "location.href = this.value");

    $this->lock = $this->getUser()->getOpenContentLock();
  }

  public function executeAdmin_bar()
  {
    $this->menu = new sfSympalMenuAdminBar('AdminBar');
    $this->menu->addChild('Icon', null, array('label' => '<div id="sympal-icon">Sympal</div>'));

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_admin_bar', array('menu' => $this->menu)));
  }
}