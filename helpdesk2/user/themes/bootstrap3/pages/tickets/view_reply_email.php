<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('View Raw Email'));
$site->set_config('container-type', 'container');

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/tickets/');
	exit;
}

if (!$auth->can('manage_tickets')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$note_get_array['id']				= $id;
$note_get_array['get_other_data'] 	= true;
$note_get_array['limit']			= 1;

$notes_array = $ticket_notes->get($note_get_array);

if (count($notes_array) == 1) {
	$note = $notes_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/tickets/');
	exit;
}

$data_temp = print_r(unserialize(base64_decode($note['email_data'])), true);

$data = convert_encoding($data_temp);

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>

<div class="row">
	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Ticket Reply')); ?></h4>
			</div>

			<div class="pull-right">
				<a href="<?php echo $config->get('address'); ?>/tickets/view/<?php echo (int) $note['ticket_id']; ?>/" class="btn btn-default"><?php echo safe_output($language->get('View')); ?></a>
			</div>

			<div class="clearfix"></div>
			
		</div>
	

	</div>

	<div class="col-md-9">
	

		
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h1 class="panel-title"><?php echo safe_output($note['subject']); ?></h1>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">
<pre>
<?php echo safe_output($data); ?>
</pre>
			</div>
		</div>
	
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>