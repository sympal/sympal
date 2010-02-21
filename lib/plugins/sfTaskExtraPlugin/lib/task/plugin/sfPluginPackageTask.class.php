<?php

require_once dirname(__FILE__).'/sfTaskExtraPluginBaseTask.class.php';

/**
 * Packages a plugin.
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfPluginPackageTask.class.php 26200 2010-01-04 23:50:59Z Kris.Wallsmith $
 */
class sfPluginPackageTask extends sfTaskExtraPluginBaseTask
{
  protected
    $pluginDir   = null,
    $interactive = true;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('plugin', sfCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('plugin-version', null, sfCommandOption::PARAMETER_REQUIRED, 'The plugin version'),
      new sfCommandOption('plugin-stability', null, sfCommandOption::PARAMETER_REQUIRED, 'The plugin stability'),
      new sfCommandOption('non-interactive', null, sfCommandOption::PARAMETER_NONE, 'Skip interactive prompts'),
      new sfCommandOption('nocompress', null, sfCommandOption::PARAMETER_NONE, 'Do not compress the package'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'plugin';
    $this->name = 'package';

    $this->briefDescription = 'Create a plugin PEAR package';

    $this->detailedDescription = <<<EOF
The [plugin:package|INFO] task creates a plugin PEAR package:

  [./symfony plugin:package sfExamplePlugin|INFO]

If your plugin includes a package.xml file, it will be used. If not, the task
will look for a package.xml.tmpl file in your plugin and use either that or a
default template to dynamically generate your package.xml file.

You can either edit your plugin's package.xml.tmpl file or use the
[--plugin-version|COMMENT] or [--plugin-stability|COMMENT] options to set the
release version and stability, respectively:

  [./symfony plugin:package sfExamplePlugin --plugin-version=0.5.0 --plugin-stability=alpha|INFO]

To disable any interactive prompts in the packaging process, include the
[--non-interactive|COMMENT] option:

  [./symfony plugin:package sfExamplePlugin --non-interactive|INFO]

To disable compression of the package tar, use the [--nocompress|COMMENT]
option:

  [./symfony plugin:package sfExamplePlugin --nocompress|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->checkPluginExists($arguments['plugin']);

    $this->pluginDir = sfApplicationConfiguration::getActive()->getPluginConfiguration($arguments['plugin'])->getRootDir();
    $this->interactive = !$options['non-interactive'];

    $cleanup = array();

    if (!file_exists($this->pluginDir.'/package.xml'))
    {
      $cleanup['temp_files'] = array();
      foreach (sfFinder::type('dir')->in($this->pluginDir) as $dir)
      {
        if (!sfFinder::type('any')->maxdepth(0)->in($dir))
        {
          $this->getFilesystem()->touch($file = $dir.'/.sf');
          $cleanup['temp_files'][] = $file;
        }
      }

      $cleanup['package_file'] = true;
      $this->generatePackageFile($arguments, $options);
    }

    $cwd = getcwd();
    chdir($this->pluginDir);

    $this->getPluginManager()->configure();

    require_once 'PEAR/Packager.php';
    $packager = new PEAR_Packager();
    $package = $packager->package($this->pluginDir.'/package.xml', !$options['nocompress']);

    chdir($cwd);

    if (PEAR::isError($package))
    {
      if (isset($cleanup['package_file']))
      {
        $cleanup['package_file'] = '.error';
      }
      $this->cleanup($cleanup);

      throw new sfCommandException($package->getMessage());
    }

    $this->cleanup($cleanup);
  }

  /**
   * Cleanup files.
   * 
   * Available options:
   * 
   *  * package_file
   * 
   * @param array $options
   */
  protected function cleanup(array $options = array())
  {
    $options = array_merge(array(
      'package_file' => false,
      'temp_files'   => array(),
    ), $options);

    if ($extension = $options['package_file'])
    {
      if (is_string($extension))
      {
        $this->getFilesystem()->copy($this->pluginDir.'/package.xml', $this->pluginDir.'/package.xml'.$extension, array('override' => true));
      }

      $this->getFilesystem()->remove($this->pluginDir.'/package.xml');
    }

    foreach ($options['temp_files'] as $file)
    {
      $this->getFilesystem()->remove($file);
    }
  }

  /**
   * Generates a package.xml file in the plugin directory.
   * 
   * @todo Move this into its own task
   */
  protected function generatePackageFile(array $arguments, array $options)
  {
    if (!file_exists($templatePath = $this->pluginDir.'/package.xml.tmpl'))
    {
      $templatePath = dirname(__FILE__).'/../generator/skeleton/plugin/plugin/package.xml.tmpl';
    }

    $template = file_get_contents($templatePath);
    $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);

    $tokens = array(
      'PLUGIN_NAME'  => $arguments['plugin'],
      'CURRENT_DATE' => date('Y-m-d'),
      'ENCODING'     => sfConfig::get('sf_charset'),
    );

    if (false !== strpos($template, '##SUMMARY##'))
    {
      $tokens['SUMMARY'] = $this->askAndValidate('Summarize your plugin in one line:', new sfValidatorCallback(array(
        'required' => true,
        'callback' => create_function('$a, $b', 'return htmlspecialchars($b, ENT_QUOTES, sfConfig::get(\'sf_charset\'));'),
      ), array(
        'required' => 'You must provide a summary of your plugin.',
      )), array(
        'value'    => isset($properties['symfony']['author']) ? htmlspecialchars($properties['symfony']['author'], ENT_QUOTES, sfConfig::get('sf_charset')) : null,
      ));
    }

    if (false !== strpos($template, '##LEAD_NAME##'))
    {
      $validator = new sfValidatorString(array(), array('required' => 'A lead developer name is required.'));
      $tokens['LEAD_NAME'] = $this->askAndValidate('Lead developer name:', $validator, array(
        'value' => isset($properties['symfony']['author']) ? htmlspecialchars($properties['symfony']['author'], ENT_QUOTES, sfConfig::get('sf_charset')) : null,
      ));
    }

    if (false !== strpos($template, '##LEAD_EMAIL##'))
    {
      $validator = new sfValidatorEmail(array(), array('required' => 'A valid lead developer email address is required.', 'invalid' => '"%value%" is not a valid email address.'));
      $tokens['LEAD_EMAIL'] = $this->askAndValidate('Lead developer email:', $validator, array(
        'value' => isset($properties['symfony']['email']) ? htmlspecialchars($properties['symfony']['email'], ENT_QUOTES, sfConfig::get('sf_charset')) : null,
      ));
    }

    if (false !== strpos($template, '##LEAD_USERNAME##'))
    {
      $validator = new sfValidatorString(array(), array('required' => 'A lead developer username is required.'));
      $tokens['LEAD_USERNAME'] = $this->askAndValidate('Lead developer username:', $validator, array(
        'value' => isset($properties['symfony']['username']) ? htmlspecialchars($properties['symfony']['username'], ENT_QUOTES, sfConfig::get('sf_charset')) : null,
      ));
    }

    if (false !== strpos($template, '##PLUGIN_VERSION##'))
    {
      $validator = new sfValidatorRegex(array('pattern' => '/\d+\.\d+\.\d+/', ), array('required' => 'A valid version number is required.', 'invalid' => '"%value%" is not a valid version number.'));
      $tokens['PLUGIN_VERSION'] = $this->askAndValidate('Plugin version number (i.e. "1.0.5"):', $validator, array('value' => $options['plugin-version']));

      // set api version based on plugin version
      $tokens['API_VERSION'] = version_compare($tokens['PLUGIN_VERSION'], '0.1.0', '>') ? join('.', array_slice(explode('.', $tokens['PLUGIN_VERSION']), 0, 2)).'.0' : $tokens['PLUGIN_VERSION'];
    }

    if (false !== strpos($template, '##STABILITY##'))
    {
      $validator = new sfValidatorChoice(array('choices' => $choices = array('devel', 'alpha', 'beta', 'stable')), array('required' => 'A valid stability is required.', 'invalid' => '"%value%" is not a valid stability ('.join('|', $choices).').'));
      $tokens['STABILITY'] = $this->askAndValidate('Plugin stability:', $validator, array('value' => $options['plugin-stability']));
    }

    $finder = sfFinder::type('any')->maxdepth(0)->prune('test')->discard('test', 'package.xml.tmpl');
    $tokens['CONTENTS'] = $this->buildContents($this->pluginDir, $finder);

    $this->getFilesystem()->copy($templatePath, $this->pluginDir.'/package.xml');
    $this->getFilesystem()->replaceTokens($this->pluginDir.'/package.xml', '##', '##', $tokens);

    // remove those tokens that shouldn't be written to the template
    unset(
      $tokens['ENCODING'],
      $tokens['CURRENT_DATE'],
      $tokens['PLUGIN_VERSION'],
      $tokens['API_VERSION'],
      $tokens['STABILITY'],
      $tokens['CONTENTS']
    );

    if (count($tokens))
    {
      // create or update package.xml template
      $this->getFilesystem()->copy($templatePath, $this->pluginDir.'/package.xml.tmpl');
      $this->getFilesystem()->replaceTokens($this->pluginDir.'/package.xml.tmpl', '##', '##', $tokens);
    }
  }

  /**
   * Returns an XML string for the contents of the supplied directory.
   * 
   * @param   string           $directory
   * @param   sfFinder         $finder
   * @param   SimpleXMLElement $baseXml
   * 
   * @return  string
   */
  protected function buildContents($directory, sfFinder $finder = null, SimpleXMLElement $baseXml = null)
  {
    if (null === $finder)
    {
      $finder = sfFinder::type('any')->maxdepth(0);
    }

    if (null === $baseXml)
    {
      $baseXml = new SimpleXMLElement('<dir name="/"/>');
    }

    foreach ($finder->in($directory) as $entry)
    {
      if (is_dir($entry))
      {
        $entryXml = $baseXml->addChild('dir');
        $entryXml['name'] = basename($entry);

        $this->buildContents($entry, null, $entryXml);
      }
      else
      {
        $entryXml = $baseXml->addChild('file');
        $entryXml['name'] = basename($entry);
        $entryXml['role'] = 'data';
      }
    }

    // format using DOM to omit XML declaration
    $domElement = dom_import_simplexml($baseXml);
    $domDocument = $domElement->ownerDocument;
    $domDocument->encoding = sfConfig::get('sf_charset');
    $xml = $domDocument->saveXml($domElement);

    return $xml;
  }

  /**
   * @see sfTask
   */
  public function askAndValidate($question, sfValidatorBase $validator, array $options = array())
  {
    if ($this->interactive)
    {
      return parent::askAndValidate($question, $validator, $options);
    }
    else
    {
      return $validator->clean(isset($options['value']) ? $options['value'] : null);
    }
  }
}
