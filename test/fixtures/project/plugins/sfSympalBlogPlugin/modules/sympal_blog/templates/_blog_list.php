<?php echo get_sympal_pager_header($pager, $content) ?>


<?php foreach ($content as $content): ?>
  <div class="row">
    <h3><?php echo link_to($content, $content->getRoute()) ?></h3>
    <?php echo image_tag(get_gravatar_url($content->CreatedBy->getEmailAddress()), 'align=right') ?>
    <p class="date">
      <strong>
        Posted by <?php echo $content->CreatedBy->getName() ?> on 
        <?php echo format_datetime($content->date_published, sfSympalConfig::get('date_published_format')) ?>
      </strong>
    </p>
    <p class="teaser"><?php echo $content->getRecord()->getTeaser() ?> <strong><small>[<?php echo link_to('read more', $content->getRoute()) ?>]</small></strong></p>
  </div>
<?php endforeach; ?>

<?php echo get_sympal_pager_navigation($pager, url_for($menuItem->getItemRoute())) ?>