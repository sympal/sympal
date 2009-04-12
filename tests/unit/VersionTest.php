<?php
$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(9, new lime_output_color());

$content = Doctrine::getTable('Content')
  ->getTypeQuery('Page')
  ->andWhere('c.slug = ?', 'home')
  ->fetchOne();

$initialVersion = $content->version;
$content->slug = 'new-slug';
$content->save();

$t->is($initialVersion, 0);
$t->is($content->version, 1);

$changes = $content->getVersionChanges(1);
$t->is($changes['slug']['new_value'], 'new-slug');

$content->revert(1);
$t->is($content->slug, 'home');

$content->Page->Translation['en']->title = 'New Title';
$content->Page->save();

$changes = $content->Page->Translation['en']->getVersionChanges(1);
$t->is($changes['title']['new_value'], 'New Title');

$content->revert(1);
$t->is($content->slug, 'home');

$slots = $content->Slots;
$version = $slots[0]->Translation['en']['version'];
$value = $slots[0]->Translation['en']['value'];
$slots[0]->Translation['en']['value'] = 'New Value';
$slots[0]->save();

$t->is($slots[0]->Translation['en']['version'], $version + 1);

$changes = $slots[0]->Translation['en']->getVersionChanges($version + 1);
$t->is($changes['value']['new_value'], 'New Value');
$slots[0]->Translation['en']->undo();
$t->is($slots[0]->Translation['en']['value'], $value);