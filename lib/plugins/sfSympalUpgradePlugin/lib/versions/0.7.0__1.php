<?php

class sfSympalUpgrade0_7_0__1 extends sfSympalVersionUpgrade
{
  public function _doUpgrade()
  {
    $this->logSection('sympal', 'Removing BaseFormDoctrineSympal inheritance');

    $find = 'abstract class BaseFormDoctrine extends BaseFormDoctrineSympal';
    $replace = 'abstract class BaseFormDoctrine extends sfFormDoctrine';

    $path = sfConfig::get('sf_lib_dir').'/form/doctrine/BaseFormDoctrine.class.php';
    file_put_contents($path, str_replace($find, $replace, file_get_contents($path)));
  }
}