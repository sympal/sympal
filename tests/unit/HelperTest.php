<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

$browser = new sfTestFunctional(new sfBrowser());
$browser->get('/');

$menuItem = Doctrine::getTable('MenuItem')->findOneBySlug('about');
$t->is($menuItem->getBreadcrumbs()->getPathAsString(), 'Home / About');
$t->is(get_sympal_breadcrumbs($menuItem), '<div id="sympal_breadcrumbs"><ul id="breadcrumbs-menu"><li id="breadcrumbs-home"><a href="/index.php/">Home</a></li><li id="breadcrumbs-about">About</li></ul></div>');

$breadcrumbs = array(
  'Home' => '@homepage',
  'About' => 'http://www.google.com',
  'Jonathan H. Wage' => 'http://www.jwage.com'
);
$t->is(get_sympal_breadcrumbs($breadcrumbs), '<div id="sympal_breadcrumbs"><ul id="breadcrumbs-menu"><li id="breadcrumbs-home"><a href="/index.php/">Home</a></li><li id="breadcrumbs-about"><a href="http://www.google.com">About</a></li><li id="breadcrumbs-jonathan-h-wage">Jonathan H. Wage</li></ul></div>');

$t->is(get_sympal_yui_path('css', 'menu/assets/skins/sam/menu'), 'http://yui.yahooapis.com/2.7.0/build/menu/assets/skins/sam/menu.css');
$t->is(get_sympal_yui_path('js', 'animation/animation'), 'http://yui.yahooapis.com/2.7.0/build/animation/animation.js');

$orig = sfConfig::get('sf_debug');
sfConfig::set('sf_debug', false);
$t->is(get_sympal_yui_path('js', 'animation/animation'), 'http://yui.yahooapis.com/2.7.0/build/animation/animation-min.js');
sfConfig::set('sf_debug', $orig);