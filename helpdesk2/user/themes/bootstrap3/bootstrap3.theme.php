<?php
/*
	Required.
	You must include these two lines at the start of your plugin.
*/
namespace sts\themes;
use sts;

class bootstrap3 {

	function __construct() {

	}
	
	/*
		This method is used to get the theme details.
		It is required.
	*/
	public function meta_data() {
		$info = array(
			'name' 						=> 'Bootstrap 3',
			'version' 					=> '2.0',
			'description'				=> '',
			'website'					=> 'http://codecanyon.net/item/tickets/2478843?ref=michaeldale',
			'author'					=> 'Dalegroup Pty Ltd',
			'author_website'			=> 'http://dalegroup.net/',
			'min_supported_version' 	=> '',
			'max_supported_version' 	=> '',
			'type'						=> 'bootstrap3'
		);

		return $info;
	}	
	
}

?>