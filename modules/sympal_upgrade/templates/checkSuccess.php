<?php use_stylesheet('/sfSympalPlugin/css/upgrade.css') ?>

<?php if ($hasNewVersion): ?>

<h1>Upgrade to <?php echo $latestVersion ?></h1>

<div class="new_version_box">
  A new version of Sympal was detected! Read below for directions on how to upgrade
  from your current version of <?php echo $currentVersion ?> to <?php echo $latestVersion ?>.
</div>

<?php echo sfSympalMarkdownRenderer::convertToHtml("

Your installation of `sfSympalPlugin` can be found here:

    ".($rootDir = $sf_context->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getRootDir())."

To begin the upgrade, we need to first change to the directory where `sfSympalPlugin` is located:

    $ ".$commands['cd']."

Before upgrading to $latestVersion lets move the current version to a backup location:

    $ ".$commands['backup']."

Now checkout the code for $latestVersion:

    $ ".$commands['download']."

Once we've done that we need to clear our cache and run the `sympal:upgrade` task:

    $ cd ".sfConfig::get('sf_root_dir')."
    $ php symfony cc
    $ php symfony sympal:upgrade

The above executed tasks can be automated by Sympal for you if you wish. We showed you
the individual steps first to help you get a better understanding of what Sympal will do
under the hood.

To check if a new version exists on the web use the `--download-new` option. It will
upgrade the Sympal source code to the latest version then run any new available
upgrade tasks.

    $ php symfony sympal:upgrade --download-new

") ?>

<?php else: ?>
  <h1>No Updates Found</h1>

  <p>Your Sympal installation is already up to date at <?php echo $currentVersion ?>!</p>
<?php endif; ?>