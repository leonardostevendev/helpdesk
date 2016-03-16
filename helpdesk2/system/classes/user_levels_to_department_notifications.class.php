<?php
/**
 * 	Forum Sections
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts;

class user_levels_to_department_notifications extends table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('user_levels_to_department_notifications');
		$this->allowed_columns(
				array(
					'department_id',
					'user_level',
					'type'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}

}


?>