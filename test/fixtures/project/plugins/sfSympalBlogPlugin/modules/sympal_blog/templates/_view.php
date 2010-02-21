<?php use_stylesheet('/sfSympalBlogPlugin/css/blog.css', 'first') ?>

<?php echo get_sympal_breadcrumbs($menuItem) ?>

<div id="sympal_blog">
  <h2><?php echo get_sympal_content_slot($content, 'title') ?></h2>
  <div class="view">
    <?php echo image_tag(get_gravatar_url($content->CreatedBy->getEmailAddress()), 'align=right') ?>

    <p>
      <strong>
        Posted by <?php echo get_sympal_content_slot($content, 'created_by_id', null, 'render_content_author') ?> on 
        <?php echo get_sympal_content_slot($content, 'date_published', null, 'render_content_date_published') ?>
      </strong>
    </p>

    <?php echo get_sympal_content_slot($content, 'body', 'Markdown') ?>
  </div>

  <?php if (sfSympalConfig::get('sfSympalCommentsPlugin', 'installed') && sfSympalConfig::get('sfSympalCommentsPlugin', 'enabled') && sfSympalConfig::get('sfSympalBlogPost', 'enable_comments')): ?>
    <?php use_helper('Comments') ?>
    <?php echo get_sympal_comments($content) ?>
  <?php endif; ?>
</div>

<?php slot('sympal_right_sidebar') ?>
  <?php echo get_component('sympal_blog', 'sidebar') ?>
<?php end_slot() ?>