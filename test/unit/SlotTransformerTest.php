<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

// mock class
class sfSympalContentSlotTransformerTest extends sfSympalContentSlotTransformer
{
  public function getProp($prop)
  {
    return $this->$prop;
  }
  
  public function getTransformerCallbacksTest()
  {
    return parent::getTransformerCallbacks();
  }
}

$content = Doctrine_Query::create()->from('sfSympalContent')->fetchOne();


$contentSlot = $content->getOrCreateSlot('body', array(
  'type' => 'Markdown',
));
$contentSlot->value = '
Some Markdown Content
=====================';
$contentSlot->save();

$transformer = new sfSympalContentSlotTransformerTest($contentSlot);
$t->is($transformer->getContentSlot(), $contentSlot, '->getContentSlot() returns the sfSympalContentSlot object');

/*
 * Transformer callbacks
 */
$t->info('1 - Test the transformer callbacks');
$markdownConfig = sfSympalConfig::get('content_slot_types', 'Markdown');

$callbacks = $transformer->getTransformerCallbacksTest();
$t->is(count($callbacks), 2, '->getTransformerCallbacks() returns two callbacks for Markdown slot type');

$t->info('  1.1 - Change the Markdown slot type to have only one transformer');
$markdownConfig['transformers'] = array('replacer');
sfSympalConfig::set('content_slot_types', 'Markdown', $markdownConfig);

$callbacks = $transformer->getTransformerCallbacksTest();
$t->is(count($callbacks), 1, '->getTransformerCallbacks() now returns only the 1 transformer callback');

$t->info('  1.2 - Change the Markdown slot type nack to 2 transformers');
$markdownConfig['transformers'] = array('replacer', 'markdown');
sfSympalConfig::set('content_slot_types', 'Markdown', $markdownConfig);

$callbacks = $transformer->getTransformerCallbacksTest();
$t->is($callbacks[0], array('sfSympalContentSlotReplacer', 'replace'), 'The first transformer matches the correct callback');

$t->info('  1.3 - Using an invalid transformer throws an exception');
$markdownConfig['transformers'] = array('fake_transformer');
sfSympalConfig::set('content_slot_types', 'Markdown', $markdownConfig);

try
{
  $transformer->getTransformerCallbacksTest();
  $t->fail('Exception not thrown');
}
catch (sfException $e)
{
  $t->pass('Exception thrown');
}