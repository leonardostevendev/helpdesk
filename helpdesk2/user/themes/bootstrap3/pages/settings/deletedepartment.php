<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if (!$auth->can('manage_system_settings')) {
	exit;
}

$id = (int) $url->get_item();

if (isset($_POST['delete'])) {
	if ($id != $config->get('default_department')) {
		$ticket_departments->delete(array('id' => $id));
		exit;	
	}
}
?>
