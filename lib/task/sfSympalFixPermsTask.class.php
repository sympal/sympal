<?php

class sfSympalFixPermsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'fix-perms';
    $this->briefDescription = 'Fixes sympal directory permissions';

    $this->detailedDescription = <<<EOF
The [sympal:fix-perms|INFO] task fixes directory permissions:

  [./symfony sympal:fix-perms|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $autoload = sfSimpleAutoload::getInstance();
    $autoload->reload();
    $autoload->saveCache();

    $items = array();

    if (file_exists(sfConfig::get('sf_upload_dir')))
    {
      $items[] = sfConfig::get('sf_upload_dir');
    }
    $items[] = sfConfig::get('sf_cache_dir');
    $items[] = sfConfig::get('sf_config_dir');
    $items[] = sfConfig::get('sf_data_dir').'/sql';
    $items[] = sfConfig::get('sf_log_dir');
    $items[] = sfConfig::get('sf_lib_dir');
    $items[] = sfConfig::get('sf_plugins_dir');
    $items[] = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'symfony';

    $dirFinder = sfFinder::type('dir');
    $fileFinder = sfFinder::type('file');
    foreach ($items as $item)
    {
      @$this->getFilesystem()->chmod($item, 0777);
      if (is_file($item))
      {
        continue;
      }
      @$this->getFilesystem()->chmod($dirFinder->in($items), 0777);
      @$this->getFilesystem()->chmod($fileFinder->in($items), 0666);
    }
  }
}