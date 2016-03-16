<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('View API Key'));
$site->set_config('container-type', 'container');

if (!$auth->can('api_access')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$id = (int) $url->get_item();


$get_array['where']['user_id']	= $auth->get('id');
$get_array['id']				= $id;

$keys = $user_api_keys->get($get_array);

if (empty($keys)) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$item = $keys[0];


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-right">
					<p>
					<a href="<?php echo $config->get('address'); ?>/profile/" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</p>
				</div>
				
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('API Key')); ?></h4>
				</div>
							

					
				<div class="clearfix"></div>	
				
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
						<h1 class="panel-title"><?php echo safe_output($language->get('Numerics')); ?></h1>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="panel-body">
				
					<p><?php echo safe_output($language->get('Numerics support is now built in. For more information about Numerics visit the website below.')); ?>
					<br /><br />
					<a href="http://cynapse.com/numerics/" class="btn btn-default"><?php echo safe_output($language->get('Numerics Website')); ?></a></p>
					<br />
				
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th><?php echo safe_output($language->get('Description')); ?></th>
								<th><?php echo safe_output($language->get('Service Type')); ?></th>
								<th><?php echo safe_output($language->get('JSON URL')); ?></th>
							</tr>
						</thead>
						
						<tbody>
							<tr>
								<td class="centre"><?php echo safe_output($language->get('Display Your Open Assigned Tickets Count')); ?></td>
								<td class="centre"><?php echo safe_output($language->get('Custom JSON -> Number from JSON data')); ?></td>
								<td class="centre"><?php echo safe_output($config->get('address')); ?>/api/numerics/?u=<?php echo safe_output($auth->get('username'));?>&amp;k=<?php echo safe_output($item['key']); ?>&amp;a=get_count_my_assigned_tickets</td>
							</tr>	
							<?php if ($auth->can('manage_tickets')) { ?>
							<tr>
								<td class="centre"><?php echo safe_output($language->get('Display All Open Tickets Count')); ?></td>
								<td class="centre"><?php echo safe_output($language->get('Custom JSON -> Number from JSON data')); ?></td>
								<td class="centre"><?php echo safe_output($config->get('address')); ?>/api/numerics/?u=<?php echo safe_output($auth->get('username'));?>&amp;k=<?php echo safe_output($item['key']); ?>&amp;a=get_count_open_tickets</td>
							</tr>								
							<?php } ?>
						</tbody>
					</table>
				
				
					<div class="clearfix"></div>
				
				</div>
			</div>
				
		</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>