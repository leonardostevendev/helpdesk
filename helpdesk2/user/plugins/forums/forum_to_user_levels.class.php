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

namespace sts\plugins;
use sts;

class forum_to_user_levels extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('forum_to_user_levels');
		$this->allowed_columns(
				array(
					'section_id',
					'user_level'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}

}


?>