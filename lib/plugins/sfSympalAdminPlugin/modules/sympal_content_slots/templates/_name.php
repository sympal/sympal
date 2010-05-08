<?php use_helper('Text') ?>

<strong><?php echo link_to($sf_sympal_content_slot->getName(), '@sympal_content_slots_edit?id='.$sf_sympal_content_slot->getId()) ?></strong><br/>
<small><?php echo truncate_text(strip_tags($sf_sympal_content_slot->getValue()), 100) ?></small>