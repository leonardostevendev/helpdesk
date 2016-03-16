<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Edit Ticket'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/tickets/');
	exit;
}

if ($auth->can('manage_tickets')) {
	//all tickets
}
else if ($auth->can('tickets_view_assigned_department')) {
	//moderator
	$t_array['department_or_assigned_or_user_id']	= $auth->get('id');
}
else if ($auth->can('tickets_view_assigned')) {
	//select assigned tickets or personal tickets
	$t_array['assigned_or_user_id'] 		= $auth->get('id');
}
else {
	//just personal tickets
	$t_array['user_id'] 					= $auth->get('id');
}

$t_array['id']				= $id;
$t_array['get_other_data'] 	= true;
$t_array['limit']			= 1;
$t_array['archived']		= 0;

$tickets_array = $tickets->get($t_array);

if (count($tickets_array) == 1) {
	$ticket = $tickets_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/tickets/');
	exit;
}

if (isset($_POST['delete'])) {
	if ($auth->can('manage_tickets') || $auth->can('tickets_delete')) {
		$tickets->delete(array('id' => $id));

		$history_array['ticket_id'] 			= $id;
		$history_array['by_user_id'] 			= $auth->get('id');
		$history_array['date_added'] 			= datetime();
		$history_array['ip_address'] 			= ip_address();
		$history_array['type'] 					= 'deleted';
		$history_array['history_description'] 	= $language->get('Ticket Deleted');

		$ticket_history->add(
			array(
				'columns' => $history_array
			)
		);

		header('Location: ' . $config->get('address') . '/tickets/');
		exit;
	}
}

