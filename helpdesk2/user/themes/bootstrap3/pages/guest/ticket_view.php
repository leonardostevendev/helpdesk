<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('View Guest Ticket'));
$site->set_config('container-type', 'container');

$id 			= (int) $url->get_item();

$access_key 	= '';
if (isset($_GET['access_key'])) {
	$access_key	= $_GET['access_key'];
}

if ($id == 0 && !empty($access_key)) {
	header('Location: ' . $config->get('address') . '/guest/');
	exit;
}

$t_array['id']				= $id;
$t_array['get_other_data'] 	= true;
$t_array['limit']			= 1;
$t_array['access_key']		= $access_key;

$tickets_array = $tickets->get($t_array);

if (count($tickets_array) == 1) {
	$ticket = $tickets_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/guest/');
	exit;
}

$notes_array = $ticket_notes->get(array('ticket_id' => (int)$ticket['id'], 'get_other_data' => true, 'private' => 0));

$custom_field_groups		= $ticket_custom_fields->get_groups(array('enabled' => 1));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<div class="col-md-3">
		<div class="well well-sm">
			<h4 class="pull-left"><?php echo safe_output($language->get('Ticket')); ?></h4>

			<div class="clearfix"></div>
			
			<label class="left-result"><?php echo safe_output($language->get('User')); ?></label>
			<p class="right-result">
				<?php echo safe_output(ucwords($ticket['owner_name'])); ?>
			</p>
			<div class="clearfix"></div>
			
			<label class="left-result"><?php echo safe_output($language->get('Status')); ?></label>
			<p class="right-result"><?php if ($ticket['state_id'] == 1) { echo $language->get('Open'); } else { echo $language->get('Closed'); } ?></p>
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
			
			<?php $plugins->run('view_ticket_details_finish'); ?>

		</div>
	
		
		<div class="well well-sm">
	
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('User Details')); ?></h4>
			</div>
			
			<div class="clearfix"></div>

			<?php if ($ticket['user_id'] == 0) { ?>		
				<label class="left-result"><?php echo safe_output($language->get('Name')); ?></label>
				<p class="right-result"><?php echo safe_output(ucwords($ticket['name'])); ?></p>
				<div class="clearfix"></div>
				
				<label class="left-result"><?php echo safe_output($language->get('Email')); ?></label>
				<p class="right-result"><a href="mailto:<?php echo safe_output($ticket['email']); ?>"><?php echo safe_output($ticket['email']); ?></a></p>
				<div class="clearfix"></div>
			<?php } else { ?>	
				<label class="left-result"><?php echo safe_output($language->get('Name')); ?></label>
				<p class="right-result"><?php echo safe_output(ucwords($ticket['owner_name'])); ?></p>
				<div class="clearfix"></div>
				
				<label class="left-result"><?php echo safe_output($language->get('Email')); ?></label>
				<p class="right-result"><a href="mailto:<?php echo safe_output($ticket['owner_email']); ?>"><?php echo safe_output($ticket['owner_email']); ?></a></p>
				<div class="clearfix"></div>
			
				<?php if (!empty($ticket['owner_phone'])) { ?>
					<label class="left-result"><?php echo safe_output($language->get('Phone')); ?></label>
					<p class="right-result"><?php echo safe_output($ticket['owner_phone']); ?></p>
					<div class="clearfix"></div>
				<?php } ?>
			<?php } ?>
			
			<?php $plugins->run('view_ticket_user_details_finish'); ?>

		</div>
		
		<?php $files = $tickets->get_files(array('id' => $ticket['id'], 'private' => 0)); ?>
		<?php if (count($files) > 0) { ?>
			<div class="well well-sm">
				<h4><?php echo safe_output($language->get('Files')); ?></h4>
				<ul>
					<?php foreach ($files as $file) { ?>
						<li><a href="<?php echo $config->get('address'); ?>/guest/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>&amp;access_key=<?php echo safe_output($ticket['access_key']); ?>"><?php echo safe_output($file['name']); ?></a></li>
					<?php } ?>
				</ul>
			</div>
		<?php } ?>

		<?php $plugins->run('view_ticket_sidebar_finish'); ?>
		
	</div>
	
	<div class="col-md-9">

		<?php if ((int) $ticket['merge_ticket_id'] != 0) { ?>
			<div class="alert alert-warning">
				<strong><?php echo html_output($language->get('This ticket was merged into another ticket.')); ?></strong>
				<div class="clearfix"></div>
			</div>		
		<?php } ?>
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h1 class="panel-title"><?php echo safe_output($ticket['subject']); ?></h1>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">
				<?php if ($config->get('gravatar_enabled')) { ?>
					<div class="pull-right">
						<?php $gravatar->setEmail($ticket['owner_email']); ?>
						<img src="<?php echo $gravatar->getUrl(); ?>" alt="Gravatar" />
					</div>
				<?php } ?>
				<?php if ($ticket['html'] == 1) { ?>
					<?php echo html_output($ticket['description']); ?>
				<?php } else { ?>
					<p><?php echo nl2br(safe_output($ticket['description'])); ?></p>
				<?php } ?>
			
				<div class="clearfix"></div>
			
				<?php $site->view_custom_field_values(array('ticket' => $ticket)); ?>		
			</div>
		</div>	


		<?php if (!empty($notes_array)) { ?>
			<h4><?php echo safe_output($language->get('Replies')); ?></h4>
			<br />
			<?php $i = 0; foreach($notes_array as $note) { ?>
				<div class="panel <?php if ($i % 2 == 0 ) { echo 'panel-default'; } else { echo 'panel-info'; } ?>">
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
								<div class="pull-right">
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
						<div class="pull-right">
							<small>
								<?php if ($note['user_id'] == 0) { ?>	
									<?php echo safe_output(ucwords($note['name']) . ' <' . $note['email'] . '>'); ?> - <abbr title="<?php echo safe_output(nice_datetime($note['date_added'])); ?>"><?php echo safe_output(time_ago_in_words($note['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></abbr>								
								<?php } else { ?>
									<?php echo safe_output(ucwords($note['owner_name'])); ?> - <abbr title="<?php echo safe_output(nice_datetime($note['date_added'])); ?>"><?php echo safe_output(time_ago_in_words($note['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></abbr>
								<?php } ?>
							</small>
							
						</div>
						<div class="clearfix"></div>		
					</div>
				</div>
			<?php $i++; } ?>		
		<?php } ?>
		
				
		<a name="addnote"></a>

		<ul class="nav nav-tabs">
			<li class="active"><a href="#"><?php echo safe_output($language->get('Reply')); ?></a></li>
		</ul>
		<div class="clearfix"></div>
		<br />
			
		<form method="post" role="form" enctype="multipart/form-data" action="<?php echo $config->get('address'); ?>/guest/ticket_addnote/<?php echo (int) $ticket['id']; ?>/?access_key=<?php echo safe_output($ticket['access_key']); ?>">
			
			<div class="col-md-7">
			
				<p><textarea class="wysiwyg_enabled" name="description" cols="70" rows="12"></textarea></p>
				
				<div class="clearfix"></div>
				
				<div class="pull-right">
					<p>
						<input type="hidden" name="id" value="<?php echo (int) $ticket['id']; ?>" />
						<button name="add" type="submit" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></button>
					</p>
				</div>
				<div class="clearfix"></div>

			</div>

			<div class="col-md-5">
				<div class="well well-sm">

					<div id="ticket_attach_file_form">
						<?php if ($config->get('storage_enabled')) { ?>
							<div class="pull-left">
								<h4><?php echo safe_output($language->get('Attach File')); ?></h4>
							</div>
							<div class="pull-right">
								<a href="#" id="add_extra_file" class="btn btn-info btn-sm"><span class="glyphicon glyphicon-plus"></span> <?php echo safe_output($language->get('Attach More')); ?></a>	
							</div>
							<div class="clearfix"></div>

							<div class="form-group">		
							    <div class="col-lg-10">								
									<div class="pull-left"><input name="file[]" type="file" /></div>									
									<div id="attach_file_area"></div>
								</div>
							</div>
								
							<div class="clearfix"></div>
						<?php } ?>
					</div>

					<?php if ($ticket['state_id'] != 2) { ?>
						<h4><?php echo safe_output($language->get('Change State')); ?></h4>
						<label class="checkbox">
							<input type="checkbox" name="close_ticket" value="1" /> <?php echo safe_output($language->get('Close Ticket')); ?>
						</label>
					<?php } ?>
				</div>
			</div>

			<div class="clearfix"></div>

		</form>
		
		<div class="clearfix"></div>

	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>