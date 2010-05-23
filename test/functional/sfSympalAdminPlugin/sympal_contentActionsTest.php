<?php

/**
 * Testing the sympal_content actions class
 */

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signinAsAdmin();

$types = Doctrine_Core::getTable('sfSympalContentType')->getAllContentTypes();
$pageType = Doctrine_Core::getTable('sfSympalContentType')->findOneBySlug('page');
$admin = Doctrine_Core::getTable('sfGuardUser')->findOneByUsername('admin');

$content = new sfSympalContent();
$content->setType($pageType);
$contentForm = new sfSympalContentForm($content);

$browser->info('1 - Test the new action')
  
  ->info('  1.1 - Going to the new action directly displays a menu of the content types')
  
  ->get('/admin/content/manage/new')
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content')
    ->isParameter('action', 'new')
  ->end()
  
  ->isForwardedTo('sympal_content', 'chooseNewType')
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', '/Add new content/')
    ->checkElement('ul.new-content-type li', 3)
  ->end()
  
  ->info(sprintf('  1.2 - Click on the %s new content', $pageType->name))
  ->click('Create '.$pageType->label)
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content')
    ->isParameter('action', 'create_type')
    ->isParameter('type', $pageType->id)
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', 'Create New '.$pageType->label)
    ->checkForm($contentForm)
  ->end()
  
  ->info('  1.3 - Create a new piece of content')
  ->click('Save')
  
  ->with('form')->begin()
    ->hasErrors(1)
    ->isError('TypeForm[title]')
  ->end()
  
  ->click('Save', array('sf_sympal_content' => array(
    'page_title' => 'test page title',
    'TypeForm' => array(
      'title' => 'New page title',
    )
  )))
;
$createdContent = Doctrine_Core::getTable('sfSympalContent')->findOneByPageTitle('test page title');

$browser
  ->info('  1.4 - See that the sfSympalContent and sfSympalPage entries were created')
  ->with('doctrine')->begin()
    ->check('sfSympalContent', array(
      'content_type_id' => $pageType->id,
      'created_by_id'   => $admin->id,
      'page_title'      => 'test page title',
    ))
    ->check('sfSympalPage', array(
      'content_id'  => $createdContent->id,
      'title'       => 'New page title',
    ))
  ->end()
;
$createdContent->delete(); // refresh

$contentTypeCount = count($types);
$home = Doctrine_Core::getTable('sfSympalContent')->findOneBySlug('home');
$browser->info('2 - Test the content type index, click through the types and edit')
  ->info('  2.1 - Start at the content types index page')
  ->get('/admin/content')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content')
    ->isParameter('action', 'content_types_index')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->info(sprintf('  2.2 There should be %s rows, one for each content type', $contentTypeCount))
    ->checkElement('#sf_admin_content .sf_admin_list tbody tr', $contentTypeCount)
  ->end()
  
  ->info('  2.3 - click the "Page" content type to see its index')
  ->click('Page')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content')
    ->isParameter('action', 'list_type')
    ->isParameter('type', 'page')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->info('  2.4 - There should be 3 pages: Home, Register, Sample Page')
    ->checkElement('#sf_admin_content .sf_admin_list tbody tr', 3)
  ->end()
  
  ->info('  2.5 - Click the "Page" entry, takes you to view that page')
  ->click('Home')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content_renderer')
    ->isParameter('action', 'index')
    ->isParameter('sympal_content_type', 'sfSympalPage')
    ->isParameter('sympal_content_id', $home->id)
  ->end()
  
  ->info('  2.6 - Go back to the page index and click edit')
  ->get('/admin/content/manage/type/page')
  ->click('Edit')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content')
    ->isParameter('action', 'edit')
    ->isParameter('id', $home->id)
  ->end()
  
  ->info('  2.7 - Goto the content index page, it will show the page type')
  ->get('/admin/content/manage')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content')
    ->isParameter('action', 'index')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', '/Manage Page Content/')
  ->end()
  
  ->info('  2.8 - goto the "view" action of a page, redirects you to the frontend')
  ->get('/admin/content/manage/'.$home->id.'/view')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content')
    ->isParameter('action', 'view')
  ->end()
  
  ->with('response')->begin()
    ->isRedirected(true)
  ->end()
  
  ->followRedirect()
  
  ->with('request')->begin()
    ->isParameter('action', 'index')
    ->isParameter('sympal_content_type', 'sfSympalPage')
    ->isParameter('sympal_content_id', $home->id)
  ->end()
;

$browser->info('3 - ');