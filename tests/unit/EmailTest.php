<?php

require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

class sfSympalMailTest extends sfSympalMail
{
  public function send($emailAddress = null, $fromAddress = null)
  {
    
  }
}

$email = new sfSympalMailTest();
$email->setEmailAddress('jonwage@gmail.com');
$email->setFromAddress('admin@sympalphp.org');
$email->setMessage('Test subject', 'Test body');

$t->is($email->getMessage() instanceof Swift_Message, true);
$t->is($email->getMessage()->getSubject(), 'Test subject');
$t->is($email->getMessage()->getBody(), 'Test body');

// forwarding
$t->is($email->getOptions(), 0);
$t->is($email->connection instanceof Swift_Connection_NativeMail, true);

class TestConn extends Swift_Connection_NativeMail
{
  
}

$conn = new TestConn();
$email->connection = $conn;
$t->is($email->connection instanceof TestConn, true);