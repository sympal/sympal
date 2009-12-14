<form action="<?php echo url_for('@sympal_register_save') ?>" method="post">
  <table>
    <?php echo $form ?>
    <tfoot>
      <tr>
        <td colspan="2">
          <input type="submit" name="register" value="<?php echo __('Register') ?>" />
        </td>
      </tr>
    </tfoot>
  </table>
</form>