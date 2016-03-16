<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Permissions'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$groups 	= $permission_groups->get(array('get_other_data' => true));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>

<div class="row">

	<div class="col-md-3">
		<div class="panel panel-default">
			<div class="panel-heading">
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Permissions')); ?></h4>
			</div>
			<div class="pull-right">
			</div>

			<div class="clearfix"></div>
			</div>

		</div>
	</div>

	<div class="col-md-9">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Groups')); ?></h4>
				</div>
				<div class="pull-right">
					<a href="<?php echo safe_output($config->get('address')); ?>/settings/add_permission_group/" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></a>
				</div>
				<div class="clearfix"></div>
			</div>

			<div class="panel-body">
				<section id="no-more-tables">
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th><?php echo safe_output($language->get('Name')); ?></th>
								<th><?php echo safe_output($language->get('Members')); ?></th>
							</tr>
						</thead>

						<tbody>
							<?php $i = 0;
								foreach($groups as $group) { ?>
								<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
									<td class="centre" data-title="<?php echo safe_output($language->get('Name')); ?>"><a href="<?php echo safe_output($config->get('address')); ?>/settings/edit_permission_group/<?php echo (int) $group['id']; ?>/"><?php echo safe_output($group['name']); ?></a></td>
									<td class="centre" data-title="<?php echo safe_output($language->get('Members')); ?>"><?php echo safe_output((int) $group['members_count']); ?></td>

								</tr>
							<?php $i++; } ?>
						</tbody>
					</table>
				</section>
			</div>
		</div>
	</div>

</div>



<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
