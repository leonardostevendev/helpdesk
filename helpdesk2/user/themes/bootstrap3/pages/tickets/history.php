<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('View Ticket History'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/tickets/');
	exit;
}

if (!$auth->can('manage_tickets') && (!$auth->can('tickets_view_audit_history'))) {
	header('Location: ' . $config->get('address') . '/');
	exit;
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

$history = $ticket_history->get(array('where' => array('ticket_id' => $ticket['id']), 'get_other_data' => true));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>

<div class="row">
	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('History')); ?></h4>
			</div>

			<div class="pull-right">
				<a href="<?php echo $config->get('address'); ?>/tickets/view/<?php echo (int) $ticket['id']; ?>/" class="btn btn-default"><?php echo safe_output($language->get('View')); ?></a>
			</div>

			<div class="clearfix"></div>
			
		</div>
	

	</div>

	<div class="col-md-9">
	

		
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h1 class="panel-title"><?php echo safe_output($ticket['subject']); ?></h1>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">
				<section id="no-more-tables">	
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th><?php echo safe_output($language->get('Added')); ?></th>
								<th><?php echo safe_output($language->get('Type')); ?></th>
								<th><?php echo safe_output($language->get('Change By')); ?></th>
								<th><?php echo safe_output($language->get('Description')); ?></th>
							</tr>
						</thead>
						<?php
							foreach ($history as $item) {						
						?>
						<tr>
							<td data-title="<?php echo safe_output($language->get('Added')); ?>">
								<abbr title="<?php echo safe_output(nice_datetime($item['date_added'])); ?>"><?php echo safe_output(time_ago_in_words($item['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></abbr>
								- <?php echo safe_output(nice_datetime($item['date_added'])); ?>
							</td>
							<td data-title="<?php echo safe_output($language->get('Type')); ?>"><?php echo safe_output(ucwords($item['type'])); ?></td>
							<td data-title="<?php echo safe_output($language->get('Change By')); ?>"><?php echo safe_output($item['ticket_history_user_name']); ?></td>
							<td data-title="<?php echo safe_output($language->get('Description')); ?>">
								<?php echo html_output($item['history_description']); ?>
								<?php if (!empty($item['state_id'])) { ?>
									<br />
									<?php echo safe_output($language->get('Status')); ?> <?php echo safe_output($item['status_name']); ?>
								<?php } ?>
								<?php if (!empty($item['priority_id'])) { ?>
									<br />
									<?php echo safe_output($language->get('Priority')); ?> <?php echo safe_output($item['priority_name']); ?>
								<?php } ?>
								<?php if (!empty($item['assigned_user_id'])) { ?>
									<br />
									<?php echo safe_output($language->get('Assigned User')); ?> <?php echo safe_output(ucwords($item['assigned_name'])); ?>
								<?php } ?>
								<?php if (!empty($item['department_id'])) { ?>
									<br />
									<?php echo safe_output($language->get('Department')); ?> <?php echo safe_output(ucwords($item['department_name'])); ?>
								<?php } ?>
								<?php if (!empty($item['pop_account_id'])) { ?>
									<br />
									<?php echo safe_output($language->get('Email Account')); ?> <?php echo safe_output($item['pop_account_name']); ?>
								<?php } ?>
								<?php if (!empty($item['date_due'])) { ?>
									<br />
									<?php echo safe_output($language->get('Date Due')); ?> <?php echo safe_output(nice_date($item['date_due'])); ?>
								<?php } ?>
							</td>
						</tr>
						<?php } ?>
					</table>
				</section>
			</div>
		</div>
	
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>