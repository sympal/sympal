<?php

include dirname(__FILE__).'/../bootstrap/unit.php';

$h = new lime_harness();
$h->register(sfFinder::type('file')->name('*Test.php')->in(dirname(__FILE__).'/..'));

$ret = $h->run() ? 0 : 1;

if (isset($argv[1]) && strpos($argv[1],'--xml=') === 0)
{
  $file = str_replace('--xml=', '', $argv[1]);
  file_put_contents($file, $h->to_xml());
}

exit($ret);