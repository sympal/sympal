<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(14, new lime_output_color());

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

// class for test transformer callbacks
class myUnitTestTransformer
{
  public static function transform1($content, sfSympalContentSlotTransformer $transformer)
  {
    return 'testing';
  }
  
  public static function transform2($content, sfSympalContentSlotTransformer $transformer)
  {
    return str_replace('Markdown', 'Markup', $content);
  }
  
  public static function transform3($content, sfSympalContentSlotTransformer $transformer)
  {
    $arg1 = 'HT';
    $arg2 = 'ML';
    $transformer->addTokenCallback('%language%', array('myUnitTestTransformer', 'languageCallback'), array($arg1, $arg2));
    
    return str_replace('Markdown', '%language%', $content);
  }
  
  public static function languageCallback($arg1, $arg2)
  {
    return $arg1.$arg2;
  }
}

$content = Doctrine_Query::create()->from('sfSympalContent')->fetchOne();


$contentSlot = $content->getOrCreateSlot('body', array(
  'type' => 'Markdown',
));
$contentSlot->value = '__Some Markdown Content__';
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
$t->is($callbacks[0], array('sfSympalContentSlotReplacer', 'transformSlotContent'), 'The first transformer matches the correct callback');

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


/*
 * Test the transformation process
 */

$t->info('2 - Test the transformation process');
$t->info('  2.1 - Setup a unit_test transformer');
$markdownConfig['transformers'] = array('unit_test');
sfSympalConfig::set('content_slot_types', 'Markdown', $markdownConfig);

$t->info('  2.2 - First transformer should always return static text "testing"');
sfSympalConfig::set('slot_transformers', 'unit_test', array('myUnitTestTransformer', 'transform1'));
test_transformer_return($t, $transformer, 'testing', array(), 'testing');

$t->info('  2.3 - Transformer will change "Markdown" to "Markup"');
sfSympalConfig::set('slot_transformers', 'unit_test', array('myUnitTestTransformer', 'transform2'));
test_transformer_return($t, $transformer, '__Some Markup Content__', array(), '__Some Markup Content__');


$t->info('  2.4 - Transformer will include a callback wildcard');
sfSympalConfig::set('slot_transformers', 'unit_test', array('myUnitTestTransformer', 'transform3'));
$tokenCallbacks = array(
  '%language%' => array(
    'callback'  => array('myUnitTestTransformer', 'languageCallback'),
    'args'      => array('HT', 'ML')
  ),
);
test_transformer_return($t, $transformer, '__Some %language% Content__', $tokenCallbacks, '__Some HTML Content__');


// tests the return values of a run in the transformer
function test_transformer_return(lime_test $t, sfSympalContentSlotTransformerTest $transformer, $transformedContent, $tokenCallbacks, $result)
{
  $transformerResult = $transformer->render();
  $t->is($transformer->getProp('_transformedContent'), $transformedContent, sprintf('->_transformedContent set to "%s"', $transformedContent));
  $t->is($transformer->getProp('_tokenCallbacks'), $tokenCallbacks, '->_tokenCallbacks matches');
  $t->is($transformerResult, $result, sprintf('The end result is "%s"', $result));
}