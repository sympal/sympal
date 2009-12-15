<?php use_stylesheet('/sfSympalPlugin/css/upgrade.css') ?>

<?php if ($hasNewVersion): ?>

<h1>Upgrade to <?php echo $latestVersion ?></h1>

<div id="new_version_box">
  A new version of Sympal was detected! Read below for directions on how to upgrade
  from your current version of <?php echo $currentVersion ?> to <?php echo $latestVersion ?>.
</div>

<?php echo sfSympalMarkdownRenderer::convertToHtml("

Your installation of `sfSympalPlugin` can be found here:

    ".($rootDir = $sf_context->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getRootDir())."

To begin the upgrade, first change to the directory where `sfSympalPlugin` is located:

    $ cd ".dirname($rootDir)."

Before upgrading to $latestVersion lets move the current version to a backup location:

    $ mv sfSympalPlugin sfSympalPlugin_$currentVersion

Now checkout the code for $latestVersion:

    $ svn co http://svn.symfony-project.org/plugins/sfSympalPlugin/tags/$latestVersion $rootDir

Once we've done that we need to clear our cache and run the `sympal:upgrade` task:

    $ cd ".sfConfig::get('sf_root_dir')."
    $ php symfony cc
    $ php symfony sympal:upgrade
") ?>
<?php else: ?>
  <h1>No Updates Found</h1>

  <p>Your Sympal installation is already up to date at <?php echo $currentVersion ?>!</p>
<?php endif; ?>