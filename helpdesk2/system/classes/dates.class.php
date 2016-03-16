<?php
/**
 * 	Database Class
 *	Copyright Dalegroup Pty Ltd 2015
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <support@dalegroup.net>
 */


namespace sts;

class dates {

	function __construct() {
		
	}
	
	public function current_financial_year() {
		return $this->financial_year(0);
	}
	
	public function previous_financial_year() {
		return $this->financial_year(-1);
	}
	
	/*
		- 1 = Previous Year
		- 2 = Year Before Previous Year
		0 	= Current Financial Year
	*/
	
	public function financial_year($previous_year) {
		
		$now = strtotime(datetime());
		
		$month 	= date('m', $now);
		$year 	= date('Y', $now);
		
		$year_now 			= $previous_year + 1;
		$previous_year_2 	= $previous_year - 1;
		
		if ($month >= 7) {
			$start 	= date('Ymd', strtotime(($year + $previous_year) . '-07-01'));
			$end	= date('Ymd', strtotime(($year + $year_now) . '-06-30'));
		}
		else {
			$start 	= date('Ymd', strtotime(($year + $previous_year_2) . '-07-01'));
			$end	= date('Ymd', strtotime(($year + $previous_year) . '-06-30'));
		}
		
		return array('start' => $start, 'end' => $end);

	}
	

	public function months_between($array) {
	
		$startDate = strtotime($array['start_year'] . '/' . $array['start_month'] . '/01');
		$endDate   = strtotime($array['end_year'] . '/' . $array['end_month'] . '/01');

		$currentDate = $endDate;

		$return = array();
		while ($currentDate >= $startDate) {
			$return[] = array('year' => date('Y',$currentDate), 'month' => date('m',$currentDate));
			$currentDate = strtotime( date('Y/m/01/',$currentDate).' -1 month');
		}
		return array_reverse($return);
	}
	
}
?>