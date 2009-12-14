<?php use_helper('I18N') ?>

<form action="<?php echo url_for('@sympal_signin') ?>" method="post">
  <table>
    <?php echo $form ?>
    <tfoot>
      <tr>
        <td colspan="2">
          <input type="submit" value="<?php echo __('Signin') ?>" />
          <?php echo link_to(__('Forgot your Password?'), '@sympal_forgot_password') ?>
        </td>
      </tr>
    </tfoot>
  </table>
</form>