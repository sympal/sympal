<?php
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());
$t->is(1, 1);

// Cache is off due to bug in Doctrine result caching. Wait for 1.0.8 to be released.
/*

$profiler = new Doctrine_Connection_Profiler();

Doctrine_Manager::getInstance()->setAttribute('use_dql_callbacks', true);

$conn = Doctrine_Manager::connection();
$conn->setListener($profiler);

sfSympalConfig::set('use_query_caching', true);
sfSympalConfig::set('use_result_caching', true);

function execute_query()
{
  Doctrine_Query::create()
    ->from('sfGuardUser')
    ->execute();
}

execute_query();
execute_query();

$t->is($profiler->count(), 1);

sfSympalConfig::set('use_query_caching', false);
sfSympalConfig::set('use_result_caching', false);
*/