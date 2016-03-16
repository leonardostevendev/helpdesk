<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if (!$auth->can('api_access')) {
	exit;
}

$id = (int) $url->get_item();

if (isset($_POST['delete'])) {
	$user_api_keys->delete(array('id' => $id, 'where' => array('user_id' => $auth->get('id'))));
	exit;
}
?>
