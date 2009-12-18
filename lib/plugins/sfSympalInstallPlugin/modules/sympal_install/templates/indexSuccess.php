<?php use_stylesheet('/sfSympalPlugin/css/sympal') ?>
<?php set_sympal_title('Install Sympal') ?>

<?php echo image_tag('/sfSympalPlugin/images/header_logo.gif') ?>

<h1>Install Sympal Now!</h1>

<?php echo $form->renderFormTag(url_for('@sympal_install_run'), array('sf_method' => 'post')) ?>
  <?php echo $form->renderHiddenFields() ?>

  <div id="plugins">
    <h2>Sympal Setup</h2>

    <table>
      <?php echo $form['setup'] ?>
    </table>
  </div>

  <div id="user">
    <h2>User Configuration</h2>

    <table>
      <?php echo $form['user'] ?>
    </table>
  </div>

  <div id="database">
    <h2>Database Configuration</h2>

    <table>
      <?php echo $form['database'] ?>
    </table>
  </div>

  <br style="clear: both;" />
  <input type="submit" name="install_now" value="Install Now" />
</form>