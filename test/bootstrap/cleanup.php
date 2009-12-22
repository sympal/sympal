<?php

function sympal_cleanup()
{
  sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/cache');
  sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/log');

  @unlink(dirname(__FILE__) . '/../fixtures/project/data/test.sqlite');
  @unlink(sfConfig::get('sf_config_dir').'/app.yml');
}

copy(dirname(__FILE__).'/../fixtures/project/data/fresh_test_db.sqlite', dirname(__FILE__).'/../fixtures/project/data/test.sqlite');
register_shutdown_function('sympal_cleanup');