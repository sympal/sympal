<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());

$redirect = new sfSympalRedirect();
$redirect->source = '/test_redirect1/:parameter1/ok/:parameter2';
$redirect->destination = '@redirect_route';
$redirect->site_id = sfSympalContext::getInstance()->getService('site_manager')->getSite()->getId();
$redirect->save();

$redirect = new sfSympalRedirect();
$redirect->source = '/test_redirect2';
$redirect->site_id = sfSympalContext::getInstance()->getService('site_manager')->getSite()->getId();
$redirect->content_id = 1;
$redirect->save();

$redirect = new sfSympalRedirect();
$redirect->source = '/test_redirect3/:parameter1/ok/:parameter2';
$redirect->destination = '/some/path/ok/:parameter2';
$redirect->site_id = sfSympalContext::getInstance()->getService('site_manager')->getSite()->getId();
$redirect->save();

$browser->clearCache();

$browser->
  get('/test_redirect1/test1/ok/test2')->
  with('response')->begin()->
    isStatusCode(301)->
    isRedirected()->
    followRedirect()->
  end()->
  with('request')->begin()->
    isParameter('module', 'test')->
    isParameter('action', 'redirect')->
    isParameter('parameter1', 'test1')->
  end()
;

$browser->
  get('/test_redirect2')->
  with('response')->begin()->
    isStatusCode(301)->
    isRedirected()->
    followRedirect()->
  end()->
  with('request')->begin()->
    isParameter('module', 'sympal_content_renderer')->
    isParameter('action', 'index')->
  end()->
  with('response')->begin()->
    matches('/Sample Content List/')->
  end()
;

$browser->
  get('/test_redirect3/test1/ok/test2')->
  with('response')->begin()->
    isStatusCode(301)->
    isRedirected()->
    followRedirect()->
  end()->
  with('request')->begin()->
    isParameter('module', 'test')->
    isParameter('action', 'redirect')->
    isParameter('parameter2', 'test2')->
  end()
;