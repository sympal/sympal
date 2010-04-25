<?php


class sfSympalContentSlotTable extends PluginsfSympalContentSlotTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfSympalContentSlot');
    }
}