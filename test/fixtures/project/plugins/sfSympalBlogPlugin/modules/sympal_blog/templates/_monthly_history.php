<h3>History</h3>
<ul>
  <?php foreach ($months as $month => $postCount): ?>
    <li><?php echo link_to(sprintf('%s (%s)', date('M Y', strtotime($month)), $postCount) , '@sympal_blog_month?m='.date('m', strtotime($month)).'&y='.date('Y', strtotime($month))) ?></li>
  <?php endforeach; ?>
</ul>