<?php use_stylesheet('/sfSympalBlogPlugin/css/blog.css', 'first') ?>
<?php echo get_sympal_breadcrumbs($menuItem, $breadcrumbsTitle) ?>
<?php $sf_response->setTitle($title) ?>

<div id="sympal_blog">
  <div class="list">
    <h2><?php echo $title ?></h2>

    <?php echo get_partial('sympal_blog/blog_list', array('pager' => $pager, 'menuItem' => $menuItem, 'content' => $content)) ?>
  </div>
</div>

<?php slot('sympal_right_sidebar') ?>
  <?php echo get_component('sympal_blog', 'sidebar') ?>
<?php end_slot() ?>