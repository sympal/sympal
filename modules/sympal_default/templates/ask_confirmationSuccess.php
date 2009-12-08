<div id="ask_confirmation">
  <h2><?php echo $title ?></h2>

  <p><?php echo htmlspecialchars_decode($message) ?></p>

  <form action="<?php echo $url ?>" method="POST">
    <input type="hidden" name="sf_method" value="<?php echo $sf_request->getMethod() ?>" />
    <input type="hidden" name="sympal_ask_confirmation" value="1" />
    <input type="hidden" name="redirect_url" value="<?php echo $sf_request->getReferer() ?>" />
    <?php foreach ($sf_request->getParameterHolder()->getAll() as $key => $value): ?>
      <input type="hidden" name="<?php echo $key ?>" value="<?php echo $value ?>" />
    <?php endforeach; ?>

    <input type="submit" name="yes" value="Yes" />
    <input type="submit" name="no" value="No" />
  </form>
</div>