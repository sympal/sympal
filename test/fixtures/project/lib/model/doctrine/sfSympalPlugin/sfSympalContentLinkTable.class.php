<?php


class sfSympalContentLinkTable extends PluginsfSympalContentLinkTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfSympalContentLink');
    }
}