<?php


class sfSympalContentGroupTable extends PluginsfSympalContentGroupTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfSympalContentGroup');
    }
}