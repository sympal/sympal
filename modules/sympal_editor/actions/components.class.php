<?php
class sympal_editorComponents extends sfComponents
{
  public function executeEdit_panel()
  {
    $this->entity = $this->menuItem->getEntity();
  }

  public function executeLanguage(sfWebRequest $request)
  {
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => Doctrine::getTable('Language')->getLanguageCodes()));
    unset($this->form[$this->form->getCSRFFieldName()]);
  }

  public function executeTools()
  {
    $entityTypes = array(null => null);
    foreach (Doctrine::getTable('EntityType')->findAll() as $entityType)
    {
      $entityTypes[$this->generateUrl('sympal_entities_create_type', array('type' => $entityType->getName()))] = $entityType->getName();
    }

    $this->newEntityWidget = new sfWidgetFormSelect(array('choices' => $entityTypes));
    $this->newEntityWidget->setAttribute('onChange', "location.href = this.value");

    $q = Doctrine_Query::create()
      ->from('Entity e')
      ->andWhere('e.locked_by = ?', $this->getUser()->getGuardUser()->getId());

    $this->locks = $q->execute();
  }

  public function executeAdmin_bar()
  {
    $this->menu = new sfSympalMenuBackend('Backend');
    $this->menu->addNode('Icon', null, array('label' => '<div id="sympal-icon">Sympal</div>'));

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_admin_bar', array('menu' => $this->menu)));
  }
}