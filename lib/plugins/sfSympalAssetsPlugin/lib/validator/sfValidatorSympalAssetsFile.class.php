<?php

/*
 * This file is part of the sympal_assets package.
 *
 * (c) 2009 Vincent Agnano <vincent.agnano@particul.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorSympalAssets validates a selected file.
 *
 * @package    sfSympalAssetsPlugin
 * @subpackage validator
 * @author     Vincent Agnano <vincent.agnano@particul.es>
 */
class sfValidatorSympalAssetsFile extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * root_dir: The upload root directory
   *
   * Available error codes:
   *
   *  * root_dir
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->setMessage('invalid', '"%file%" is not a valid file path.');
    $this->addOption('type');
    $this->addMessage('type', 'the file must be a "%type%"');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $full_path = sfConfig::get('sf_web_dir').'/'.$value;
    if(!is_file($full_path))
    {
      throw new sfValidatorError($this, 'invalid', array('file' => $value));
    }
    
    return $value;
  }
}
