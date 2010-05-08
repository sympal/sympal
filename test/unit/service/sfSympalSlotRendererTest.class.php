<?php
$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
$t = new lime_test(7);

$sympal_configuration = $plugin_configuration->getSympalConfiguration();

$content = sfSympalContent::createNew('sfSympalPage');
$slotRenderer = new sfSympalSlotRenderer($sympal_configuration);

$t->info('1 - Test renderSlotByName()');

$t->is($content->getEditableSlotsExistOnPage(), false, '->getEditableSlotsExistOnPage() on Content begins as false');
$value = $slotRenderer->renderSlotByName('title', $content, array('edit_mode' => 'test', 'default_value' => 'testing default'));
$t->is($content->getEditableSlotsExistOnPage(), true, '->getEditableSlotsExistOnPage() is true after rendering a slot');

$t->is($slotRenderer->getOption('fake', 'default'), 'default', '->getOption() returns the default value if the option does not exist');
$t->is($slotRenderer->getOption('edit_mode', 'default'), 'test', '->getOption() returns the correct option value');
$t->is($value, 'testing default', 'Renders the default value we passed via default_value');

$content->title = 'testing';

$value = $slotRenderer->renderSlotByName('title', $content, array('edit_mode' => 'test'));
$t->is($value, 'testing', 'Renders "testing" once we set its value to testing');


$t->info('2 - Test ->renderSlot()');

$slot = new sfSympalContentSlot();
$slot->name = 'body';
$slot->is_column = false;
$slot->value = 'testing body value';
$slot->setContentRenderedFor($content);

$value = $slotRenderer->renderSlot($slot);
$t->is($value, 'testing body value', '->renderSlot() returns the correct value');