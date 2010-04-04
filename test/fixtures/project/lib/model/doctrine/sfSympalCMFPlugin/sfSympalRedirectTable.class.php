<?php


class sfSympalRedirectTable extends PluginsfSympalRedirectTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfSympalRedirect');
    }
}