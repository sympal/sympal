<?php use_stylesheet('/sfSympalPlugin/css/sympal') ?>
<?php set_sympal_title('Install Sympal') ?>

<?php echo image_tag('/sfSympalPlugin/images/header_logo.gif') ?>

<h2>Install Sympal Now!</h2>

<?php echo $form->renderFormTag(url_for('@sympal_install_run'), array('sf_method' => 'post')) ?>
  <table>
    <?php echo $form ?>
  </table>

  <input type="submit" name="install_now" value="Install Now" />
</form>