<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../../../bootstrap/unit.php');

$t = new lime_test(2);

$slugs = array(
  '/other/files sympal information.txt' => 'other-files-sympal-information',
  '/screens sympalphp.png' => 'screens-sympalphp',
);

foreach ($slugs as $in => $out)
{
  $t->is(sfSympalasset::slugBuilder($in), $out, sprintf('::slugBuilder() - "%s" becomes "%s"', $in, $out));
}