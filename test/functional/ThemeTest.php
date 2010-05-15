<?php

/**
 * A functional test for sfSympalActions - as close to a unit test as
 * we could get
 * 
 * @package     sfSympalPlugin 
 * @subpackage  test
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());



$browser->info('1 - Test the theme setting for Site, Content, Content Type');

  $browser->info('  1.1 - Theme test #1');
  testTheme($browser, 'default', 'The theme is "default", set by the global default_theme config');

  $browser->info('  1.2 - Theme test #2');
  testTheme($browser, 'wordpress_default', 'The Site theme "wordpress_default" is used', null, null, 'wordpress_default');

  $browser->info('  1.3 - Theme test #3');
  testTheme($browser, 'wordpress_default', 'The Type theme "wordpress_default" is used', null, 'wordpress_default', null);

  $browser->info('  1.4 - Theme test #4');
  testTheme($browser, 'wordpress_default', 'The Content theme "wordpress_default" is used', 'wordpress_default', null, null);

  $browser->info('  1.5 - Theme test #5');
  testTheme($browser, 'sympal', 'The Type theme "sympal" takes precedence over Site theme', null, 'sympal', 'wordpress_default');

  $browser->info('  1.6 - Theme test #6');
  testTheme($browser, 'default', 'The Type theme "default" takes precedence over everything', 'default', 'sympal', 'wordpress_default');

// used to test that the proper theme is set for the given Content, Type, and Site theme conditions
function testTheme(sfSympalTestFunctional $browser, $expected, $explanation, $contentTheme = null, $contentTypeTheme = null, $siteTheme = null)
{
  $browser->info('... setting up theme data');
  $content = Doctrine_Core::getTable('sfSympalContent')->findOneBySlug('sample-page');
  $content->theme = $contentTheme;
  $content->save();

  $content->Type->theme = $contentTypeTheme;
  $content->Type->save();

  $content->Site->theme = $siteTheme;
  $content->Site->save();
  
  $browser
    ->info('      Content     = ' . (($contentTheme) ? $contentTheme : '(none)'))
    ->info('      ContentType = ' . (($contentTypeTheme) ? $contentTypeTheme : '(none)'))
    ->info('      Site        = ' . (($siteTheme) ? $siteTheme : '(none)'))
    
    ->get('/sample-page')
    
    ->info('    '.$explanation)
    ->with('theme')->begin()
      ->isCurrentTheme($expected)
    ->end()
  ;

  // spacing after the test
  $browser->info('');
  $browser->info('');
}



