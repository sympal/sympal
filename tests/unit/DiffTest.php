<?php

require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

$from = 'This is the from text';
$to = 'This is the to text that we are going to run a diff against.';

$diff = new sfSympalDiff($from, $to);

$t->is((string) $diff, "This is the <del>from</del><ins>to</ins> text<ins> that we are going to run a diff against.</ins>\n");