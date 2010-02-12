<?php use_helper('SympalSearch') ?>
<form action="<?php echo url_for(get_sympal_search_route()) ?>">
  <input type="text" name="q" value="<?php echo $sf_request->getParameter('q') ?>" />
  <input type="submit" name="search" value="Search" />
</form>