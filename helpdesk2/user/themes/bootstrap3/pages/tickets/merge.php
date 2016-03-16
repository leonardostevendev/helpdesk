<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Merge Tickets'));
$site->set_config('container-type', 'container');

if (isset($_SESSION['merge']) && is_array($_SESSION['merge'])) {

}
else {
	header('Location: ' . $config->get('address') . '/tickets/');
	exit;
}

if (isset($_POST['merge'])) {
	if (isset($_POST['keep_ticket_id'])) {
		
		$ticket_id = $tickets->merge(array('ids' => $_SESSION['merge']['ticket_ids'], 'primary_id' => (int) $_POST['keep_ticket_id']));
		
		header('Location: ' . $config->get('address') . '/tickets/view/' . (int) $ticket_id . '/');
		exit;
	}
	else {
		$message = $language->get('Please select a primary ticket.');
	}
}

$tickets_array = $tickets->get(array('ids' => $_SESSION['merge']['ticket_ids'], 'get_other_data' => true));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
	<div class="row">
		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Merge Tickets')); ?></h4>
				</div>

				<div class="pull-right">
					<button type="submit" name="merge" class="btn btn-primary"><?php echo safe_output($language->get('Merge')); ?></button>	
				</div>

				<div class="clearfix"></div>
			
			</div>
		
		</div>

		<div class="col-md-9">
			<?php if (isset($message)) { ?>
				<div class="alert alert-error">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			<section id="no-more-tables">
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th><?php echo safe_output($language->get('Primary')); ?></th>
							<th><?php echo safe_output($language->get('Added')); ?></th>
							<th><?php echo safe_output($language->get('Status')); ?></th>
							<th><?php echo safe_output($language->get('Priority')); ?></th>
							<th><?php echo safe_output($language->get('Subject')); ?></th>
							<th><?php echo safe_output($language->get('User')); ?></th>
							<th><?php echo safe_output($language->get('Assigned')); ?></th>
							<th><?php echo safe_output($language->get('Updated')); ?></th>
						</tr>
					</thead>
					
					<tfoot>
						<tr>
							<th><?php echo safe_output($language->get('Primary')); ?></th>
							<th><?php echo safe_output($language->get('Added')); ?></th>
							<th><?php echo safe_output($language->get('Status')); ?></th>
							<th><?php echo safe_output($language->get('Priority')); ?></th>
							<th><?php echo safe_output($language->get('Subject')); ?></th>
							<th><?php echo safe_output($language->get('User')); ?></th>
							<th><?php echo safe_output($language->get('Assigned')); ?></th>
							<th><?php echo safe_output($language->get('Updated')); ?></th>

						</tr>
					</tfoot>
					<tbody id="merge-tickets-table-body">
						<?php
							$i = 0;
							foreach ($tickets_array as $ticket) {
						?>
						<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
							<td data-title="<?php echo safe_output($language->get('Primary')); ?>" class="centre">
								<input type="radio" name="keep_ticket_id" value="<?php echo (int) $ticket['id']; ?>" />
							</td>
							<td data-title="<?php echo safe_output($language->get('Added')); ?>" class="centre"><?php echo safe_output(time_ago_in_words($ticket['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></td>
							<td data-title="<?php echo safe_output($language->get('Status')); ?>" class="<?php if ($ticket['state_id'] == 1) { echo 'ticket-open'; } elseif ($ticket['state_id'] == 2) { echo 'ticket-closed'; } ?> centre" style="background-color: <?php echo safe_output($ticket['status_colour']); ?>"><?php echo safe_output($ticket['status_name']); ?></td>
							<td data-title="<?php echo safe_output($language->get('Priority')); ?>" class="centre"><?php echo safe_output($ticket['priority_name']); ?></td>
							<td data-title="<?php echo safe_output($language->get('Subject')); ?>" class="centre"><?php echo safe_output($ticket['subject']); ?></td>
							<td data-title="<?php echo safe_output($language->get('User')); ?>" class="centre"><?php echo safe_output(ucwords($ticket['owner_name'])); ?></td>
							<td data-title="<?php echo safe_output($language->get('Assigned')); ?>" class="centre"><?php echo safe_output(ucwords($ticket['assigned_name'])); ?></td>
							<td data-title="<?php echo safe_output($language->get('Updated')); ?>" class="centre">
								<?php echo safe_output(time_ago_in_words($ticket['last_modified'])); ?> <?php echo safe_output($language->get('ago')); ?>
							</td>
						</tr>
						<?php $i++; } ?>
					</tbody>
				</table>
			</section>	
			
			<!--
			<label class="checkbox">
				<input type="checkbox" name="delete_old" value="1" checked="checked" /> <?php echo safe_output($language->get('Delete Old Tickets')); ?>
			</label>							
			-->
			
		</div>
	</div>
</form>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>