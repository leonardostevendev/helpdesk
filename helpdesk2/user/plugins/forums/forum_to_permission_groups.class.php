<?php
/**
 * 	Forum Section Permissions
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace sts\plugins;
use sts;

class forum_to_permission_groups extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('forum_to_permission_groups');
		$this->allowed_columns(
				array(
					'section_id',
					'group_id'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}

}


?>