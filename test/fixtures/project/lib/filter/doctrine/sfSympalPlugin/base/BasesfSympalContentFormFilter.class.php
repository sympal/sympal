<?php

/**
 * sfSympalContent filter form base class.
 *
 * @package    sympal
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasesfSympalContentFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'site_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Site'), 'add_empty' => true)),
      'content_type_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Type'), 'add_empty' => true)),
      'last_updated_by_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('LastUpdatedBy'), 'add_empty' => true)),
      'created_by_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('CreatedBy'), 'add_empty' => true)),
      'date_published'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'custom_path'        => new sfWidgetFormFilterInput(),
      'theme'              => new sfWidgetFormFilterInput(),
      'template'           => new sfWidgetFormFilterInput(),
      'module'             => new sfWidgetFormFilterInput(),
      'action'             => new sfWidgetFormFilterInput(),
      'publicly_editable'  => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'page_title'         => new sfWidgetFormFilterInput(),
      'meta_keywords'      => new sfWidgetFormFilterInput(),
      'meta_description'   => new sfWidgetFormFilterInput(),
      'i18n_slug'          => new sfWidgetFormFilterInput(),
      'created_at'         => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'         => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'slug'               => new sfWidgetFormFilterInput(),
      'slots_list'         => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContentSlot')),
      'groups_list'        => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
      'edit_groups_list'   => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
      'links_list'         => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent')),
      'assets_list'        => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalAsset')),
      'comments_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalComment')),
    ));

    $this->setValidators(array(
      'site_id'            => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Site'), 'column' => 'id')),
      'content_type_id'    => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Type'), 'column' => 'id')),
      'last_updated_by_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('LastUpdatedBy'), 'column' => 'id')),
      'created_by_id'      => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('CreatedBy'), 'column' => 'id')),
      'date_published'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'custom_path'        => new sfValidatorPass(array('required' => false)),
      'theme'              => new sfValidatorPass(array('required' => false)),
      'template'           => new sfValidatorPass(array('required' => false)),
      'module'             => new sfValidatorPass(array('required' => false)),
      'action'             => new sfValidatorPass(array('required' => false)),
      'publicly_editable'  => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'page_title'         => new sfValidatorPass(array('required' => false)),
      'meta_keywords'      => new sfValidatorPass(array('required' => false)),
      'meta_description'   => new sfValidatorPass(array('required' => false)),
      'i18n_slug'          => new sfValidatorPass(array('required' => false)),
      'created_at'         => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'         => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'slug'               => new sfValidatorPass(array('required' => false)),
      'slots_list'         => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContentSlot', 'required' => false)),
      'groups_list'        => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
      'edit_groups_list'   => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
      'links_list'         => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent', 'required' => false)),
      'assets_list'        => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalAsset', 'required' => false)),
      'comments_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalComment', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_sympal_content_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addSlotsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.sfSympalContentSlotRef sfSympalContentSlotRef')
          ->andWhereIn('sfSympalContentSlotRef.content_slot_id', $values);
  }

  public function addGroupsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.sfSympalContentGroup sfSympalContentGroup')
          ->andWhereIn('sfSympalContentGroup.group_id', $values);
  }

  public function addEditGroupsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.sfSympalContentEditGroup sfSympalContentEditGroup')
          ->andWhereIn('sfSympalContentEditGroup.group_id', $values);
  }

  public function addLinksListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.sfSympalContentLink sfSympalContentLink')
          ->andWhereIn('sfSympalContentLink.linked_content_id', $values);
  }

  public function addAssetsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.sfSympalContentAsset sfSympalContentAsset')
          ->andWhereIn('sfSympalContentAsset.asset_id', $values);
  }

  public function addCommentsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.sfSympalContentComment sfSympalContentComment')
          ->andWhereIn('sfSympalContentComment.comment_id', $values);
  }

  public function getModelName()
  {
    return 'sfSympalContent';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'site_id'            => 'ForeignKey',
      'content_type_id'    => 'ForeignKey',
      'last_updated_by_id' => 'ForeignKey',
      'created_by_id'      => 'ForeignKey',
      'date_published'     => 'Date',
      'custom_path'        => 'Text',
      'theme'              => 'Text',
      'template'           => 'Text',
      'module'             => 'Text',
      'action'             => 'Text',
      'publicly_editable'  => 'Boolean',
      'page_title'         => 'Text',
      'meta_keywords'      => 'Text',
      'meta_description'   => 'Text',
      'i18n_slug'          => 'Text',
      'created_at'         => 'Date',
      'updated_at'         => 'Date',
      'slug'               => 'Text',
      'slots_list'         => 'ManyKey',
      'groups_list'        => 'ManyKey',
      'edit_groups_list'   => 'ManyKey',
      'links_list'         => 'ManyKey',
      'assets_list'        => 'ManyKey',
      'comments_list'      => 'ManyKey',
    );
  }
}
