<h1><?php echo __('Previewing "%1%" Theme', array('%1%' => $sf_request->getParameter('preview'))) ?></h1>

<?php echo __('You are previewing the %1% theme. Go back to the %2%.', array('%1%' => '<strong>'.$sf_request->getParameter('preview').'</strong>', '%2%' => link_to(__('list of themes'), '@sympal_themes'))) ?>