if (isset($_POST['save'])) {
	if (!empty($_POST['subject'])) {
		$history_array['ticket_id'] 			= $id;
		$history_array['by_user_id'] 			= $auth->get('id');
		$history_array['date_added'] 			= datetime();
		$history_array['ip_address'] 			= ip_address();

		$ticket_edit =
			array(
				'id'				=> $id,
				'subject'			=> $_POST['subject'],
				'description'		=> $_POST['description']
			);

		if ($auth->can('manage_tickets')) {
			$ticket_edit['user_id'] 	= (int) $_POST['user_id'];

			if ($ticket['user_id'] != (int) $_POST['user_id']) {
				$history_array['user_id'] 				= (int) $_POST['user_id'];
				$history_array['type'] 					= 'owner';
				$history_array['history_description'] 	= $language->get('Ticket Owner Changed');

				$ticket_history->add(
					array(
						'columns' => $history_array
					)
				);
				unset($history_array['user_id']);
			}

		}

		if ($auth->can('manage_tickets') || $auth->can('tickets_assign_user') || $auth->can('tickets_transfer_department')) {

			if ($auth->can('manage_tickets') || $auth->can('tickets_assign_user')) {
				if ($ticket['assigned_user_id'] != (int) $_POST['assigned_user_id']) {
					$history_array['assigned_user_id'] 		= (int) $_POST['assigned_user_id'];
					$history_array['type'] 					= 'assigned';
					$history_array['history_description'] 	= $language->get('Assigned User (via Edit)');

					$ticket_history->add(
						array(
							'columns' => $history_array
						)
					);
					unset($history_array['assigned_user_id']);
				}
				$ticket_edit['assigned_user_id'] 	= (int) $_POST['assigned_user_id'];
			}

			if ($auth->can('manage_tickets') || $auth->can('tickets_transfer_department')) {
				if ($ticket['department_id'] != (int) $_POST['department_id']) {
					$history_array['department_id'] 		= (int) $_POST['department_id'];
					$history_array['type'] 					= 'transferred';
					$history_array['history_description'] 	= $language->get('Transferred Department (via Edit)');

					$ticket_history->add(
						array(
							'columns' => $history_array
						)
					);
					unset($history_array['department_id']);
				}
				$ticket_edit['department_id'] 		= (int) $_POST['department_id'];
			}

			if (isset($_POST['company_id']) && ($_POST['company_id'] != '')) {
				$ticket_edit['company_id']	= (int) $_POST['company_id'];
			}
		}

		if ($auth->can('manage_tickets') || $auth->can('tickets_change_status')) {
			if ($ticket['state_id'] != (int) $_POST['state_id']) {

				$ticket_edit['date_state_changed'] = datetime();

				$history_array['state_id'] 				= (int) $_POST['state_id'];
				$history_array['type'] 					= 'status';
				$history_array['history_description'] 	= $language->get('Status Changed (via Edit)');

				$ticket_history->add(
					array(
						'columns' => $history_array
					)
				);
				unset($history_array['state_id']);
			}

			if ($ticket['priority_id'] != (int) $_POST['priority_id']) {
				$history_array['priority_id'] 			= (int) $_POST['priority_id'];
				$history_array['type'] 					= 'priority';
				$history_array['history_description'] 	= $language->get('Priority Changed (via Edit)');

				$ticket_history->add(
					array(
						'columns' => $history_array
					)
				);
				unset($history_array['priority_id']);
			}

			$ticket_edit['state_id'] 		= (int) $_POST['state_id'];
			$ticket_edit['priority_id'] 	= (int) $_POST['priority_id'];
		}

		if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned') || $auth->can('tickets_view_assigned_department')) {

			if ($ticket['date_due'] != $_POST['date_due']) {
				$history_array['date_due'] 				= $_POST['date_due'];
				$history_array['type'] 					= 'due';
				$history_array['history_description'] 	= $language->get('Date Due Changed (via Edit)');

				$ticket_history->add(
					array(
						'columns' => $history_array
					)
				);
				unset($history_array['date_due']);
			}

			$ticket_edit['date_due'] 		= $_POST['date_due'];

		}



		if ($config->get('html_enabled')) {
			$ticket_edit['html'] = 1;
		}

		$tickets->edit($ticket_edit);


		if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department') || $auth->can('tickets_view_assigned')) {
			$ticket_custom_fields->delete_value(array('ticket_id' => $id));
		}
		else {
			$ticket_custom_fields->delete_value(array('ticket_id' => $id, 'client_modify' => 1));
		}

		foreach($_POST as $index => $value){
			if(strncasecmp($index, 'field-', 6) === 0) {
				$group_index = explode('-', $index);
				$group_id = (int) $group_index[1];
				if ($group_id !== 0) {

					$edit_array['ticket_field_group_id']	= $group_id;
					$edit_array['ticket_id']				= $id;

					if (is_array($value)) {
						$values = array();
						foreach($value as $check_index => $check_item) {
							$values[] = $check_item;
						}
						$edit_array['value']					= json_encode($value);
					}
					else {
						$edit_array['value']					= $value;
					}

					$ticket_custom_fields->add_value($edit_array);
					unset($edit_array);
				}
			}
		}

		header('Location: ' . $config->get('address') . '/tickets/view/' . $id . '/');
		exit;
	}
	else {
		$message = $language->get('Subject Empty');
	}
}

if ($auth->can('manage_tickets')) {
	$departments	= $ticket_departments->get(array('enabled' => 1));
} else {
	$departments 	= $ticket_departments->get(array('enabled' => 1, 'get_other_data' => true, 'user_id' => $auth->get('id')));
}

$priorities 	= $ticket_priorities->get(array('enabled' => 1));
$status 		= $ticket_status->get(array('enabled' => 1));

//$notes_array 	= $ticket_notes->get(array('ticket_id' => (int)$ticket['id'], 'get_other_data' => true));

