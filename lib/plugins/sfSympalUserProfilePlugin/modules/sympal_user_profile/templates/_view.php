<?php echo get_sympal_breadcrumbs($menuItem, $content) ?>

<h2>
  <?php echo image_tag($userProfile->getGravatarUrl()) ?> 
  <?php echo $content->getHeaderTitle() ?>
</h2>

<?php echo get_sympal_content_slot($content, 'biography') ?>

<?php if ($sf_user->isAuthenticated() && $sf_user->getGuardUser()->id == $userProfile->user_id): ?>
  <?php echo link_to('Edit your User Profile', '@sympal_user_profile') ?>
<?php endif; ?>

<?php echo get_sympal_comments($content) ?>