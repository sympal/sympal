<div id="sympal_admin_signin">
  <?php use_helper('I18N') ?>

  <form action="<?php echo url_for('@sympal_admin') ?>" method="post">
    <div id="sf_admin_container">
      <h1>Sympal Admin Signin</h1>

      <div id="sf_admin_signin">
        <div class="sf_admin_form">

          <?php echo get_partial('sympal_default/render_form', array('form' => $form)) ?>

          <input type="submit" value="<?php echo __('Signin', null, 'sf_guard') ?>" />
        </div>
      </div>
    </div>
  </form>
</div>