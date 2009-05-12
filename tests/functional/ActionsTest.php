<?php
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfTestFunctional(new sfBrowser());

$browser->post('/security/signin', array(
  'signin' => array(
    'username' => 'admin',
    'password' => 'admin'
  )
));

$browser->
  get('/test/ask_confirmation')->
  click('Yes')->
  with('request')->begin()->
    isParameter('sympal_ask_confirmation', 1)->
    isParameter('yes', 'Yes')->
  end()->
  with('response')->begin()->
    contains('Ok!')->
  end()->
  get('/admin/dashboard')->
  get('/test/ask_confirmation')->
  click('No')->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('request')->begin()->
    isParameter('module', 'sympal_dashboard')->
    isParameter('action', 'index')->
  end()
;

$browser->get('test/new_email');

$actions = $browser->getContext()->getController()->getActionStack()->getLastEntry()->getActionInstance();
$email = $browser->getRequest()->getAttribute('email');
$message = $email->getMessage();

$t = $browser->test();
$t->is($message->getSubject(), 'Subject');
$t->is($message->getBody(), "Body Test variable Test variable 2");

$browser->
  get('/test/forward_to_route')->
  with('request')->begin()->
    isParameter('module', 'test')->
    isParameter('action', 'route_to_forward_to')->
    isParameter('param1', 'value1')->
    isParameter('param2', 'value2')->
  end()
;

$browser->
  get('/test/forward_to_route2')->
  with('request')->begin()->
    isParameter('module', 'test')->
    isParameter('action', 'route_to_forward_to')->
    isParameter('param1', 'value1')->
    isParameter('param2', 'value2')->
  end()
;

$browser->
  get('/test/start_go_back')->
  get('/test/go_back')->
  isRedirected()->
  followRedirect()->
  with('request')->begin()->
    isParameter('module', 'test')->
    isParameter('action', 'start_go_back')->
  end()
;

$browser->get('/test/change_layout');

$response = $browser->getResponse();

$stylesheets = $response->getStylesheets();
$t->is(isset($stylesheets['test']), true);

$browser->
  with('response')->begin()->
    contains('Test Layout')->
  end()
;