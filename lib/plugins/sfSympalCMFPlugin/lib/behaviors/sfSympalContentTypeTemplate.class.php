<?php

/**
 * Doctrine template for Sympal content type models to act as.
 * Automatically adds a content_id column and creates a one-to-one relationship
 * with sfSympalContent.
 *
 * Example: If you had a sfSympalBlogPost content type you would have sfSympalContent
 * hasOne sfSympalBlogPost and sfSympalBlogPost hasOne sfSympalContent
 *
 * @package sfSympalCMFPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalContentTypeTemplate extends sfSympalRecordTemplate
{
  /**
   * Hook into the content type models setTableDefinition() process and add 
   * a content_id column
   *
   * @return void
   */
  public function setTableDefinition()
  {
    parent::setTableDefinition();

    $this->hasColumn('content_id', 'integer');
  }

  /**
   * Hook into the content type models setTableDefinition() process and add the
   * relationships between sfSympalContent and the sfSympalContentTypeNameModel
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();

    $this->hasOne('sfSympalContent as Content', array(
      'local' => 'content_id',
      'foreign' => 'id',
      'onDelete' => 'CASCADE'
    ));

    $contentTable = Doctrine_Core::getTable('sfSympalContent');
    $class = $this->getInvoker()->getTable()->getOption('name');
    $contentTable->bind(array($class, array('local' => 'id', 'foreign' => 'content_id')), Doctrine_Relation::ONE);
  }
}