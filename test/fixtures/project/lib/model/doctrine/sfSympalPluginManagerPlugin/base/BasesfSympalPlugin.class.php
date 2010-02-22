<?php

/**
 * BasesfSympalPlugin
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $plugin_author_id
 * @property string $title
 * @property string $name
 * @property clob $description
 * @property clob $summary
 * @property string $image
 * @property string $users
 * @property string $scm
 * @property string $homepage
 * @property string $ticketing
 * @property string $link
 * @property boolean $is_downloaded
 * @property boolean $is_installed
 * @property boolean $is_theme
 * @property sfSympalPluginAuthor $Author
 * 
 * @method integer              getPluginAuthorId()   Returns the current record's "plugin_author_id" value
 * @method string               getTitle()            Returns the current record's "title" value
 * @method string               getName()             Returns the current record's "name" value
 * @method clob                 getDescription()      Returns the current record's "description" value
 * @method clob                 getSummary()          Returns the current record's "summary" value
 * @method string               getImage()            Returns the current record's "image" value
 * @method string               getUsers()            Returns the current record's "users" value
 * @method string               getScm()              Returns the current record's "scm" value
 * @method string               getHomepage()         Returns the current record's "homepage" value
 * @method string               getTicketing()        Returns the current record's "ticketing" value
 * @method string               getLink()             Returns the current record's "link" value
 * @method boolean              getIsDownloaded()     Returns the current record's "is_downloaded" value
 * @method boolean              getIsInstalled()      Returns the current record's "is_installed" value
 * @method boolean              getIsTheme()          Returns the current record's "is_theme" value
 * @method sfSympalPluginAuthor getAuthor()           Returns the current record's "Author" value
 * @method sfSympalPlugin       setPluginAuthorId()   Sets the current record's "plugin_author_id" value
 * @method sfSympalPlugin       setTitle()            Sets the current record's "title" value
 * @method sfSympalPlugin       setName()             Sets the current record's "name" value
 * @method sfSympalPlugin       setDescription()      Sets the current record's "description" value
 * @method sfSympalPlugin       setSummary()          Sets the current record's "summary" value
 * @method sfSympalPlugin       setImage()            Sets the current record's "image" value
 * @method sfSympalPlugin       setUsers()            Sets the current record's "users" value
 * @method sfSympalPlugin       setScm()              Sets the current record's "scm" value
 * @method sfSympalPlugin       setHomepage()         Sets the current record's "homepage" value
 * @method sfSympalPlugin       setTicketing()        Sets the current record's "ticketing" value
 * @method sfSympalPlugin       setLink()             Sets the current record's "link" value
 * @method sfSympalPlugin       setIsDownloaded()     Sets the current record's "is_downloaded" value
 * @method sfSympalPlugin       setIsInstalled()      Sets the current record's "is_installed" value
 * @method sfSympalPlugin       setIsTheme()          Sets the current record's "is_theme" value
 * @method sfSympalPlugin       setAuthor()           Sets the current record's "Author" value
 * 
 * @package    sympal
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BasesfSympalPlugin extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('sf_sympal_plugin');
        $this->hasColumn('plugin_author_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('description', 'clob', null, array(
             'type' => 'clob',
             ));
        $this->hasColumn('summary', 'clob', null, array(
             'type' => 'clob',
             ));
        $this->hasColumn('image', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('users', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('scm', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('homepage', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('ticketing', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('link', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('is_downloaded', 'boolean', null, array(
             'type' => 'boolean',
             'default' => 0,
             ));
        $this->hasColumn('is_installed', 'boolean', null, array(
             'type' => 'boolean',
             'default' => 0,
             ));
        $this->hasColumn('is_theme', 'boolean', null, array(
             'type' => 'boolean',
             'default' => 0,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('sfSympalPluginAuthor as Author', array(
             'local' => 'plugin_author_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));
    }
}