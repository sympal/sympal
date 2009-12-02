<form action="<?php echo url_for('@sympal_change_language') ?>">
  <table>
    <?php echo $form ?>
  </table>
  <input type="submit" value="<?php echo __('ok') ?>" />
</form>