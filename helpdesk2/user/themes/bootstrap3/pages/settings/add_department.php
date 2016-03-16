<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add Department'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {
		$add_array['name']				= $_POST['name'];
		$add_array['enabled']			= 1;
		$add_array['public_view'] 		= $_POST['public_view'] ? 1 : 0;

		$id = $ticket_departments->add($add_array);

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

	}
	else {
		$message = $language->get('Name Empty');
	}
}

$groups 	= $permission_groups->get(array('get_other_data' => true));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h3><?php echo safe_output($language->get('Add Department')); ?></h3>
					</div>
					<div class="pull-right">
							<button type="submit" name="add" class="btn btn-success"><?php echo safe_output($language->get('Add')); ?></button>
							<a href="<?php echo $config->get('address'); ?>/settings/tickets/#departments" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</div>
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
					<h4><?php echo $language->get('Department Settings'); ?></h4>
				</div>

				<div class="panel-body">

					<p><?php echo $language->get('Name'); ?><br /><input style="width: 170px" type="text" class="form-control" required name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" size="30" /></p>
					<div class="clearfix"></div>

					<p><?php echo safe_output($language->get('Public')); ?><br />
					<select name="public_view">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if (isset($_POST['public_view']) && ($_POST['public_view'] == 1)) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>

				</div>
				<div class="clearfix"></div>

			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4><?php echo $language->get('Notifications'); ?></h4>
				</div>

				<div class="panel-body">

					<p><?php echo safe_output($language->get('On top of the normal email notifications you can send notices to the following permission groups within the department.')); ?></p>

					<p><strong><?php echo safe_output($language->get('New Department Ticket')); ?></strong></p>

					<p>
					<?php foreach($groups as $group) { ?>
						<div class="checkbox">
							<label>
							<input type="checkbox" name="new_department_ticket[]" value="<?php echo (int) $group['id']; ?>" <?php if (isset($_POST['new_department_ticket']) && in_array($group['id'], $_POST['new_department_ticket'])) echo 'checked="checked"'; ?> /> <?php echo safe_output($group['name']); ?> <!--(<?php echo (int) $group['members_count']; ?> <?php echo safe_output($language->get('Members')); ?>)--><br />
							</label>
						</div>
					<?php } ?>
					</p>


					<p><strong><?php echo safe_output($language->get('New Department Ticket Reply')); ?></strong></p>

					<p>
					<?php foreach($groups as $group) { ?>
						<div class="checkbox">
							<label>
								<input type="checkbox" name="new_department_ticket_reply[]" value="<?php echo (int) $group['id']; ?>" <?php if (isset($_POST['new_department_ticket_reply']) && in_array($group['id'], $_POST['new_department_ticket_reply'])) echo 'checked="checked"'; ?> /> <?php echo safe_output($group['name']); ?> <!--(<?php echo (int) $group['members_count']; ?> <?php echo safe_output($language->get('Members')); ?>)--><br />
							</label>
						</div>
					<?php } ?>
					</p>

				</div>
				<div class="clearfix"></div>

			</div>

			<div class="clearfix"></div>

		</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
