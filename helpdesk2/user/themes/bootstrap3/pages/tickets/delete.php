<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$id = (int) $url->get_item();

if (isset($_POST['delete'])) {
	if ((int) $id != 0) {

		if ($auth->can('manage_tickets') || $auth->can('tickets_delete')) {

			$allowed = true;
			if (!$auth->can('manage_tickets')) {
				if (!$tickets_support->can(array('action' => 'view', 'id' => $id))) {
					$allowed = false;
				}
			}

			if ($allowed) {
				$tickets->delete(array('id' => $id));
			}

		}
	}
}

?>