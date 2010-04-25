<?php


class sfSympalContentSlotRefTable extends PluginsfSympalContentSlotRefTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfSympalContentSlotRef');
    }
}