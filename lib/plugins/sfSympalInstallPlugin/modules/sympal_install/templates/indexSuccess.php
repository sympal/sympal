<?php use_stylesheet('/sfSympalPlugin/css/sympal') ?>

<?php echo image_tag('/sfSympalPlugin/images/header_logo.gif') ?>

<h1>Install Sympal Now!</h1>

<?php echo $form->renderFormTag(url_for('@sympal_install_run'), array('sf_method' => 'post')) ?>
  <?php echo $form->renderHiddenFields() ?>

  <div id="user">
    <h2>Setup your First User</h2>

    <table>
      <?php echo $form['user'] ?>
    </table>
  </div>

  <div id="database">
    <h2>Configure your Database</h2>

    <table>
      <?php echo $form['database'] ?>
    </table>
  </div>

  <div id="plugins">
    <h2>Choose Additional Plugins to Install</h2>

    <table>
      <?php echo $form['setup']['plugins'] ?>
    </table>
  </div>

  <br style="clear: both;" />
  <input type="submit" name="install_now" value="Install Now" />
</form>