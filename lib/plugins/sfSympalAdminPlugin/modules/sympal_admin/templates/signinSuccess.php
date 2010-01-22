<?php use_helper('I18N') ?>

<div id="sympal_signin">
  <h1><?php echo __('Signin'); ?></h1>

  <form action="<?php echo url_for('@sympal_admin') ?>" method="post">
    <table>
      <tbody>
        <?php echo $form ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2">
            <input type="submit" value="<?php echo __('Signin', null, 'sf_guard') ?>" />
          </td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>