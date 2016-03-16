<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;


$id = (int) $url->get_item();

if ($id == 0) {
	echo 'Error';
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

$file_mode = 1;
if (isset($_GET['note_id'])) {
	$file_mode = 2;
}
else if (isset($_GET['action']) && ($_GET['action'] == 'all_files')) {
	$file_mode = 3;
}


/*
	Ticket Files
*/

$public_files_tmp = $tickets->get_files(array('id' => $ticket['id'], 'private' => 0));

foreach($public_files_tmp as &$tmp) {
	if ($file_mode == 1) {
		$tmp['highlight'] = 1;	
	}
	else {
		$tmp['highlight'] = 0;
	}
}

$private_files_tmp = array();
if ($auth->can('tickets_view_private_replies')) {
	$private_files_tmp = $tickets->get_files(array('id' => $ticket['id'], 'private' => 1));
	
	foreach($private_files_tmp as &$tmp2) {
		if ($file_mode == 1) {
			$tmp2['highlight'] = 1;	
		}
		else {
			$tmp2['highlight'] = 0;
		}
	}
	
}

/*
	Note Files
*/

$public_files_tmp_2 = $ticket_notes->get_files(array('ticket_id' => $ticket['id'], 'private' => 0));

foreach($public_files_tmp_2 as &$tmp3) {
	if ($file_mode == 2 && $_GET['note_id'] == $tmp3['ticket_note_id']) {
		$tmp3['highlight'] = 1;	
	}
	else {
		$tmp3['highlight'] = 0;
	}
}

$private_files_tmp_2 = array();
if ($auth->can('tickets_view_private_replies')) {
	$private_files_tmp_2 = $ticket_notes->get_files(array('ticket_id' => $ticket['id'], 'private' => 1));
	
	foreach($private_files_tmp_2 as &$tmp4) {
		if ($file_mode == 2 && $_GET['note_id'] == $tmp4['ticket_note_id']) {
			$tmp4['highlight'] = 1;	
		}
		else {
			$tmp4['highlight'] = 0;
		}
	}
	
}

$public_files = array_merge($public_files_tmp, $public_files_tmp_2);
$private_files = array_merge($private_files_tmp, $private_files_tmp_2);

?>

<link href="<?php echo safe_output($config->get('address')); ?>/system/libraries/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo safe_output($config->get('address')); ?>/system/libraries/datatables/js/jquery.dataTables.min.js"></script>

<script type="text/javascript"> 
	$(document).ready(function () {
		var table = $('.sts_datatable').DataTable({
			paging: false
		});
			
		$('.dataTables_filter input').attr("placeholder", "Search");
		
		var $gallery = $("a[rel=modal-gal]").colorbox(
			{
				photo:true,
				scalePhotos: true,
				width: '60%',
				height: '75%'
			}
		);
		
		$("#open-modal-gal").click(function(e){
			e.preventDefault();
			$("a[rel=modal-gal]").eq(0).click();
		});	
	
		<?php if ($auth->can('tickets_view_private_replies')) { ?>
			var $gallery = $("a[rel=modal-private-gal]").colorbox(
				{
					photo:true,
					scalePhotos: true,
					width: '60%',
					height: '75%'
				}
			);
			
			$("#open-modal-private-gal").click(function(e){
				e.preventDefault();
				$("a[rel=modal-private-gal]").eq(0).click();
			});	
		<?php } ?>
	});
</script>	
							
<!-- Modal -->
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title">
				<?php echo $language->get('Attachments'); ?> - <?php echo safe_output($ticket['subject']); ?>
			</h4>
		</div>
		<div class="modal-body">
		
			<ul class="nav nav-tabs">
				<li <?php if (!isset($_GET['type'])) { ?>class="active"<?php } ?>><a href="#public_attachments" data-toggle="tab"><?php echo safe_output($language->get('Attachments')); ?></a></li>
				<?php if ($auth->can('tickets_view_private_replies')) { ?>
					<li <?php if (isset($_GET['type']) && $_GET['type'] == 'private') { ?>class="active"<?php } ?>><a href="#private_attachments" data-toggle="tab"><?php echo safe_output($language->get('Private Attachments')); ?></a></li>
				<?php }?>
			</ul>	
			
			<div class="tab-content">
				<div class="tab-pane <?php if (!isset($_GET['type'])) { ?>active<?php } ?>" id="public_attachments">
					<section id="no-more-tables">				
						<table class="table table-striped sts_datatable">
							<thead>
								<tr>
									<th><?php echo $language->get('Added'); ?></th>
									<th><?php echo $language->get('Name'); ?></th>
									<th><?php echo $language->get('Size'); ?></th>
									<th><?php echo $language->get('User'); ?></th>
									<th><?php echo $language->get('View'); ?></th>
								</tr>
							</thead>
							
							<tbody>
								<?php									
									foreach($public_files as $file) { ?>
									<tr>
										<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('Added'); ?>"><?php echo safe_output(time_ago_in_words($file['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></td>
										<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('Name'); ?>">
											<?php if (isset($file['ticket_note_id'])) { ?>
												<a rel="gal-1" href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>&amp;note_id=<?php echo (int) $file['ticket_note_id']; ?>"><?php echo safe_output($file['name']); ?></a>											
											<?php } else { ?>
												<a rel="gal-1" href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>"><?php echo safe_output($file['name']); ?></a>
											<?php } ?>
										</td>
										<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('Size'); ?>"><?php echo safe_output($storage->get_file_size($file)); ?><?php echo safe_output($language->get('MB')); ?></td>
										<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('User'); ?>"><?php echo safe_output($file['user_fullname']); ?></td>
										<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('View'); ?>">
											<?php if (isset($file['ticket_note_id'])) { ?>
												<a rel="modal-gal" href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>&amp;note_id=<?php echo (int) $file['ticket_note_id']; ?>&amp;action=view" title="<?php echo safe_output($file['name']); ?> - <?php echo safe_output(time_ago_in_words($file['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?>"><span class="glyphicon glyphicon-eye-open"></span></a>											
											<?php } else { ?>
												<a rel="modal-gal" href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>&amp;action=view" title="<?php echo safe_output($file['name']); ?> - <?php echo safe_output(time_ago_in_words($file['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?>"><span class="glyphicon glyphicon-eye-open"></span></a>
											<?php } ?>
										</td>
									</tr>			
								<?php } ?>
							</tbody>
						</table>
					</section>
				</div>
				
				<?php if ($auth->can('tickets_view_private_replies')) { ?>
					<div class="tab-pane <?php if (isset($_GET['type']) && $_GET['type'] == 'private') { ?>active<?php } ?>" id="private_attachments">
						<section id="no-more-tables">				
							<table class="table table-striped sts_datatable">
								<thead>
									<tr>
										<th><?php echo $language->get('Added'); ?></th>
										<th><?php echo $language->get('Name'); ?></th>
										<th><?php echo $language->get('Size'); ?></th>
										<th><?php echo $language->get('User'); ?></th>
										<th><?php echo $language->get('View'); ?></th>
									</tr>
								</thead>
								
								<tbody>
									<?php
										foreach($private_files as $file) { ?>
										<tr>
											<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('Added'); ?>"><?php echo safe_output(time_ago_in_words($file['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></td>
											<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('Name'); ?>">
												<?php if (isset($file['ticket_note_id'])) { ?>
													<a rel="gal-1" href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>&amp;note_id=<?php echo (int) $file['ticket_note_id']; ?>"><?php echo safe_output($file['name']); ?></a>
												<?php } else { ?>
													<a rel="gal-1" href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>"><?php echo safe_output($file['name']); ?></a>												
												<?php } ?>
											</td>
											<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('Size'); ?>"><?php echo safe_output($storage->get_file_size($file)); ?><?php echo safe_output($language->get('MB')); ?></td>
											<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('User'); ?>"><?php echo safe_output($file['user_fullname']); ?></td>
											<td <?php if ($file['highlight']) { ?>class="alert-warning"<?php } ?> data-title="<?php echo $language->get('View'); ?>">
												<?php if (isset($file['ticket_note_id'])) { ?>
													<a rel="modal-private-gal" href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>&amp;note_id=<?php echo (int) $file['ticket_note_id']; ?>&amp;action=view" title="<?php echo safe_output($file['name']); ?> - <?php echo safe_output(time_ago_in_words($file['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?>"><span class="glyphicon glyphicon-eye-open"></span></a>
												<?php } else { ?>
													<a rel="modal-private-gal" href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>&amp;action=view" title="<?php echo safe_output($file['name']); ?> - <?php echo safe_output(time_ago_in_words($file['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?>"><span class="glyphicon glyphicon-eye-open"></span></a>												
												<?php } ?>
											</td>
										</tr>			
									<?php } ?>
								</tbody>
							</table>
						</section>
					</div>
				<?php } ?>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn btn-default" data-dismiss="modal"><?php echo safe_output($language->get('Close')); ?></a>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
