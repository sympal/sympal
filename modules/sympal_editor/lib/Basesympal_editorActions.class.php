<?php

class Basesympal_editorActions extends sfActions
{
  public function executePublish_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject()->publish();
    
    $this->getUser()->setFlash('notice', 'Content published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeUnpublish_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject()->unpublish();
    
    $this->getUser()->setFlash('notice', 'Content un-published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeSave_panel_position(sfWebRequest $request)
  {
    $x = $request->getParameter('x');
    $y = $request->getParameter('y');
    $this->getUser()->setAttribute('sympal_editor_panel_x', $x);
    $this->getUser()->setAttribute('sympal_editor_panel_y', $y);

    return sfView::NONE;
  }

  public function executeSave_form_current_tab(sfWebRequest $request)
  {
    if ($request->getParameter('name'))
    {
      $this->getUser()->setAttribute($request->getParameter('name').'.current_form_tab', $request->getParameter('id'), 'admin_module');
    }

    return sfView::NONE;
  }

  public function executeSave_tools_state(sfWebRequest $request)
  {
    $this->getUser()->setAttribute('editor_tools_state', $request->getParameter('state'), 'sympal');

    return sfView::NONE;
  }
}