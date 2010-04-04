<?php

/**
 * Unit test for the sfSympalConfiguration class
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  test
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-02-06
 * @version     svn:$Id$ $Author$
 */

$app = 'sympal';
require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(2);

$sympalConfiguration = sfSympalContext::getInstance()->getSympalConfiguration();

sfContext::getInstance()->getRequest()->setParameter('module', 'sympal_dashboard');
$t->is($sympalConfiguration->isAdminModule(), true, '->isAdminModule() returns true for admin module');
 
sfContext::getInstance()->getRequest()->setParameter('module', 'sympal_content_renderer');
$t->is($sympalConfiguration->isAdminModule(), false, '->isAdminModule() returns false for non-admin module');