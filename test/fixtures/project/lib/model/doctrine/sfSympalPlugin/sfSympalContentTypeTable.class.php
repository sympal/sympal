<?php


class sfSympalContentTypeTable extends PluginsfSympalContentTypeTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfSympalContentType');
    }
}