<?php

require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());

$browser->
  signInAsAdmin()->
  get('/blog-post/sample_blogpost')->
  click('Save', array('sf_sympal_comment' => array(
    'subject' => 'Test',
    'body' => 'Test',
  )), array('_with_csrf' => true))
;

Doctrine_Core::getTable('sfSympalBlogPost')->getConnection()->clear();

$browser->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('response')->begin()->
    matches('/comment_1/')->
    matches('/Posted on (.*) by Sympal Admin./')->
  end()
;