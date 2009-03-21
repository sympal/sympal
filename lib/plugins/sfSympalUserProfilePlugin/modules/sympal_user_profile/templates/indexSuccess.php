<table>
  <tr>
    <td valign="top">
      <?php echo $renderer->render() ?>
    </td>
    <td valign="top" align="right">
      <form action="<?php echo url_for('@sympal_user_profile_save') ?>" method="POST">
        <table>
          <?php echo $form ?>
          <tfoot>
            <tr>
              <td colspan="2"><input type="submit" name="save" value="Save Profile" /></th>
            </tr>
          </tfoot>
        </table>
      </form>
    </td>
  </tr>
</table>