<h3>Latest 5 Posts</h3>
<ul>
  <?php foreach ($latestPosts as $content): ?>
    <li><?php echo link_to($content, $content->getRoute()) ?></li>
  <?php endforeach; ?>
</ul>