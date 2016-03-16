<?php
/**
 * 	Forum Departments
 *	Copyright Dalegroup Pty Ltd 2013
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts\plugins;
use sts;

class forum_to_departments extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('forum_to_departments');
		$this->allowed_columns(
				array(
					'section_id',
					'department_id'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}

}


?>