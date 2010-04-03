<?php use_helper('I18N') ?>

<div id="sympal_signin">
  <h1><?php echo __('Signin'); ?></h1>

  <form action="<?php echo url_for('@sympal_admin') ?>" method="post">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    <div class="form_row<?php echo $form['username']->hasError() ? ' form_row_error' : '' ?>">
      <?php echo $form['username']->renderLabel() ?><br/>
      <?php echo $form['username']->renderError() ?>
      <?php echo $form['username']->render() ?>
    </div>
    <div class="form_row<?php echo $form['username']->hasError() ? ' form_row_error' : '' ?>">
      <?php echo $form['password']->renderLabel() ?><br/>
      <?php echo $form['password']->renderError() ?>
      <?php echo $form['password']->render() ?>
    </div>
    <input type="submit" value="<?php echo __('Signin', null, 'sf_guard') ?>" />
  </form>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    $('#signin_username').focus();
  });
</script>