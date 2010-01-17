<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
  <?php sympal_minify() ?>
  <?php include_stylesheets() ?>
  <?php include_javascripts() ?>
</head>
<body>

  <div id="sympal_ajax_loading">
    Loading...
  </div>

  <div id="container">

  <!-- content -->
  <div id="content">

  <?php if (!$sf_request->getParameter('popup') && $sf_user->isAuthenticated() && $sf_sympal_context->getSite()): ?>
    <div id="header">
      <h1><?php echo $sf_sympal_context->getSite()->getTitle() ?> <?php echo sfSympalConfig::getCurrentVersion() ?> Admin</h1>
    </div>

    <div id="column_left">
      <p>
        <strong>
          Signed in as <?php echo $sf_user->getUsername() ?> [<?php echo link_to('signout', '@sympal_signout', 'confirm=Are you sure you wish to signout?') ?>]
        </strong>
      </p>

      <?php if (sfSympalConfig::isI18nEnabled()): ?>
        <?php
        $user = sfContext::getInstance()->getUser();
        $form = new sfFormLanguage($user, array('languages' => sfSympalConfig::get('language_codes', null, array($user->getCulture()))));
        unset($form[$form->getCSRFFieldName()]);
        $widgetSchema = $form->getWidgetSchema();
        $widgetSchema['language']->setAttribute('onChange', "this.form.submit();");
        ?>

        <?php echo $form->renderFormTag(url_for('@sympal_change_language_form')) ?>
          <?php echo $form ?>
        </form>

        <br/>
      <?php endif; ?>

      <?php echo get_sympal_admin_menu() ?>
    </div>

    <!-- right column -->
    <div id="column_right">
      <?php echo get_sympal_flash() ?>
      <?php echo $sf_content ?>
    </div>
    <!-- end left column -->
  <?php else: ?>
    <?php echo $sf_content ?>
  <?php endif; ?>

  </div>
  <!-- end content -->
  <br style="clear: both;" />
  </div>
</body>
</html>