if ($auth->can('manage_tickets')) {
	$users_array	= $users->get();
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/ajax_ticket_views.js"></script>
<div class="row">

	<script type="text/javascript">
		$(document).ready(function () {
			$('#delete').click(function () {
				if (confirm("<?php echo safe_output($language->get('Are you sure you wish to delete this ticket?')); ?>")){
					return true;
				}
				else{
					return false;
				}
			});
			$('#date_due').datetimepicker({
				widgetPositioning: {
					vertical: "bottom",
					horizontal: "auto"
				}
			});


			<?php if ($config->get('ticket_views_enabled')) { ?>
				sts_update_ticket_views(<?php echo (int) $ticket['id']; ?>);
				<?php if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) { ?>
					sts_get_ticket_views(<?php echo (int) $ticket['id']; ?>);
				<?php } ?>
			<?php } ?>
		});
	</script>
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h4><?php echo safe_output($language->get('Ticket')); ?></h4>
					</div>

					<div class="pull-right">
						<button type="submit" name="save" class="btn btn-success"><?php echo safe_output($language->get('Save')); ?></button>
						<a href="<?php echo safe_output($config->get('address')); ?>/tickets/view/<?php echo (int) $ticket['id']; ?>/" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</div>

					<div class="clearfix"></div>
				</div>

				<div class="panel-body" id="#edit-body">
				<?php if ($auth->can('manage_tickets')) { ?>
					<label class="left-result"><?php echo safe_output($language->get('User')); ?></label>
					<p class="right-result">
						<select name="user_id">
							<option value="">&nbsp;</option>
							<?php foreach ($users_array as $user) { ?>
								<option value="<?php echo (int) $user['id']; ?>"<?php if (isset($ticket['user_id']) && ($ticket['user_id'] == $user['id'])) { echo ' selected="selected"'; } ?>><?php echo safe_output(ucwords($user['name'])); ?></option>
							<?php } ?>
						</select>
					</p>
					<div class="clearfix"></div>
				<?php } else { ?>
					<label class="left-result"><?php echo safe_output($language->get('User')); ?></label>
					<p class="right-result"><?php echo safe_output(ucwords($ticket['owner_name'])); ?></p>
					<div class="clearfix"></div>
				<?php } ?>

				<label class="left-result"><?php echo safe_output($language->get('Status')); ?></label>
				<p class="right-result">
					<?php if ($auth->can('manage_tickets') || $auth->can('tickets_change_status')) { ?>
						<select name="state_id">
						<?php foreach ($status as $status_item) { ?>
							<option value="<?php echo (int) $status_item['id']; ?>"<?php if ($ticket['state_id'] == $status_item['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output($status_item['name']); ?></option>
						<?php } ?>
						</select>
					<?php } else { ?>
						<?php echo safe_output(ucwords($ticket['status_name'])); ?>
					<?php } ?>
				</p>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('Priority')); ?></label>
				<p class="right-result">
					<?php if ($auth->can('manage_tickets') || $auth->can('tickets_change_status')) { ?>
						<select name="priority_id">
						<?php foreach ($priorities as $priority) { ?>
							<option value="<?php echo (int) $priority['id']; ?>"<?php if ($ticket['priority_id'] == $priority['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output($priority['name']); ?></option>
						<?php } ?>
						</select>
					<?php } else { ?>
						<?php echo safe_output(ucwords($ticket['priority_name'])); ?>
					<?php } ?>
				</p>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('Submitted By')); ?></label>
				<p class="right-result">
					<?php echo safe_output(ucwords($ticket['submitted_name'])); ?>
				</p>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('Assigned User')); ?></label>
				<p class="right-result">
					<?php if ($auth->can('manage_tickets') || $auth->can('tickets_assign_user')) { ?>
					<select name="assigned_user_id" id="assigned_user_id">
						<option value=""></option>
						<option value="<?php echo (int) $ticket['assigned_user_id']; ?>" selected="selected"><?php echo safe_output(ucwords($ticket['assigned_name'])); ?></option>
					</select>
					<?php } else { ?>
						<?php echo safe_output(ucwords($ticket['assigned_name'])); ?>
					<?php } ?>
				</p>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('Department')); ?></label>
				<p class="right-result">
					<?php if ($auth->can('manage_tickets')) { ?>
						<select name="department_id" id="update_department_id">
							<?php foreach($departments as $department) { ?>
								<option value="<?php echo (int) $department['id']; ?>"<?php if ($ticket['department_id'] == $department['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output(ucwords($department['name'])); ?></option>
							<?php } ?>
						</select>
					<?php } else if ($auth->can('tickets_transfer_department')) { ?>
						<select name="department_id" id="update_department_id">
							<?php foreach($departments as $department) { ?>
								<?php if ($department['is_user_member']) { ?>
									<option value="<?php echo (int) $department['id']; ?>"<?php if ($ticket['department_id'] == $department['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output(ucwords($department['name'])); ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					<?php } else { ?>
						<?php echo safe_output(ucwords($ticket['department_name'])); ?>
					<?php } ?>
				</p>
				<div class="clearfix"></div>

				<?php $plugins->run('edit_ticket_form_after_department', $ticket); ?>

				<label class="left-result"><?php echo safe_output($language->get('Added')); ?></label>
				<p class="right-result"><?php echo safe_output(time_ago_in_words($ticket['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></p>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('Updated')); ?></label>
				<p class="right-result"><?php echo safe_output(time_ago_in_words($ticket['last_modified'])); ?> <?php echo safe_output($language->get('ago')); ?></p>
				<div class="clearfix"></div>

				<label class="left-result"><?php echo safe_output($language->get('ID')); ?></label>
				<p class="right-result"><?php echo safe_output($ticket['id']); ?></p>
				<div class="clearfix"></div>

				<?php if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned') || $auth->can('tickets_view_assigned_department')) { ?>
					<label class="left-result"><?php echo safe_output($language->get('Date Due')); ?></label>
					<p class="right-result">
						<input type="text" data-date-format="YYYY-MM-DD" id="date_due" class="form-control" placeholder="<?php echo safe_output($language->get('Date Due')); ?>" name="date_due" value="<?php if (isset($ticket['date_due'])) echo safe_output($ticket['date_due']); ?>" size="19" />
					</p>
					<div class="clearfix"></div>
				<?php } ?>

				<?php if ($auth->can('manage_tickets') || $auth->can('tickets_delete')) { ?>
					<br />
					<div class="pull-right">
						<button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo safe_output($language->get('Delete')); ?></button>
					</div>
					<div class="clearfix"></div>
				<?php } ?>
				</div>

			</div>

			<?php if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) { ?>
				<?php $files = $tickets->get_files(array('id' => $ticket['id'], 'private' => 0)); ?>
				<?php if (count($files) > 0) { ?>
					<div class="panel panel-default">
						<div class="panel-heading">
						  <h4><?php echo safe_output($language->get('Files')); ?></h4>
						</div>
						<div class="panel-body">
							<ul>
								<?php foreach ($files as $file) { ?>
								<li id="ticket_id-<?php echo (int) $ticket['id']; ?>"><a href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>"><?php echo safe_output($file['name']); ?></a> <a href="#" class="delete_existing_ticket_file" id="file_id-<?php echo (int) $file['id']; ?>"><img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/images/icons/delete.png" alt="Delete File" /></a></li>
								<?php } ?>
							</ul>
						</div>
					</div>
				<?php } ?>

				<?php $files = $tickets->get_files(array('id' => $ticket['id'], 'private' => 1)); ?>
				<?php if (count($files) > 0) { ?>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4><?php echo safe_output($language->get('Private Files')); ?></h4>
						</div>
						<div class="panel-body">
							<ul>
								<?php foreach ($files as $file) { ?>
								<li id="ticket_id-<?php echo (int) $ticket['id']; ?>"><a href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>"><?php echo safe_output($file['name']); ?></a> <a href="#" class="delete_existing_ticket_file" id="file_id-<?php echo (int) $file['id']; ?>"><img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/images/icons/delete.png" alt="Delete File" /></a></li>
								<?php } ?>
							</ul>
						</div>
					</div>
				<?php } ?>
			<?php } ?>

		</div>

		<div class="col-md-9">

			<div class="alert alert-success">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				<?php echo $language->get('Email notifications are not sent when editing a ticket from this page.'); ?>
			</div>

			<div id="ajax_ticket_views_header"></div>

			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo safe_output($message); ?>
				</div>
			<?php } ?>

			<div class="panel panel-default">
				<div class="panel-body">
					<h3><?php echo safe_output($language->get('Subject')); ?></h3>
					<p><input type="text" size="50" name="subject" class="form-control" value="<?php echo safe_output($ticket['subject']); ?>" /></p>

					<h3><?php echo safe_output($language->get('Description')); ?></h3>
					<div id="no_underline">
						<p>
							<textarea class="wysiwyg_enabled" name="description" cols="80" rows="12">
								<?php if ($ticket['html'] == 1) { ?>
									<?php echo html_output($ticket['description']); ?>
								<?php } else { ?>
									<?php echo nl2br(safe_output($ticket['description'])); ?>
								<?php } ?>
							</textarea>
						</p>
					</div>

					<div class="clear"></div>
				</div>

				<?php $site->view_custom_field_edit_form(array('ticket' => $ticket)); ?>
			</div>
		</div>


	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
