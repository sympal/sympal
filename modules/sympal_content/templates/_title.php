<?php $route = $content->getRoute() ?>
<strong><small>(<?php echo $content['Type']['label'] ?>)</small></strong>
<?php echo link_to($content, $content->getEditRoute()) ?> <small>[<?php echo link_to('view', $route) ?>]</small><br/>
<small><?php echo $content->getEvaluatedRoutePath() ?></small>