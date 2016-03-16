<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('View Ticket'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_tickets') && !$auth->can('tickets')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/tickets/');
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

$site->set_title($ticket['subject']);

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

$note_get_array['ticket_id'] 		= (int) $ticket['id'];
$note_get_array['get_other_data'] 	= true;

if (!$auth->can('tickets_view_private_replies')) {
	$note_get_array['private'] 		= 0;
}

if ($auth->get('view_ticket_reverse')) {
	$note_get_array['order'] 		= 'desc';
}

$notes_array = $ticket_notes->get($note_get_array);

$status 		= $ticket_status->get(array('enabled' => 1));

if ($auth->can('manage_tickets')) {
	$departments	= $ticket_departments->get(array('enabled' => 1));
} else {
	$departments 	= $ticket_departments->get(array('enabled' => 1, 'get_other_data' => true, 'user_id' => $auth->get('id')));
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/ajax_ticket_views.js"></script>
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
		<?php if ($config->get('ticket_views_enabled')) { ?>
			sts_update_ticket_views(<?php echo (int) $ticket['id']; ?>);
			<?php if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) { ?>
				sts_get_ticket_views(<?php echo (int) $ticket['id']; ?>);
			<?php } ?>
		<?php } ?>
	});
</script>
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/user_selector2.js"></script>

