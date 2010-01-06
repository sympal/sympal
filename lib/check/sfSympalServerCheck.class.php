<?php

class sfSympalServerCheck
{
  protected $_checks;

  const WARNING = 1;
  const ERROR = 2;

  public function __construct()
  {
    $this->_checks = $this->createChecks();
  }

  protected function createChecks()
  {
    return array(
      'server' => array(
        new sfSympalServerCheckUnit('unix', DIRECTORY_SEPARATOR == '/', true)
      ),
      'symfony' => array(
        new sfSympalServerCheckUnit('version', SYMFONY_VERSION, '1.3.0', self::ERROR)
      ),
      'php config' => array(
        new sfSympalServerCheckUnit('version', phpversion(), '5.2.4', self::ERROR),
        new sfSympalServerCheckUnit('memory', ini_get('memory_limit'), '48M', self::ERROR),
        new sfSympalServerCheckUnit('magic quote gpc', ini_get('magic_quotes_gpc'), false),
        new sfSympalServerCheckUnit('upload max filesize', ini_get('upload_max_filesize'), '2M'),
        new sfSympalServerCheckUnit('post max size', ini_get('post_max_size'), '2M'),
        new sfSympalServerCheckUnit('register globals', ini_get('register_globals'), false),
        new sfSympalServerCheckUnit('session auto_start', ini_get('session.auto_start'), false),
        new sfSympalServerCheckUnit('mbstring', extension_loaded('mbstring'), true),
        new sfSympalServerCheckUnit('utf8_decode()', function_exists('utf8_decode'), true)
      ),
      'php_extensions' => array(
        new sfSympalServerCheckUnit('pdo', extension_loaded('pdo'), true, self::ERROR),
        new sfSympalServerCheckUnit('pdo_mysql', extension_loaded('pdo_mysql'), true),
        new sfSympalServerCheckUnit('pdo_pgsql', extension_loaded('pdo_pgsql'), true),
        new sfSympalServerCheckUnit('pdo_sqlite', extension_loaded('pdo_sqlite'), true),
        new sfSympalServerCheckUnit('json', extension_loaded('json') ? phpversion('json') : false, '1.0', self::ERROR),
        new sfSympalServerCheckUnit('gd', extension_loaded('gd'), true, self::ERROR),
        new sfSympalServerCheckUnit('ctype', extension_loaded('ctype'), true, self::ERROR),
        new sfSympalServerCheckUnit('dom', extension_loaded('dom'), true, self::ERROR),
        new sfSympalServerCheckUnit('iconv', extension_loaded('iconv'), true, self::ERROR),
        new sfSympalServerCheckUnit('pcre', extension_loaded('pcre'), true, self::ERROR),
        new sfSympalServerCheckUnit('reflection', extension_loaded('Reflection'), true, self::ERROR),
        new sfSympalServerCheckUnit('simplexml', extension_loaded('SimpleXML'), true, self::ERROR),
        new sfSympalServerCheckUnit('apc', function_exists('apc_store') ? phpversion('apc') : false, '3.0'),
        new sfSympalServerCheckUnit('mbstring', extension_loaded('mbstring'), true),
        new sfSympalServerCheckUnit('curl', extension_loaded('curl'), true),
        new sfSympalServerCheckUnit('xml', extension_loaded('xml'), true),
        new sfSympalServerCheckUnit('xsl', extension_loaded('xsl'), true)
      )
    );
  }

  public function getCheckspace($name)
  {
    return $this->_checks[$name];
  }

  public function getChecks()
  {
    return $this->_checks;
  }
}