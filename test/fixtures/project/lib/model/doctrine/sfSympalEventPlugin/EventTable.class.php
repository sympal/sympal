<?php


class EventTable extends PluginEventTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Event');
    }
}