<?php

class sfSympalMail
{
  protected 
    $_mailer,
    $_message,
    $_emailAddress,
    $_fromAddress;

  public function __construct()
  {
    $this->_mailer = new Swift(new Swift_Connection_NativeMail());
  }

  public function setEmailAddress($emailAddress)
  {
    $this->_emailAddress = $emailAddress;
  }

  public function setFromAddress($fromAddress)
  {
    $this->_fromAddress = $fromAddress;
  }

  public function setMessage($subject, $body, $type = 'text/html')
  {
    $this->_message = new Swift_Message($subject, $body, $type);

    return $this->_message;
  }

  public function getMessage()
  {
    return $this->_message;
  }

  public function send($emailAddress = null, $fromAddress = null)
  {
    $emailAddress = $emailAddress ? $emailAddress:$this->_emailAddress;
    $fromAddress = $fromAddress ? $fromAddress:$this->_fromAddress;

    $this->_mailer->send($this->_message, $emailAddress, $fromAddress);
    $this->_mailer->disconnect();
  }

  public function __get($name)
  {
    return $this->_mailer->$name;
  }

  public function __set($name, $value)
  {
    $this->_mailer->$name = $value;
  }

  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->_mailer, $method), $arguments);
  }
}