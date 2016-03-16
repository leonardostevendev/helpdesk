<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Users'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_users')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['impersonate'])) {
	if ($auth->can('manage_system_settings')) {

		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = $auth->get('name') . ' (' . $auth->get('username') . ')' . ' Impersonated User ID: ' . (int) $_POST['impersonate_user_id'];
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'impersonate';
		$log_array['event_source'] = 'users';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;

		$log->add($log_array);

		$_SESSION['user_data']['id']		 	= (int) $_POST['impersonate_user_id'];
		$_SESSION['user_data']['impersonated'] 	= 1;

		header('Location: ' . $config->get('address') . '/');
		exit;
	}
}

$get_array = array();

$get_array['get_other_data'] = true;

if (isset($_GET['filter'])) {
	if (isset($_GET['like_search']) && !empty($_GET['like_search'])) {
		$get_array['like_search'] 	= $_GET['like_search'];
		$like_search_temp			= $_GET['like_search'];
	}
	if (isset($_GET['group_id']) && !empty($_GET['group_id'])) {
		$get_array['group_id'] 	= (int) $_GET['group_id'];
		$group_id_temp			= $_GET['group_id'];
	}
	if (isset($_GET['allow_login']) && $_GET['allow_login'] != '') {
		$get_array['allow_login'] 	= (int) $_GET['allow_login'];
		$allow_login_temp			= $_GET['allow_login'];
	}
}

$users_array = $users->get($get_array);

if (SAAS_MODE) {
	$paid_users_count			= $users->count(array('not_group_ids' => array(0, 1)));
}

$groups 		= $permission_groups->get();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<div class="col-md-3">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Users')); ?></h4>
				</div>
				<div class="pull-right">
					<a href="<?php echo $config->get('address'); ?>/users/add/" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></a>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">
				<p class="left-result"><?php echo safe_output($language->get('Users')); ?></p>
				<p class="right-result"><?php echo count($users_array); ?></p>

				<div class="clearfix"></div>

			<?php if (SAAS_MODE) { ?>
				<p class="left-result"><?php echo safe_output($language->get('Paid Users')); ?></p>
				<p class="right-result"><?php echo (int) ($paid_users_count); ?></p>

				<div class="clearfix"></div>

				<p class="left-result"><?php echo safe_output($language->get('Max Paid Users')); ?></p>
				<p class="right-result"><?php echo (int) SAAS_MAX_USERS; ?></p>

				<div class="clearfix"></div>
			<?php } ?>
			</div>

		</div>

		<div class="panel panel-default">

				<form method="get" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
				<div class="panel-heading">
						<input type="text" class="form-control" placeholder="<?php echo safe_output($language->get('Search')); ?>" name="like_search" value="<?php if (isset($like_search_temp)) echo safe_output($like_search_temp); ?>" size="15" />
					<div class="clearfix"></div>
				</div>

				<div class="panel-body">
					<label class="left-result"><?php echo safe_output($language->get('Permissions')); ?></label>
					<p class="right-result">
						<select name="group_id">
							<option value="">&nbsp;</option>
							<?php foreach($groups as $group) { ?>
								<option value="<?php echo (int) $group['id']; ?>"<?php if (isset($group_id_temp) && $group_id_temp == $group['id']) echo ' selected="selected"'; ?>><?php echo safe_output($group['name']); ?></option>
							<?php } ?>
						</select>
					</p>

					<div class="clearfix"></div>

					<label class="left-result"><?php echo safe_output($language->get('Allow Login')); ?></label>
					<p class="right-result">
						<select name="allow_login">
							<option value="">&nbsp;</option>
							<option value="0"<?php if (isset($allow_login_temp) && $allow_login_temp == 0) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if (isset($allow_login_temp) && $allow_login_temp == 1) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select>
					</p>

					<div class="clearfix"></div>
					<br />
					<div class="pull-right">
							<button type="submit" name="filter" class="btn btn-info"><?php echo safe_output($language->get('Filter')); ?></button> <a href="<?php echo safe_output($config->get('address')); ?>/users/" class="btn btn-default"><?php echo safe_output($language->get('Clear')); ?></a>
					</div>
					<div class="clearfix"></div>
				</div><!-- panel-body -->
			</form>
		</div>

		<?php if ($auth->can('manage_system_settings')) { ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Impersonate User')); ?></h4>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">
				<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

					<label class="left-result"><?php echo safe_output($language->get('User')); ?></label>
					<p class="right-result">
						<select name="impersonate_user_id">
							<option value="">&nbsp;</option>
							<?php foreach ($users_array as $user) { ?>
								<?php if ($user['allow_login'] == 1) { ?>
									<option value="<?php echo (int) $user['id']; ?>"><?php echo safe_output($user['name'] . ' (' . $user['username'] . ')'); ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</p>

					<div class="clearfix"></div>
					<br />

					<div class="pull-right">
							<button type="submit" name="impersonate" class="btn btn-primary"><?php echo safe_output($language->get('Impersonate')); ?></button>
					</div>
				</form>
			</div>
		</div>
		<?php } ?>
	</div>

	<div class="col-md-9">

		<section id="no-more-tables">
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th><?php echo safe_output($language->get('Name')); ?></th>
						<th><?php echo safe_output($language->get('Email')); ?></th>
						<th><?php echo safe_output($language->get('Username')); ?></th>
						<th><?php echo safe_output($language->get('Permissions')); ?></th>
						<th><?php echo safe_output($language->get('Added')); ?></th>
					</tr>
				</thead>
				<?php
					$i = 0;
					foreach ($users_array as $user) {
				?>
				<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
					<td data-title="<?php echo safe_output($language->get('Name')); ?>"><a href="<?php echo $config->get('address'); ?>/users/view/<?php echo (int) $user['id']; ?>/"><?php echo safe_output($user['name']); ?></a></td>
					<td data-title="<?php echo safe_output($language->get('Email')); ?>"><?php echo safe_output($user['email']); ?></td>
					<td data-title="<?php echo safe_output($language->get('Username')); ?>"><?php echo safe_output($user['username']); ?></td>
					<td data-title="<?php echo safe_output($language->get('Permissions')); ?>"><?php echo safe_output($user['permission_group_name']); ?></td>
					<td data-title="<?php echo safe_output($language->get('Added')); ?>"><abbr title="<?php echo safe_output(nice_datetime($user['date_added'])); ?>"><?php echo safe_output(time_ago_in_words($user['date_added'])); ?> <?php echo safe_output($language->get('ago')); ?></abbr></td>
				</tr>
				<?php $i++; } ?>
			</table>
		</section>

	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
