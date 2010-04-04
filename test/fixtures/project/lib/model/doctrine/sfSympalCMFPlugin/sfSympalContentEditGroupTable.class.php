<?php


class sfSympalContentEditGroupTable extends PluginsfSympalContentEditGroupTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfSympalContentEditGroup');
    }
}