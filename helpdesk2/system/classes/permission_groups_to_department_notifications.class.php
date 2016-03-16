<?php
/**
 * 	Permission Groups to Department Notifications
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace sts;

class permission_groups_to_department_notifications extends table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('permission_groups_to_department_notifications');
		$this->allowed_columns(
				array(
					'department_id',
					'group_id',
					'type'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}

}


?>