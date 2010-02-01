<td colspan="2">
  <?php echo __('%%name%%<br/><small>%%description%%</small>', array('%%name%%' => link_to(sfInflector::humanize(sfInflector::underscore($sf_guard_permission->getName())), 'sympal_permissions_edit', $sf_guard_permission), '%%description%%' => $sf_guard_permission->getDescription()), 'messages') ?>
</td>
