<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(7, new lime_output_color());

$dataGrid = sfSympalDataGrid::create('User', 'u')
  ->addColumn('u.id', 'renderer=test/data_grid_id')
  ->addColumn('u.username', 'method=getDataGridUsername')
  ->addColumn('u.name');

$t->is($dataGrid->getRows(), array(
  array(
    'id' => 'partial_1',
    'username' => 'method_admin',
    'name' => 'Sympal Admin'
  )
));

$t->is($dataGrid->getPagerHeader(), '<div class="sympal_pager_header"><h3>Showing 1 to 1 of 1 total results.</h3></div>');

$dataGrid = sfSympalDataGrid::create('ContentType', 'c')
  ->setMaxPerPage(1)
  ->setPage(1)
  ->configureColumn('c.id', 'renderer=test/data_grid_id');

$t->is($dataGrid->getRows(), array(
    array(
      'id' => 'partial_1',
      'name' => 'Page',
      'description' => 'The page content type is the default Sympal content type. It is a simple page that only consists of a title and body. The contents of the body are a sympal content slot that can be filled with your selected type of content.',
      'label' => 'Page',
      'plugin_name' => 'sfSympalPagesPlugin',
      'default_path' => '/pages/:slug',
      'layout' => NULL,
      'site_id' => '1',
      'slug' => 'page',
    )
  )
);

$dataGrid = sfSympalDataGrid::create('ContentType', 'c')
  ->setMaxPerPage(1)
  ->setRenderingModule('test');

$t->is($dataGrid->render(), 'Test');

$dataGrid = sfSympalDataGrid::create('ContentTemplate', 't')
  ->where('t.name = ?', 'Register')
  ->addColumn('t.name');

$rows = $dataGrid->getRows();
$t->is($rows[0]['name'], 'Register');

$dataGrid = sfSympalDataGrid::create('ContentTemplate', 't')
  ->where('t.name = ?', 'Register')
  ->addColumn('t.name', 'is_sortable=false label=Test');

$t->is($dataGrid->getColumnSortLink($dataGrid->getColumn('t.name')), 'Test');

$dataGrid = sfSympalDataGrid::create('ContentTemplate', 't')
  ->where('t.name = ?', 'Register')
  ->addColumn('t.name')
  ->isSortable(false);

$t->is($dataGrid->getColumnSortLink($dataGrid->getColumn('t.name')), 'Name');