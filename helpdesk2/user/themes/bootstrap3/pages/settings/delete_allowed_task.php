<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if (!$auth->can('manage_system_settings')) {
	exit;
}

if (isset($_GET['group_id']) && isset($_GET['task_id'])) {
	$groups		= $permission_groups->get(array('id' => (int) $_GET['group_id']));
	
	if ($groups[0]['allow_modify']) {
		$permissions->remove_task_from_group(array('task_id' => (int) $_GET['task_id'], 'group_id' => (int) $_GET['group_id']));
	}
	
	header('Location: ' . $config->get('address') . '/settings/edit_permission_group/' . (int) $_GET['group_id'] . '/');
	exit;
}
?>
