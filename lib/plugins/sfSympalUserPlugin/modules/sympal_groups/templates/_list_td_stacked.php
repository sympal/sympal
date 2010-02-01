<td colspan="2">
  <?php echo __('%%name%%<br/><small>%%description%%</small>', array('%%name%%' => link_to(sfInflector::humanize(sfInflector::underscore($sf_guard_group->getName())), 'sympal_groups_edit', $sf_guard_group), '%%description%%' => $sf_guard_group->getDescription()), 'messages') ?>
</td>
