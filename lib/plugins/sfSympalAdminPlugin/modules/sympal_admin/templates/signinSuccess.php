<?php use_helper('I18N') ?>

<div id="sympal_signin">
  <h1>Signin</h1>

  <?php use_helper('I18N') ?>

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