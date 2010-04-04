<?php


class sfSympalContentTable extends PluginsfSympalContentTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfSympalContent');
    }
}