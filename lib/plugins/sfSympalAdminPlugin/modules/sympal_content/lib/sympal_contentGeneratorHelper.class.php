<?php

/**
 * sympal_content module helper.
 *
 * @package    sympal
 * @subpackage sympal_content
 * @author     Your name here
 * @version    SVN: $Id: helper.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_contentGeneratorHelper extends BaseSympal_contentGeneratorHelper
{
  public function renderForm()
  {
    
  }

  public function linkToSaveAndView($object, $params)
  {
    return '<li class="sf_admin_action_save_and_view"><input type="submit" value="'.__($params['label'], array(), 'sf_admin').'" name="_save_and_view" /></li>';
  }

  public function linkToSaveAndEditMenu($object, $params)
  {
    return '<li class="sf_admin_action_save_and_edit_menu"><input type="submit" value="'.__($params['label'], array(), 'sf_admin').'" name="_save_and_edit_menu" /></li>';
  }

  public function linkToSaveAndEditSlots($object, $params)
  {
    return '<li class="sf_admin_action_save_and_edit_slots"><input type="submit" value="'.__($params['label'], array(), 'sf_admin').'" name="_save_and_edit_slots" /></li>';
  }
}
