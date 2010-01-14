<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());

$redirect = new sfSympalRedirect();
$redirect->source = '/test_redirect';
$redirect->destination = '@homepage';
$redirect->site_id = sfSympalContext::getInstance()->getSite()->getId();
$redirect->save();

$browser->
  get('/test_redirect')->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('request')->begin()->
    isParameter('module', 'sympal_content_renderer')->
    isParameter('action', 'index')->
  end()->
  with('response')->begin()->
    matches('/Home/')->
  end()
;

$redirect = new sfSympalRedirect();
$redirect->source = '/test_redirect2';
$redirect->site_id = sfSympalContext::getInstance()->getSite()->getId();
$redirect->content_id = 1;
$redirect->save();

$browser->
  get('/test_redirect2')->
  with('response')->begin()->
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