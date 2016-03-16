<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;


$id = (int) $url->get_item();

if ($id == 0) {
	echo 'Error';
	exit;
}

if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) {

}
else {
	echo 'Error';
	exit;
}

//admin and global mods
if ($auth->can('manage_tickets')) {
	//all tickets
}
//moderator
else if ($auth->can('tickets_view_assigned_department')) {
	$t_array['department_or_assigned_or_user_id']	= $auth->get('id');
}
//users and user plus
else if ($auth->can('tickets_view_assigned')) {
	//select assigned tickets or personal tickets
	$t_array['assigned_or_user_id'] 		= $auth->get('id');
}
//sub
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
	echo 'Error';
	exit;
}

if ($auth->can('manage_tickets')) {
	$departments	= $ticket_departments->get(array('enabled' => 1));
} else {
	$departments 	= $ticket_departments->get(array('enabled' => 1, 'get_other_data' => true, 'user_id' => $auth->get('id')));
}

$priorities 	= $ticket_priorities->get(array('enabled' => 1));
$status 		= $ticket_status->get(array('enabled' => 1));

?>
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/user_selector2.js"></script>
<script>
$(document).ready(function () {

	$('#department_email_alert').hide();
	$('#assigned_user_email_alert').hide();
	
	
	$('body').on('change', '#update_department_id2', function (e) {
		if ($('#update_department_id2').val() != <?php echo (int) $ticket['department_id']; ?>) {
			$('#department_email_alert').slideDown();
		}
		else {
			$('#department_email_alert').slideUp();
		}
		$('#assigned_user_email_alert').slideUp();

	});
	
	$('body').on('change', '#assigned_user_id2', function (e) {
		if ($('#assigned_user_id2').val() != <?php echo (int) $ticket['assigned_user_id']; ?>) {
			$('#assigned_user_email_alert').slideDown();
		}
		else {
			$('#assigned_user_email_alert').slideUp();
		}

	});
		
	$('body').on('click', '#save', function (e) {
		
		e.preventDefault();
		
		$.ajax({
			type: "POST",
			url:  sts_base_url + "/tickets/editmodalsave/" + <?php echo (int) $id; ?>,
			data: "save=true&department_id=" + $('#update_department_id2').val() + "&assigned_user_id=" + $('#assigned_user_id2').val() + "&priority_id=" + $('#priority_id2').val() + "&state_id=" + $('#state_id2').val(),
			success: function(html){
				//alert(html);
				window.location.replace("<?php echo $config->get('address'); ?>/tickets/");
			}
		 });
		
		
	});
	
	$('#model_ticket_delete').click(function () {
		if (confirm("<?php echo safe_output($language->get('Are you sure you wish to delete this ticket?')); ?>")){
			$.ajax({
				type: "POST",
				url:  sts_base_url + "/tickets/delete/" + <?php echo (int) $id; ?>,
				data: "delete=true",
				success: function(html){
					window.location.replace("<?php echo $config->get('address'); ?>/tickets/");
				}
			 });
			 
			return true;
		}
		else{
			return false;
		}
	});

});
</script>
<!-- Modal -->
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title"><?php echo safe_output($ticket['subject']); ?></h4>
		</div>
		<div class="modal-body">
			<?php if (count($departments) > 1) { ?>
				<p><?php echo safe_output($language->get('Transfer to Department')); ?><br />
					<?php if ($auth->can('manage_tickets')) { ?>
						<select name="department_id2" id="update_department_id2">
							<?php foreach($departments as $department) { ?>
								<option value="<?php echo (int) $department['id']; ?>"<?php if ($ticket['department_id'] == $department['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output(ucwords($department['name'])); ?></option>
							<?php } ?>
						</select>
					<?php } else if ($auth->can('tickets_transfer_department')) { ?>
						<select name="department_id2" id="update_department_id2">
							<?php foreach($departments as $department) { ?>
								<?php if ($department['is_user_member'] || $department['public_view']) { ?>
									<option value="<?php echo (int) $department['id']; ?>"<?php if ($ticket['department_id'] == $department['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output(ucwords($department['name'])); ?></option>
								<?php } ?>
							<?php } ?>		
						</select>
					<?php } else { ?>
						<?php echo safe_output(ucwords($ticket['department_name'])); ?>
					<?php } ?>
				</p>
				
				<div id="department_email_alert">
					<div class="alert alert-success">
						<a href="#" class="close" data-dismiss="alert">&times;</a>
						<?php echo html_output($language->get('An email will be sent to this department.')); ?>
					</div>	
				</div>

				
			<?php } ?>	
			
			<p><?php echo safe_output($language->get('Assign User')); ?><br />

				<?php if ($auth->can('manage_tickets') || $auth->can('tickets_assign_user')) { ?>
					<select name="assigned_user_id2" id="assigned_user_id2">
						<option value=""></option>					
						<option value="<?php echo (int) $ticket['assigned_user_id']; ?>" selected="selected"><?php echo safe_output(ucwords($ticket['assigned_name'])); ?></option>
					</select>
				<?php } else { ?>
					<?php echo safe_output(ucwords($ticket['assigned_name'])); ?>
				<?php } ?>
			</p>	

			<div id="assigned_user_email_alert">
				<div class="alert alert-success">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($language->get('An email will be sent to this person.')); ?>
				</div>	
			</div>
			
			<p><?php echo safe_output($language->get('Status')); ?><br />
			<?php if ($auth->can('manage_tickets') || $auth->can('tickets_change_status')) { ?>
				<select name="state_id2" id="state_id2">
				<?php foreach ($status as $status_item) { ?>
					<option value="<?php echo (int) $status_item['id']; ?>"<?php if ($ticket['state_id'] == $status_item['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output($status_item['name']); ?></option>
				<?php } ?>
				</select>
			<?php } else { ?>
				<?php echo safe_output(ucwords($ticket['status_name'])); ?>
			<?php } ?>
			</p>
			
			<p><?php echo safe_output($language->get('Priority')); ?><br />
			<?php if ($auth->can('manage_tickets') || $auth->can('tickets_change_status')) { ?>
				<select name="priority_id2" id="priority_id2">
				<?php foreach ($priorities as $priority) { ?>
					<option value="<?php echo (int) $priority['id']; ?>"<?php if ($ticket['priority_id'] == $priority['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output($priority['name']); ?></option>
				<?php } ?>
				</select>
			<?php } else { ?>
				<?php echo safe_output(ucwords($ticket['priority_name'])); ?>		
			<?php } ?>
			</p>
				
			<div class="clearfix"></div>
			
			<br />
			<?php if (!empty($ticket['last_replier'])) { ?>
				<p><?php echo safe_output($language->get('Last Replier')); ?><br /><?php echo safe_output($ticket['last_replier']); ?></p>
			<?php } ?>
			<div class="clearfix"></div>

		</div>
		<div class="modal-footer">
			<?php if ($auth->can('manage_tickets') || $auth->can('tickets_delete')) { ?>
				<a href="#" id="model_ticket_delete" class="btn btn-danger" data-dismiss="modal"><?php echo safe_output($language->get('Delete')); ?></a>
			<?php } ?>
			<a href="#" class="btn btn-default" data-dismiss="modal"><?php echo safe_output($language->get('Cancel')); ?></a>
			<a href="#" class="btn btn-primary" id="save" data-dismiss="modal"><?php echo safe_output($language->get('Save')); ?></a>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
