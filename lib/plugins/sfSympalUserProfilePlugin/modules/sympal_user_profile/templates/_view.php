<?php use_helper('Entity') ?>

<?php echo get_sympal_breadcrumbs($menuItem, $entity) ?>

<h2>
  <?php echo image_tag($userProfile->getGravatarUrl()) ?> 
  <?php echo $entity->getHeaderTitle() ?>
</h2>

<?php echo sympal_entity_slot($entity, 'biography') ?>

<?php if ($sf_user->isAuthenticated() && $sf_user->getGuardUser()->id == $userProfile->user_id): ?>
  <?php echo link_to('Edit your User Profile', '@sympal_user_profile') ?>
<?php endif; ?>

<?php echo get_sympal_comments($entity) ?>