<div class="row ticket-view">
	<div class="col-md-3">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Ticket')); ?></h4>
				</div>

				<div class="pull-right">
					<div class="btn-group">
					<a class="custom_modal btn btn-info" data-href="<?php echo $config->get('address'); ?>/tickets/view_files_modal/<?php echo (int) $ticket['id']; ?>/?action=all_files" title="<?php echo safe_output($language->get('Attachments')); ?>"><i class="fa fa-paperclip"></i></a>

					<?php if ($auth->can('manage_tickets') || ($auth->can('tickets_view_audit_history'))) { ?>
						<a href="<?php echo $config->get('address'); ?>/tickets/history/<?php echo (int) $ticket['id']; ?>/" class="btn btn-info" title="<?php echo safe_output($language->get('History')); ?>"><i class="fa fa-clock-o"></i></a>

					<?php } ?>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>

			<div class="panel-body">
			<label class="left-result"><?php echo safe_output($language->get('User')); ?></label>
			<p class="right-result">
				<?php echo safe_output(ucwords($ticket['owner_name'])); ?>
			</p>
			<div class="clearfix"></div>

			<label class="left-result"><?php echo safe_output($language->get('Status')); ?></label>
			<p class="right-result"><?php echo safe_output($ticket['status_name']);  ?></p>
			<div class="clearfix"></div>

			<label class="left-result"><?php echo safe_output($language->get('Priority')); ?></label>
			<p class="right-result"><?php echo safe_output($ticket['priority_name']); ?></p>
			<div class="clearfix"></div>

			<label class="left-result"><?php echo safe_output($language->get('Submitted By')); ?></label>
			<p class="right-result">
				<?php echo safe_output(ucwords($ticket['submitted_name'])); ?>
			</p>
			<div class="clearfix"></div>

			<label class="left-result"><?php echo safe_output($language->get('Assigned User')); ?></label>
			<p class="right-result">
				<?php echo safe_output(ucwords($ticket['assigned_name'])); ?>
			</p>
			<div class="clearfix"></div>

			<label class="left-result"><?php echo safe_output($language->get('Department')); ?></label>
			<p class="right-result"><?php echo safe_output(ucwords($ticket['department_name'])); ?></p>
			<div class="clearfix"></div>

			<?php $plugins->run('view_ticket_details_after_department', $ticket); ?>

			<label class="left-result"><?php echo safe_output($language->get('Added')); ?></label>
			<p class="right-result"><abbr title="<?php echo safe_output(nice_datetime($ticket['date_added'])); ?>"><?php echo safe_output(time_ago_in_words($ticket['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></abbr></p>
			<div class="clearfix"></div>

			<label class="left-result"><?php echo safe_output($language->get('Updated')); ?></label>
			<p class="right-result"><abbr title="<?php echo safe_output(nice_datetime($ticket['last_modified'])); ?>"><?php echo safe_output(time_ago_in_words($ticket['last_modified'])); ?> <?php echo safe_output($language->get('ago')); ?></abbr></p>
			<div class="clearfix"></div>

			<label class="left-result"><?php echo safe_output($language->get('ID')); ?></label>
			<p class="right-result"><?php echo safe_output($ticket['id']); ?></p>
			<div class="clearfix"></div>

			<?php if ($ticket['pop_account_name'] != '' && $auth->can('manage_tickets')) { ?>
				<label class="left-result"><?php echo safe_output($language->get('Email Account')); ?></label>
				<p class="right-result"><?php echo safe_output($ticket['pop_account_name']); ?></p>
				<div class="clearfix"></div>
			<?php } ?>

			<?php if (!empty($ticket['cc'])) { ?>
				<?php $cc = unserialize($ticket['cc']); ?>
				<label class="left-result"><?php echo safe_output($language->get('CC')); ?></label>
				<p class="right-result">
					<a href="#" class="popover-item"
					data-html="true"
					data-content="
					<ul>
					<?php foreach($cc as $cc_item) { ?>
						<li><?php echo safe_output($cc_item); ?></li>
					<?php } ?>
					</ul>
					" data-title="CC'ed Email Addresses">
						<?php echo (int) count($cc); ?>
					</a>
				</p>
				<div class="clearfix"></div>
			<?php } ?>

			<?php if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned') || $auth->can('tickets_view_assigned_department')) { ?>
				<?php if (isset($ticket['date_due']) && !empty($ticket['date_due']) && ($ticket['date_due'] !== '0000-00-00')) { ?>
					<label class="left-result"><?php echo safe_output($language->get('Date Due')); ?></label>
					<p class="right-result"><?php echo safe_output(nice_date($ticket['date_due'])); ?></p>
					<div class="clearfix"></div>
				<?php } ?>
			<?php } ?>
			</div>
			<?php $plugins->run('view_ticket_details_finish'); ?>
			<div class="panel-footer">
				<div class="pull-right">
					<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
						<div class="btn-group">
							<?php if ($auth->can('manage_tickets') || $auth->can('tickets_delete')) { ?>
								<button type="submit" id="delete" name="delete" class="btn btn-danger" title="<?php echo safe_output($language->get('Delete')); ?>"><i class="fa fa-trash"></i></button>
							<?php } ?>
							<a href="<?php echo $config->get('address'); ?>/tickets/edit/<?php echo (int) $ticket['id']; ?>/" class="btn btn-warning" title="<?php echo safe_output($language->get('Edit')); ?>"><i class="fa fa-pencil-square-o"></i></a>
						</div>
					</form>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>


			</div>

		</div>


		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('User Details')); ?></h4>
				</div>

				<?php if ($ticket['user_id'] == 0 && $auth->can('manage_users')) { ?>
					<div class="pull-right">
						<a href="<?php echo $config->get('address'); ?>/users/add/?name=<?php echo safe_output($ticket['name']); ?>&amp;email=<?php echo safe_output($ticket['email']); ?>" class="btn btn-default"><?php echo safe_output($language->get('Create Account')); ?></a>
					</div>
				<?php } ?>

				<div class="clearfix"></div>
			</div>

			<div class="panel-body">
				<?php if ($ticket['user_id'] == 0) { ?>
					<p class="center"><?php echo safe_output($language->get('This ticket is from a user without an account.')); ?></p>

					<label class="left-result"><?php echo safe_output($language->get('Name')); ?></label>
					<p class="right-result"><?php echo safe_output(ucwords($ticket['name'])); ?></p>
					<div class="clearfix"></div>

					<label class="left-result"><?php echo safe_output($language->get('Email')); ?></label>
					<p class="right-result"><a href="mailto:<?php echo safe_output($ticket['email']); ?>"><?php echo safe_output($ticket['email']); ?></a></p>
					<div class="clearfix"></div>
				<?php } else { ?>
					<?php if ($auth->can('manage_users')) { ?>
						<label class="left-result"><?php echo safe_output($language->get('Name')); ?></label>
						<p class="right-result"><a href="<?php echo $config->get('address'); ?>/users/view/<?php echo (int) $ticket['user_id']; ?>/"><?php echo safe_output(ucwords($ticket['owner_name'])); ?></a></p>
						<div class="clearfix"></div>
					<?php } else { ?>
						<label class="left-result"><?php echo safe_output($language->get('Name')); ?></label>
						<p class="right-result"><?php echo safe_output(ucwords($ticket['owner_name'])); ?></p>
						<div class="clearfix"></div>
					<?php } ?>

					<label class="left-result"><?php echo safe_output($language->get('Email')); ?></label>
					<p class="right-result"><a href="mailto:<?php echo safe_output($ticket['owner_email']); ?>"><?php echo safe_output($ticket['owner_email']); ?></a></p>
					<div class="clearfix"></div>

					<?php if (!empty($ticket['owner_phone'])) { ?>
						<label class="left-result"><?php echo safe_output($language->get('Phone')); ?></label>
						<p class="right-result"><?php echo safe_output($ticket['owner_phone']); ?></p>
						<div class="clearfix"></div>
					<?php } ?>
				<?php } ?>
			</div>

			<?php $plugins->run('view_ticket_user_details_finish'); ?>

		</div>

		<?php $plugins->run('view_ticket_sidebar_finish', $ticket); ?>

	</div>

	<div class="col-md-9">
		<?php $plugins->run('view_ticket_content_start', $ticket); ?>

		<?php if ((int) $ticket['merge_ticket_id'] != 0) { ?>
			<div class="alert alert-warning">
				<strong><?php echo html_output($language->get('This ticket was merged into another ticket.')); ?></strong>
				<div class="pull-right">
					<p><a href="<?php echo safe_output($config->get('address')); ?>/tickets/view/<?php echo (int) $ticket['merge_ticket_id']; ?>/" class="btn btn-info"><?php echo safe_output($language->get('View Ticket')); ?></a></p>
				</div>
				<div class="clearfix"></div>
			</div>
		<?php } ?>

		<div id="ajax_ticket_views_header"></div>

		<?php if ($auth->get('view_ticket_reverse')) { ?>
			<?php include('include_view_add_reply.php'); ?>
		<?php } ?>

		<?php if (!$auth->get('view_ticket_reverse')) { ?>
			<?php include('include_view_ticket.php'); ?>
		<?php } ?>

		<?php if (!empty($notes_array)) { ?>
			<div class="page-header">
				<h4><?php echo safe_output($language->get('Replies')); ?></h4>
			</div>
			<?php $i = 0; foreach($notes_array as $note) { ?>
				<?php
					$files = array();

					if (!empty($note['file_ids']) && !empty($note['file_names'])) {
						$file_ids = explode(chr(29), $note['file_ids']);
						$file_names = explode(chr(29), $note['file_names']);

						foreach($file_ids as $index => $value) {
							$files[] = array('id' => $value, 'name' => $file_names[$index]);
						}
					}

				?>

				<div class="panel <?php if ($i % 2 == 0 ) { echo 'panel-default'; } else { echo 'panel-info'; } ?> ticket-reply">
					<?php if (!empty($note['subject'])) { ?>
						<div class="panel-heading">
							<div class="pull-left">
								<h1 class="panel-title"><?php echo safe_output($note['subject']); ?></h1>
							</div>
							<div class="clearfix"></div>
						</div>
					<?php } ?>
					<div class="panel-body">
						<div class="pull-right">
							<?php if ($config->get('gravatar_enabled')) { ?>
								<?php if ($note['user_id'] == 0) { ?>
									<?php $gravatar->setEmail($note['email']); ?>
								<?php } else { ?>
									<?php $gravatar->setEmail($note['owner_email']); ?>
								<?php } ?>
								<div class="pull-right gravatar">
									<p><img src="<?php echo $gravatar->getUrl(); ?>" alt="Gravatar" /></p>
								</div>
							<?php } ?>
						</div>
						<?php if ($note['html'] == 1) { ?>
							<?php echo html_output($note['description']); ?>
						<?php } else { ?>
							<p><?php echo nl2br(safe_output($note['description'])); ?></p>
						<?php } ?>
						<div class="clearfix"></div>
					</div>
					<div class="panel-footer">

							<?php if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) { ?>
								<div class="pull-left">
									<a class="custom_modal btn btn-default btn-xs" data-href="<?php echo $config->get('address'); ?>/tickets/edit_reply_modal/<?php echo (int) $note['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>" title="Edit reply"><i class="fa fa-pencil-square-o"></i></a>
								</div>
							<?php } ?>

							<?php if ($auth->can('manage_tickets') || $auth->can('tickets_carbon_copy_reply')) { ?>
								<?php if (!empty($note['cc'])) { ?>
								<div class="pull-left">
									<?php $cc = unserialize($note['cc']); ?>
									<a href="#" class="popover-item"
									data-html="true"
									data-content="
									<ul>
									<?php foreach($cc as $cc_item) { ?>
										<li><?php echo safe_output($cc_item); ?></li>
									<?php } ?>
									</ul>
									" data-title="<?php echo safe_output($language->get('Carbon Copied Email Addresses')); ?>">
										<span class="label label-success" title="Carbon Copied"><?php echo safe_output($language->get('Carbon Copied')); ?></span>
									</a>
								</div>
								<?php } ?>
							<?php } ?>

							<?php if ($note['private'] == 1) { ?>
								<div class="pull-left">
									<span class="label label-default"><?php echo safe_output($language->get('Private Reply')); ?></span>
								</div>
							<?php } ?>

							<?php if ($auth->can('manage_tickets') && !empty($note['email_data'])) { ?>
								<div class="pull-left">
									<a class="btn btn-default btn-xs" href="<?php echo $config->get('address'); ?>/tickets/view_reply_email/<?php echo (int) $note['id']; ?>/"><span class="glyphicon glyphicon-inbox"></span></a>
								</div>
							<?php } ?>

							<?php if (!empty($files)) { ?>
								<div class="pull-left">
								<!--<a href="#" class="btn btn-default btn-xs" id="open-note-gal-<?php echo (int) $note['id']; ?>"><?php echo safe_output($language->get('Gallery')); ?></a>-->

								<a class="custom_modal btn btn-default btn-xs" data-href="<?php echo $config->get('address'); ?>/tickets/view_files_modal/<?php echo (int) $ticket['id']; ?>/?note_id=<?php echo (int) $note['id']; ?><?php if ($note['private'] == 1) { ?>&amp;type=private<?php } ?>"><?php echo safe_output($language->get('Attachments')); ?></a>

								<?php foreach($files as $file) { ?>
									<a href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>&amp;note_id=<?php echo (int) $note['id']; ?>&amp;action=view" title="<?php echo safe_output($file['name']); ?>" rel="note-gal-<?php echo (int) $note['id']; ?>"></a>
									<a href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>&amp;note_id=<?php echo (int) $note['id']; ?>" title="<?php echo safe_output($file['name']); ?>" class="btn btn-info btn-xs"><?php echo safe_output($file['name']); ?></a>
								<?php } ?>

								<script type="text/javascript">
									$(document).ready(function () {
										var $gallery = $("a[rel=note-gal-<?php echo (int) $note['id']; ?>]").colorbox(
											{
												photo:true,
												scalePhotos: true,
												width: '60%',
												height: '75%'
											}
										);

										$("#open-note-gal-<?php echo (int) $note['id']; ?>").click(function(e){
											e.preventDefault();
											$("a[rel=note-gal-<?php echo (int) $note['id']; ?>]").eq(0).click();
										});
									});
								</script>
								</div>
							<?php } ?>



								<?php if ($note['user_id'] == 0) { ?>
								<div class="pull-right">
									<?php echo safe_output(ucwords($note['name']) . ' <' . $note['email'] . '>'); ?>&nbsp;<abbr title="<?php echo safe_output(nice_datetime($note['date_added'])); ?>"><?php echo safe_output(time_ago_in_words($note['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></abbr>
								</div>
								<?php } else { ?>
								<div class="pull-right">
									<?php echo safe_output(ucwords($note['owner_name'])); ?>&nbsp;<abbr title="<?php echo safe_output(nice_datetime($note['date_added'])); ?>"><?php echo safe_output(time_ago_in_words($note['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></abbr>
								</div>
								<?php } ?>
						<div class="clearfix"></div>



					</div>
				</div>
			<?php $i++; } ?>
		<?php } ?>
		<?php if ($auth->get('view_ticket_reverse')) { ?>
			<div class="page-header">
				<h4><?php echo safe_output($language->get('Ticket')); ?></h4>
			</div>
			<?php include('include_view_ticket.php'); ?>
		<?php } ?>

		<?php if (!$auth->get('view_ticket_reverse')) { ?>
			<?php include('include_view_add_reply.php'); ?>
		<?php } ?>

		<?php $plugins->run('view_ticket_content_finish', $ticket); ?>
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
