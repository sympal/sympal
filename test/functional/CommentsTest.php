<?php

/**
 * Functional testing for sfSympalCommentsPlugin
 * 
 * @author      Jon Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-01-22
 * @version     svn:$Id$ $Author$
 */
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->setTester('doctrine', 'sfTesterDoctrine');

$goodValues = array(
  'website' => 'http://www.sympalphp.org',
  'body'    => 'Hey, nice test comment!',
);
$browser->info('1 - Sign in and post some comments')->
  signInAsAdmin()->
  
  get('/blog-post/sample_blogpost')->
  
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('#sympal_comments', true)->
    checkForm('sfSympalNewCommentForm')->
  end()->
  
  info('  1.1 - Submit a bad form, count the errors')->
  
  click('Save Comment', array('sf_sympal_comment' => array(
    'website' => 'bad site',
  )))->
  
  with('form')->begin()->
    hasErrors(2)->
    isError('body', 'required')->
    isError('website', 'invalid')->
  end()->
  
  click('Save Comment', array('sf_sympal_comment' => $goodValues))->
  
  with('form')->begin()->
    hasErrors(0)->
  end()->
  
  with('request')->begin()->
    isParameter('module', 'sympal_comments')->
    isParameter('action', 'create')->
  end()
;

Doctrine_Core::getTable('sfSympalBlogPost')->getConnection()->clear();

$sympalAdmin = Doctrine_Core::getTable('sfGuardUser')->findOneByUsername('admin');
$browser->
  with('response')->isRedirected()->followRedirect()->
  
  with('response')->begin()->
    matches('/comment_1/')->
    matches('/Posted on (.*) by/')->
    info('  1.2 - Check that the website is surrounded by a nofollow')->
    checkElement('a[rel="nofollow"][href="'.$goodValues['website'].'"]', '/Sympal Admin/')->
  end()->
  
  with('doctrine')->begin()->
    check('sfSympalComment', array_merge(
      $goodValues,
      array('user_id' => $sympalAdmin->id)
    ))->
  end()
;

$goodValues = array(
  'email_address' => 'ryan@thatsquality.com',
  'body'  => 'just another test comment',
);
$browser->info('2 - Post some comments anonymously')->
  signOut()->
  
  get('/blog-post/sample_blogpost')->
  
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('#sympal_comments', true)->
    checkForm('sfSympalNewCommentForm')->
  end()->
  
  info('  2.1 - Submit a bad form, count the errors')->
  
  click('Save Comment')->
  
  with('form')->begin()->
    hasErrors(2)->
    isError('body', 'required')->
    isError('email_address', 'required')->
  end()->
  
  click('Save Comment', array('sf_sympal_comment' => $goodValues))->
  
  with('form')->begin()->
    hasErrors(0)->
  end()->
  
  with('request')->begin()->
    isParameter('module', 'sympal_comments')->
    isParameter('action', 'create')->
  end()
;

Doctrine_Core::getTable('sfSympalBlogPost')->getConnection()->clear();

$browser->
  with('response')->isRedirected()->followRedirect()->
  
  with('response')->begin()->
    matches('/comment_2/')->
    matches('/Posted on (.*) by[\s]+ anonymous\./')->
  end()->
  
  with('doctrine')->begin()->
    check('sfSympalComment', $goodValues)->
  end()
;