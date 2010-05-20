<?php

function sympal_cleanup()
{
  sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/cache');
  sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/log');
  rmdir(dirname(__FILE__).'/../fixtures/project/cache');
  rmdir(dirname(__FILE__).'/../fixtures/project/log');
  
  $currentDir = getcwd();
  chdir(dirname(__FILE__).'/../fixtures/project');
  exec('git checkout *');
  chdir($currentDir);
  
  mkdir(dirname(__FILE__).'/../fixtures/project/cache', 0777, true);
  mkdir(dirname(__FILE__).'/../fixtures/project/log', 0777, true);

  @unlink(dirname(__FILE__) . '/../fixtures/project/data/test.sqlite');
  

}

copy(dirname(__FILE__).'/../fixtures/project/data/fresh_test_db.sqlite', dirname(__FILE__).'/../fixtures/project/data/test.sqlite');
copy(dirname(__FILE__).'/../fixtures/project/config/fresh_app.yml', dirname(__FILE__).'/../fixtures/project/config/app.yml');
register_shutdown_function('sympal_cleanup');

function refresh_assets()
{
  $uploadDir = dirname(__FILE__).'/../fixtures/project/web/uploads';
  sfToolkit::clearDirectory($uploadDir);

  $finder = new sfFinder();
  $filesystem = new sfFilesystem();
  $filesystem->mirror(dirname(__FILE__).'/../fixtures/assets', $uploadDir, $finder);
}