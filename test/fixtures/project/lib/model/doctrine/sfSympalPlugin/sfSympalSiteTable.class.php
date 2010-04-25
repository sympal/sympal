<?php


class sfSympalSiteTable extends PluginsfSympalSiteTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfSympalSite');
    }
}