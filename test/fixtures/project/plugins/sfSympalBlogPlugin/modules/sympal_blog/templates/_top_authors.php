<h3>Top 5 Authors</h3>
<ul>
  <?php foreach ($authors as $author): ?>
    <li><?php echo $author->getName() ?> (<?php echo $author->num_posts ?>)</li>
  <?php endforeach; ?>
</ul>