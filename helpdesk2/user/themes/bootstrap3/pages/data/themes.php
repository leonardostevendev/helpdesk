<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if ($auth->can('manage_system_settings')) {
	echo json_encode($themes->get());
}
else {
	echo json_encode(array());
}
?>