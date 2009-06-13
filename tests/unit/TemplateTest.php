<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

$template = "
##test/test##
<?php echo 'Testing' ?>
";
$t->is(sfSympalTemplate::process($template), 'Test
Testing');

$template = "##test/email##";
$t->is(sfSympalTemplate::process($template, array(
  'variable' => 'Variable 1',
  'variable2' => 'Variable 2'
)), 'Subject
Body Variable 1 Variable 2');
