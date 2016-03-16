<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Edit Department'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$id = (int) $url->get_item();

$departments		= $ticket_departments->get(array('id' => $id));

if (empty($departments)) {
	header('Location: ' . $config->get('address') . '/settings/tickets/#departments');
	exit;
}

$item = $departments[0];

if (isset($_POST['delete'])) {
	if ($item['id'] != 1) {
		$ticket_departments->delete(array('id' => $item['id']));
		header('Location: ' . $config->get('address') . '/settings/tickets/#departments');
		exit;
	}
}

if (isset($_POST['save'])) {

	if (!empty($_POST['name'])) {
		$add_array['name']				= $_POST['name'];
		$add_array['id']				= $id;
		$add_array['public_view']		= 1;
		if ($item['id'] != 1) {
			$add_array['public_view'] 	= $_POST['public_view'] ? 1 : 0;
		}

		$ticket_departments->edit($add_array);

		$permission_groups_to_department_notifications->delete(array('columns' => array('department_id' => $id)));

		if (!empty($_POST['new_department_ticket'])) {
			foreach ($_POST['new_department_ticket'] as $group_id) {
				$permission_groups_to_department_notifications->add(array('columns' => array('department_id' => $id, 'group_id' => (int) $group_id, 'type' => 'new_department_ticket')));
			}
		}

		if (!empty($_POST['new_department_ticket_reply'])) {
			foreach ($_POST['new_department_ticket_reply'] as $group_id) {
				$permission_groups_to_department_notifications->add(array('columns' => array('department_id' => $id, 'group_id' => (int) $group_id, 'type' => 'new_department_ticket_reply')));
			}
		}

		header('Location: ' . $config->get('address') . '/settings/tickets/#departments');
		exit;
		//$message = $language->get('Saved');

	}
	else {
		$message = $language->get('Name empty');
	}
	$departments	= $ticket_departments->get(array('id' => $id));
	$item 			= $departments[0];
}

$users_array = $users->get(array('department_id' => $id, 'get_other_data' => true));

$notifications_array = $permission_groups_to_department_notifications->get(array('where' => array('department_id' => $item['id'], 'type' => 'new_department_ticket')));

$new_array = array();
foreach($notifications_array as $x) {
	$new_array[] = $x['group_id'];
}

$notifications_array = $permission_groups_to_department_notifications->get(array('where' => array('department_id' => $item['id'], 'type' => 'new_department_ticket_reply')));

$reply_array = array();
foreach($notifications_array as $i) {
	$reply_array[] = $i['group_id'];
}

$groups 	= $permission_groups->get(array('get_other_data' => true));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>

<script type="text/javascript">
	$(document).ready(function () {
		$('#delete').click(function () {
			if (confirm("<?php echo safe_output($language->get('Are you sure you wish to delete this Department?')); ?>")){
				return true;
			}
			else{
				return false;
			}
		});
	});
</script>

<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h3><?php echo safe_output($language->get('Department')); ?></h3>
					</div>
					<div class="pull-right">
						<button type="submit" name="save" class="btn btn-success"><?php echo safe_output($language->get('Save')); ?></button>
						<a href="<?php echo $config->get('address'); ?>/settings/tickets/#departments" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</div>
					<div class="clearfix"></div>
				</div>

				<div class="panel-body">
					<?php if ($item['id'] != 1) { ?>
						<button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo safe_output($language->get('Delete')); ?></button>
					<?php } else { ?>
						<?php echo safe_output($language->get('Default Department cannot be deleted.')); ?>
					<?php } ?>
					<div class="clearfix"></div>
				</div>

			</div>
		</div>

		<div class="col-md-9">

			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>

			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h4><?php echo $language->get('Name'); ?></h4>
						<input type="text" name="name" value="<?php echo safe_output($item['name']); ?>" size="30" class="form-control"/>
					</div>
					<div class="clearfix"></div>
				</div>
				<?php if ($item['id'] != 1) { ?>
				<div class="panel-heading">
					<div class="pull-left">
						<h4><?php echo safe_output($language->get('Public')); ?></h4>
						<select name="public_view">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($item['public_view'] == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select>
					</div>
					<div class="clearfix"></div>
				</div>
				<?php } else { ?>
				<div class="panel-heading">
					<div class="pull-left">
						<h4><?php echo safe_output($language->get('Default Department must be public.')); ?></h4>
						<input name="public_view" type="hidden" value="1" class="form-control"/>
					</div>
					<div class="clearfix"></div>
				</div>
				<?php } ?>


			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4><?php echo $language->get('Notifications'); ?></h4>
				</div>
				<div class="panel-body">

					<p><?php echo safe_output($language->get('On top of the normal email notifications you can send notices to the following user groups within the department.')); ?></p>
					<p><strong><?php echo safe_output($language->get('New Department Ticket')); ?></strong></p>
					<p>
					<?php foreach($groups as $group) { ?>
						<div class="checkbox">
							<label>
								<input type="checkbox" name="new_department_ticket[]" value="<?php echo (int) $group['id']; ?>" <?php if (in_array($group['id'], $new_array)) echo 'checked="checked"'; ?> /> <?php echo safe_output($group['name']); ?> <!--(<?php echo (int) $group['members_count']; ?> <?php echo safe_output($language->get('Members')); ?>)--><br />
							</label>
						</div>
					<?php } ?>
					</p>
					<p><strong><?php echo safe_output($language->get('New Department Ticket Reply')); ?></strong></p>
					<p>
					<?php foreach($groups as $group) { ?>
						<div class="checkbox">
							<label>
								<input type="checkbox" name="new_department_ticket_reply[]" value="<?php echo (int) $group['id']; ?>" <?php if (in_array($group['id'], $reply_array)) echo 'checked="checked"'; ?> /> <?php echo safe_output($group['name']); ?> <!--(<?php echo (int) $group['members_count']; ?> <?php echo safe_output($language->get('Members')); ?>)--><br />
							</label>
						</div>
					<?php } ?>
					</p>
				</div>

			</div>

			<?php if (!empty($users_array)) { ?>
				<div class="well well-sm">
					<h3><?php echo $language->get('Members'); ?></h3>

					<section id="no-more-tables">
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th><?php echo $language->get('Name'); ?></th>
									<th><?php echo $language->get('Permissions'); ?></th>
								</tr>
							</thead>

							<tbody>
								<?php $i = 0;
									foreach($users_array as $user) { ?>
									<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
										<td data-title="<?php echo $language->get('Name'); ?>" class="centre"><a href="<?php echo $config->get('address'); ?>/users/view/<?php echo (int) $user['id']; ?>/"><?php echo safe_output($user['name']); ?></a></td>
										<td data-title="<?php echo $language->get('Permissions'); ?>" class="centre"><?php echo safe_output($user['permission_group_name']); ?></td>
									</tr>
								<?php $i++; } ?>
							</tbody>
						</table>
					</section>

				</div>
			<?php } ?>

			<div class="clearfix"></div>

		</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
