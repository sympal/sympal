<?php

/**
 * PluginsfSympalContentSlot form.
 *
 * @package    filters
 * @subpackage sfSympalContentSlot *
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
abstract class PluginsfSympalContentSlotFormFilter extends BasesfSympalContentSlotFormFilter
{
  public function setup()
  {
    parent::setup();

    sfSympalFormToolkit::changeContentSlotTypeWidget($this, true);
  }

  public function addTypeColumnQuery(Doctrine_Query $query, $field, $value)
  {
    if ($value)
    {
      $query->andWhere('type = ?', $value);
    }
  }
}
