<?php

require_once dirname(__FILE__).'/sfTaskExtraGeneratorBaseTask.class.php';

/**
 * Generates a new front controller.
 *
 * @package     sfGenerateControllerTaskPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfGenerateControllerTask.class.php 25037 2009-12-07 19:45:39Z Kris.Wallsmith $
 */
class sfGenerateControllerTask extends sfTaskExtraGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('app', sfCommandArgument::REQUIRED, 'The controller application name'),
      new sfCommandArgument('env', sfCommandArgument::OPTIONAL, 'The controller environment name', 'dev'),
    ));

    $this->addOptions(array(
      new sfCommandOption('filename', null, sfCommandOption::PARAMETER_REQUIRED, 'Filename for the controller'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Controller debug mode'),
      new sfCommandOption('allowed-ip', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'Restrict traffic by IP address'),
      new sfCommandOption('check-server', null, sfCommandOption::PARAMETER_NONE, 'Check for configuration variables in the $_SERVER array'),
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Overwrite any existing file of the same name'),
    ));

    $this->aliases = array('init-controller');
    $this->namespace = 'generate';
    $this->name = 'controller';

    $this->briefDescription = 'Generates a new front controller';

    $this->detailedDescription = <<<EOF
The [generate:controller|INFO] task creates a front controller in the [web/|COMMENT] 
directory:

  [./symfony generate:controller frontend dev --debug|INFO]

Traffic to this controller can be restricted by IP address by using the 
[allowed-ip|COMMENT] option:

  [./symfony generate:controller frontend dev --allowed-ip="127.0.0.1" --debug|INFO]

This common use case can also be accomplished using the [localhost|COMMENT] shortcut:

  [./symfony generate:controller frontend dev --allowed-ip="localhost" --debug|INFO]

If you want to use a filename other than the symfony default filename, use 
the [filename|COMMENT] option:

  [./symfony generate:controller frontend prod --filename="index"|INFO]

The controller can be configured to listen to the server for configuration
variables using the [check-server|COMMENT] option:

  [./symfony generate:controller frontend prod --check-server|INFO]

This option will add logic to your controller to first look for 
[SF_APPLICATION|COMMENT], [SF_ENVIRONMENT|COMMENT] and [SF_DEBUG|COMMENT] keys in the [\$ SERVER|COMMENT] array. If
these keys are not found, the command arguments are used.

  [./symfony generate:controller frontend prod --filename="index" --check-server --force|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['app'];
    $env = $arguments['env'];

    // validate the environment name
    if (!preg_match('/^[a-zA-Z0-9_\x7f-\xff]*$/', $env))
    {
      throw new sfCommandException(sprintf('The environment "%s" is invalid.', $env));
    }

    // determine filename for the controller
    if ($options['filename'])
    {
      $filename = $options['filename'];

      // validate the filename
      if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $filename))
      {
        throw new sfCommandException(sprintf('The filename "%s" is invalid.', $filename));
      }

      $filename .= '.php';
    }
    else
    {
      $filename = $app.'_'.$env.'.php';
    }

    // ip restriction
    $ipCheck = null;
    if ($options['allowed-ip'])
    {
      $ipArray = preg_replace('/\s+/', ' ', var_export($this->mapAllowedIps($options['allowed-ip']), true));
      $ipCheck = <<<EOF

// access to this controller is restricted by remote ip address
if (!in_array(@\$_SERVER['REMOTE_ADDR'], $ipArray))
{
  header('HTTP/1.1 403 Forbidden');
  echo 'Access to this file is restricted. Please refer to <code>$filename</code> for more information.';
  die;
}

EOF;
    }

    if (file_exists(sfConfig::get('sf_web_dir').'/'.$filename))
    {
      if ($options['force'])
      {
        $this->getFilesystem()->remove(sfConfig::get('sf_web_dir').'/'.$filename);
      }
      else
      {
        throw new InvalidArgumentException(sprintf('A "%s" controller already exists. Use the --force option to overwrite.', $filename));
      }
    }

    $this->getFilesystem()->copy(dirname(__FILE__).'/skeleton/controller/index.php', sfConfig::get('sf_web_dir').'/'.$filename);
    if ($options['check-server'])
    {
      $this->getFilesystem()->replaceTokens(sfConfig::get('sf_web_dir').'/'.$filename, '##', '##', array(
        'APP_NAME'    => "\n  ".'isset($_SERVER[\'SF_APPLICATION\']) ? $_SERVER[\'SF_APPLICATION\'] : '.var_export($app, true),
        'ENVIRONMENT' => "\n  ".'isset($_SERVER[\'SF_ENVIRONMENT\']) ? $_SERVER[\'SF_ENVIRONMENT\'] : '.var_export($env, true),
        'IS_DEBUG'    => "\n  ".'isset($_SERVER[\'SF_DEBUG\']) ? (boolean) $_SERVER[\'SF_DEBUG\'] : '.($options['debug'] ? 'true' : 'false'),
        'IP_CHECK'    => $ipCheck,
      ));
    }
    else
    {
      $this->getFilesystem()->replaceTokens(sfConfig::get('sf_web_dir').'/'.$filename, '##', '##', array(
        'APP_NAME'    => var_export($app, true),
        'ENVIRONMENT' => var_export($env, true),
        'IS_DEBUG'    => $options['debug'] ? 'true' : 'false',
        'IP_CHECK'    => $ipCheck,
      ));
    }
  }

  /**
   * Map IP aliases.
   * 
   * @param   array $aliased
   * 
   * @return  array
   */
  protected function mapAllowedIps($aliased)
  {
    $mapped = array();
    foreach ($aliased as $alias)
    {
      switch ($alias)
      {
        case 'localhost':
          $mapped[] = '127.0.0.1';
          $mapped[] = '::1';
          break;

        default:
          $mapped[] = $alias;
      }
    }

    return $mapped;
  }
}
