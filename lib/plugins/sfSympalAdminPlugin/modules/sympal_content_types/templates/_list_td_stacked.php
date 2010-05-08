<td colspan="2">
  <?php echo __('%%label%%<br/><small>%%description%%</small>', array('%%label%%' => link_to($sf_sympal_content_type->getLabel(), 'sympal_content_types_edit', $sf_sympal_content_type), '%%description%%' => $sf_sympal_content_type->getDescription()), 'messages') ?>
</td>
