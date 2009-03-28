<div id="ask_confirmation">
  <h2><?php echo $title ?></h2>

  <p><?php echo $message ?></p>

  <form action="<?php echo $url ?>" method="<?php echo $sf_request->getMethod() ?>">
    <input type="hidden" name="confirmation" value="1" />
    <input type="hidden" name="redirect_url" value="<?php echo $sf_request->getReferer() ?>" />
    <input type="submit" name="yes" value="Yes" />
    <input type="submit" name="no" value="No" />
  </form>
</div>