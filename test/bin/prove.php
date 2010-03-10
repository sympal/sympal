<?php

include dirname(__FILE__).'/../bootstrap/unit.php';

$h = new lime_harness();
$h->register(sfFinder::type('file')->name('*Test.php')->in(dirname(__FILE__).'/..'));

exit($h->run() ? 0 : 1);