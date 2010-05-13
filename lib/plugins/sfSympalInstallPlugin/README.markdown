sfSympaInstallPlugin
====================

This plugin handles the actual installation process of sympal. The only
prerequisites are that `sfSympalPlugin` be present in your project and
that the following lines be in `config/ProjectConfiguration.class.php`:

    public function setup()
    {
      require_once(dirname(__FILE__).'/../plugins/sfSympalPlugin/config/sfSympalPluginConfiguration.class.php');
      sfSympalPluginConfiguration::enableSympalPlugins($this);
    }

With the above lines, this plugin will become available.

Command-line Installation
-------------------------

Sympal can easily be installed from the command line by using the following
task:

    ./symfony sympal:install

The task performs the following actions:

...

Web-based Installation
----------------------

...