<?php
/**
 * 	Dalegroup Framework
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *  Core Functions
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts;

/**
 * Class Auto Loader. Allows php class files to be included automatically when constructing a class.
 *
 * @param   string   $class_name The name of the class to be included (excluding .class.php)
 */
function class_auto_load($class_name) {
	//echo $class_name . '<br />';
	
	$class_name = \str_replace(__NAMESPACE__, '', $class_name);
	$class_name = \str_replace('\\', '', $class_name);
	if (file_exists((CLASSES . '/' . $class_name . '.class.php'))) {
		require(CLASSES . '/' . $class_name . '.class.php');
	}
}

/**
 * This function is called at the shutdown of the PHP file.
 * This is currently only used to close the session (and only really needed on buggy versions of PHP)
 *
 */
function shutdown() {
	
	//fixes a bug with certain PHP installs
	session_write_close();
}

/**
 * Returns the UTC date in MySQL datetime format.
 *
 * @param   int   	$add_seconds The number of seconds you wish to add to the returned datetime.
 * @return  string	The UTC datetime value.
 */
function datetime_utc($add_seconds = 0, $format = 'Y-m-d H:i:s') {
	$base_time = time() + (int) $add_seconds;
	return gmdate($format, $base_time);
}

/**
 * Returns the date in MySQL datetime format based on the currently set timezone.
 *
 * @param   int   	$add_seconds The number of seconds you wish to add to the returned datetime.
 * @return  string	The datetime value.
 */
function datetime($add_seconds = 0, $format = 'Y-m-d H:i:s') {
	$base_time = time() + (int) $add_seconds;
	return date($format, $base_time);
}

function thedate($add_seconds = 0, $format = 'Y-m-d') {
	switch($format) {
		case 'YYYY-MM-DD':
			$format = 'Y-m-d';
		break;
		
		case 'DD/MM/YYYY':
			$format = 'd/m/Y';
		break;
	}

	$base_time = time() + (int) $add_seconds;
	return date($format, $base_time);
}

/**
 * Returns the current IP address of connection that requested the PHP file.
 *
 * @return  string	The ip address.
 */
function ip_address() {
	if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
		return $_SERVER['REMOTE_ADDR'];
	}
	else {
		return '';
	}
}

/**
 * Returns a random string.
 *
 * @param   int   	$length 		The length of the random string to return.
 * @param   string  $chars 			The characters included in the random string.
 * @return  string					The random string.
 */
function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
    // Length of character list
    $chars_length = (strlen($chars) - 1);

    // Start our string
    $string = $chars{rand(0, $chars_length)};
   
    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string))
    {
        // Grab a random character from our list
        $r = $chars{rand(0, $chars_length)};
       
        // Make sure the same two characters don't appear next to each other
        if ($r != $string{$i - 1}) $string .=  $r;
    }
   
    // Return the string
    return $string;
}

/**
 * Decodes a string that was encrypted using the global encryption key
 *
 * @param   string  $string 		The encrypted string
 * @return  string					The clear text string
 */
function decode($string) {
	$config 	= &singleton::get(__NAMESPACE__ . '\config');
	$key 		= $config->get('encryption_key');
	$decrypted 	= rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "\0");

	return $decrypted;
}

/**
 * Encodes a string using the global encryption key
 *
 * @param   string  $string 		The cleart text string
 * @return  string					The encrypted string
 */
function encode($string) {
	$config 	= &singleton::get(__NAMESPACE__ . '\config');
	$key 		= $config->get('encryption_key');
	$encrypted 	= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
	
	return $encrypted;
}

/**
 * Returns the next and previous page.
 *
 * Form the array like this:
 * <code>
 * $array = array(
 *   'page'    => 1,       // the current page
 *   'limit'   => 100,     // the number of items on a page
 * );
 * 
 * </code>
 *
 * @param   array   $array 			The array explained above
 * @return  array					The returned array.
 */
function paging_start($array) {

	$return_array	= array();

	$page 	= (int) $array['page'];
	$limit	= (int) $array['limit'];
	
	$offset = $page * $limit - $limit;
	
	if ($offset < 0) {
		$offset = 0;
	}
	
	$return_array['offset'] = (int) $offset;
	
	$return_array['next_page'] = $page + 1;
	
	$return_array['previous_page'] = $page - 1;
	
	if ($return_array['previous_page'] < 1) {
		$return_array['previous_page'] = 1;
	}
	if ($return_array['next_page'] < 1) {
		$return_array['next_page'] = 1;
	}
	return $return_array;
}

function paging_finish($array) {
	if ($array['events'] < (int) $array['limit']) {
		$array['next_page'] = $array['next_page'] - 1;
	}
	return $array;
}

/**
 * Returns the user agent for use when calling external web sites and services
 *
 * @return  string					The user agent (i.e. Dalegroup STS/1.1)
 */
function user_agent() {
	$config 	= &singleton::get(__NAMESPACE__ . '\config');

	$program_version	= $config->get('program_version');

	return 'Dalegroup STS/' . $program_version;
}

/**
 * Checks to see if an email address is valid
 *
 * @param   string  $email 			The email address
 * @return  bool					TRUE if the email is value or FALSE if it is not valid
 */
function check_email_address($email) {
	
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * Start the timer, works out page generation time
 *
 */
function start_timer() {
	global $sts_tstart;
	
	$sts_tstart = microtime(true);

	return true;	
}

/**
 * Returns the time it took for generation. 
 *
 * @param   int 	$accuracy 		Level of accuracy
 * @return  string					The time past since calling start_timer()
 */
function stop_timer($accuracy = 4) {
	global $sts_tstart;
	
	//fixes rando windows bug??
	$tend = microtime(true);
	$tend = microtime(true);
	
	$totaltime = number_format($tend - $sts_tstart, $accuracy);
	
	return $totaltime;
}

/**
 * Removes slashes from a value. This will remove slashes from an array too.
 *
 * @param   array  $array 			The array of values to strip
 * @return  array					The stripped array.
 */
function remove_magic_quotes($array) {

	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$array[$key] = remove_magic_quotes($value);
		}
		else {
			$array[$key] = stripslashes($value);
		}
	}

	return $array;
}

/**
 * Sets register_globals off
 *
 */ 
function unregister_globals() {
	if (!ini_get('register_globals')) {
		return true;
	}

	// Might want to change this perhaps to a nicer error
	if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
		die('GLOBALS overwrite attempt detected.');
	}

	// Variables that shouldn't be unset
	$noUnset = array('GLOBALS',  '_GET',
		'_POST',    '_COOKIE',
		'_REQUEST', '_SERVER',
		'_ENV',    '_FILES');

	$input = array_merge($_GET,    $_POST,
	$_COOKIE, $_SERVER,
	$_ENV,    $_FILES,
	
	isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());

	foreach ($input as $k => $v) {
		if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
			unset($GLOBALS[$k]);
		}
	}

	return true;
}

/**
 * Returns the last x number of days
 *
 * @param   int  $days 			The number of days to return
 * @return  array				The array of dates
 */
function last_x_days($days = 7) {
	
	$array = array();
	for($i = $days - 1; $i >= 0; $i--) {
		$array[] = date("Y-m-d", strtotime('-'. (int) $i .' day', strtotime(datetime())));
	}
	return $array;
}

/**
 * Returns the last x number of months
 *
 * @param   int  $months 		The number of months to return
 * @return  array				The array of dates
 */
function last_x_months($months = 6) {
	
	$array = array();
	for($i = $months - 1; $i >= 0; $i--) {
		$array[] = date("Y-m", strtotime('first day of this month -'. (int) $i .' months', strtotime(datetime())));
	}
	return $array;
}

/**
 * Checks a submitted date matches Y-m-d H:i
 *
 * @param   string $data 		The date to test
 * @return  bool				TRUE or FALSE
 */
function check_datetime($data) {
    if (date('Y-m-d H:i', strtotime($data)) == $data) {
        return true;
    } else {
        return false;
    }
}

/**
 * Formats a date into a human readable style
 *
 * @param   string $data 		The date to format
 * @return  string				The date in a nice format :)
 */
function nice_date($date, $utc = false) {
	if ($utc) {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		
		$date 		= new \DateTime($date, new \DateTimeZone('utc'));
		$tz 		= new \DateTimeZone($config->get('default_timezone'));
		
		$date->setTimezone($tz);
		
		return date('D M d, Y', strtotime($date->format('D M d, Y')));		
	}
	else {
		return date('D M d, Y', strtotime($date));
	}
}

/**
 * Formats a date and time into a human readable style
 *
 * @param   string $date 		The datetime to format
 * @param   string $utc 		True or False (true is input date is in UTC)
 * @return  string				The date in a nice format :)
 */
function nice_datetime($date, $utc = false) {
	if ($utc) {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		
		$date 		= new \DateTime($date, new \DateTimeZone('utc'));
		$tz 		= new \DateTimeZone($config->get('default_timezone'));
		
		$date->setTimezone($tz);
		
		return date('D M d, Y, h:i A', strtotime($date->format('D M d, Y, h:i A')));		
	}
	else {
		return date('D M d, Y, h:i A', strtotime($date));
	}
}


/**
 * Returns a UUID
 *
 */
function uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),	

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}


/**
 * Returns the timezones supported in PHP
 *
 * @return  array		The timezones in an array
 */
function get_timezones() {
	
	$tzlist = \DateTimeZone::listIdentifiers();
	
	return $tzlist;
}

/**
 * Adds an s to the end of a string if count is greater than 1
 *
 * @param   int 	$count 		The count
 * @param   string 	$text 		The text to add s to if needed
 * @return  string				The returned text
 */
function pluralize($count, $text) { 
	$language 		= &singleton::get(__NAMESPACE__ . '\language');

    return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}" . $language->get('s') ) );
}

/**
 * Time ago that the datetime occurred
 *
 */
function ago($datetime) {

	$language 		= &singleton::get(__NAMESPACE__ . '\language');

    $interval = date_create(datetime())->diff($datetime);		

    if ( $v = $interval->y >= 1 ) return pluralize( $interval->y, $language->get('year') );
    if ( $v = $interval->m >= 1 ) return pluralize( $interval->m, $language->get('month') );
    if ( $v = $interval->d >= 1 ) return pluralize( $interval->d, $language->get('day') );
    if ( $v = $interval->h >= 1 ) return pluralize( $interval->h, $language->get('hour') );
    if ( $v = $interval->i >= 1 ) return pluralize( $interval->i, $language->get('minute') );
	
    return pluralize( $interval->s, $language->get('second') );
}

function convert_encoding($string, $encoding = NULL) {

	//return convert_encoding_auto($string);

	if (isset($encoding) && !empty($encoding)) {
		$return = mb_convert_encoding($string, 'UTF-8', $encoding);
	}
	else {
		$return = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
	}
	
	return $return;
}

function convert_encoding_auto($string) {
    // detect the character encoding of the incoming file
    $encoding = mb_detect_encoding( $string, "auto" );
      
    // escape all of the question marks so we can remove artifacts from
    // the unicode conversion process
    $target = str_replace( "?", "[question_mark]", $string );
      
    // convert the string to the target encoding
    $target = mb_convert_encoding( $target, 'UTF-8', $encoding);
      
    // remove any question marks that have been introduced because of illegal characters
    $target = str_replace( "?", "", $target );
      
    // replace the token string "[question_mark]" with the symbol "?"
    $target = str_replace( "[question_mark]", "?", $target );
  
    return $target;
}

function rearrange( $arr ){
    foreach( $arr as $key => $all ){
        foreach( $all as $i => $val ){
            $new[$i][$key] = $val;   
        }   
    }
    return $new;
}

function str_replace_array(array $replace, $subject) { 
   return str_replace(array_keys($replace), array_values($replace), $subject);    
} 

function get_countries() {
	$countries = 
	array( array("name"=>"Andorra", "code"=>"AD"),
	array("name"=>"United Arab Emirates", "code"=>"AE"),
	array("name"=>"Afghanistan", "code"=>"AF"),
	array("name"=>"Antigua and Barbuda", "code"=>"AG"),
	array("name"=>"Anguilla", "code"=>"AI"),
	array("name"=>"Albania", "code"=>"AL"),
	array("name"=>"Armenia", "code"=>"AM"),
	array("name"=>"Netherlands Antilles", "code"=>"AN"),
	array("name"=>"Angola", "code"=>"AO"),
	array("name"=>"Asia/Pacific Region", "code"=>"AP"),
	array("name"=>"Antarctica", "code"=>"AQ"),
	array("name"=>"Argentina", "code"=>"AR"),
	array("name"=>"American Samoa", "code"=>"AS"),
	array("name"=>"Austria", "code"=>"AT"),
	array("name"=>"Australia", "code"=>"AU"),
	array("name"=>"Aruba", "code"=>"AW"),
	array("name"=>"Aland Islands", "code"=>"AX"),
	array("name"=>"Azerbaijan", "code"=>"AZ"),
	array("name"=>"Bosnia and Herzegovina", "code"=>"BA"),
	array("name"=>"Barbados", "code"=>"BB"),
	array("name"=>"Bangladesh", "code"=>"BD"),
	array("name"=>"Belgium", "code"=>"BE"),
	array("name"=>"Burkina Faso", "code"=>"BF"),
	array("name"=>"Bulgaria", "code"=>"BG"),
	array("name"=>"Bahrain", "code"=>"BH"),
	array("name"=>"Burundi", "code"=>"BI"),
	array("name"=>"Benin", "code"=>"BJ"),
	array("name"=>"Bermuda", "code"=>"BM"),
	array("name"=>"Brunei Darussalam", "code"=>"BN"),
	array("name"=>"Bolivia", "code"=>"BO"),
	array("name"=>"Brazil", "code"=>"BR"),
	array("name"=>"Bahamas", "code"=>"BS"),
	array("name"=>"Bhutan", "code"=>"BT"),
	array("name"=>"Bouvet Island", "code"=>"BV"),
	array("name"=>"Botswana", "code"=>"BW"),
	array("name"=>"Belarus", "code"=>"BY"),
	array("name"=>"Belize", "code"=>"BZ"),
	array("name"=>"Canada", "code"=>"CA"),
	array("name"=>"Cocos (Keeling) Islands", "code"=>"CC"),
	array("name"=>"Congo", "code"=>"CD"),
	array("name"=>"Central African Republic", "code"=>"CF"),
	array("name"=>"Congo", "code"=>"CG"),
	array("name"=>"Switzerland", "code"=>"CH"),
	array("name"=>"Cote d'Ivoire", "code"=>"CI"),
	array("name"=>"Cook Islands", "code"=>"CK"),
	array("name"=>"Chile", "code"=>"CL"),
	array("name"=>"Cameroon", "code"=>"CM"),
	array("name"=>"China", "code"=>"CN"),
	array("name"=>"Colombia", "code"=>"CO"),
	array("name"=>"Costa Rica", "code"=>"CR"),
	array("name"=>"Cuba", "code"=>"CU"),
	array("name"=>"Cape Verde", "code"=>"CV"),
	array("name"=>"Christmas Island", "code"=>"CX"),
	array("name"=>"Cyprus", "code"=>"CY"),
	array("name"=>"Czech Republic", "code"=>"CZ"),
	array("name"=>"Germany", "code"=>"DE"),
	array("name"=>"Djibouti", "code"=>"DJ"),
	array("name"=>"Denmark", "code"=>"DK"),
	array("name"=>"Dominica", "code"=>"DM"),
	array("name"=>"Dominican Republic", "code"=>"DO"),
	array("name"=>"Algeria", "code"=>"DZ"),
	array("name"=>"Ecuador", "code"=>"EC"),
	array("name"=>"Estonia", "code"=>"EE"),
	array("name"=>"Egypt", "code"=>"EG"),
	array("name"=>"Western Sahara", "code"=>"EH"),
	array("name"=>"Eritrea", "code"=>"ER"),
	array("name"=>"Spain", "code"=>"ES"),
	array("name"=>"Ethiopia", "code"=>"ET"),
	array("name"=>"Europe", "code"=>"EU"),
	array("name"=>"Finland", "code"=>"FI"),
	array("name"=>"Fiji", "code"=>"FJ"),
	array("name"=>"Falkland Islands (Malvinas)", "code"=>"FK"),
	array("name"=>"Micronesia", "code"=>"FM"),
	array("name"=>"Faroe Islands", "code"=>"FO"),
	array("name"=>"France", "code"=>"FR"),
	array("name"=>"Gabon", "code"=>"GA"),
	array("name"=>"United Kingdom", "code"=>"GB"),
	array("name"=>"Grenada", "code"=>"GD"),
	array("name"=>"Georgia", "code"=>"GE"),
	array("name"=>"French Guiana", "code"=>"GF"),
	array("name"=>"Guernsey", "code"=>"GG"),
	array("name"=>"Ghana", "code"=>"GH"),
	array("name"=>"Gibraltar", "code"=>"GI"),
	array("name"=>"Greenland", "code"=>"GL"),
	array("name"=>"Gambia", "code"=>"GM"),
	array("name"=>"Guinea", "code"=>"GN"),
	array("name"=>"Guadeloupe", "code"=>"GP"),
	array("name"=>"Equatorial Guinea", "code"=>"GQ"),
	array("name"=>"Greece", "code"=>"GR"),
	array("name"=>"South Georgia and the South Sandwich Islands", "code"=>"GS"),
	array("name"=>"Guatemala", "code"=>"GT"),
	array("name"=>"Guam", "code"=>"GU"),
	array("name"=>"Guinea-Bissau", "code"=>"GW"),
	array("name"=>"Guyana", "code"=>"GY"),
	array("name"=>"Hong Kong", "code"=>"HK"),
	array("name"=>"Heard Island and McDonald Islands", "code"=>"HM"),
	array("name"=>"Honduras", "code"=>"HN"),
	array("name"=>"Croatia", "code"=>"HR"),
	array("name"=>"Haiti", "code"=>"HT"),
	array("name"=>"Hungary", "code"=>"HU"),
	array("name"=>"Indonesia", "code"=>"ID"),
	array("name"=>"Ireland", "code"=>"IE"),
	array("name"=>"Israel", "code"=>"IL"),
	array("name"=>"Isle of Man", "code"=>"IM"),
	array("name"=>"India", "code"=>"IN"),
	array("name"=>"British Indian Ocean Territory", "code"=>"IO"),
	array("name"=>"Iraq", "code"=>"IQ"),
	array("name"=>"Iran", "code"=>"IR"),
	array("name"=>"Iceland", "code"=>"IS"),
	array("name"=>"Italy", "code"=>"IT"),
	array("name"=>"Jersey", "code"=>"JE"),
	array("name"=>"Jamaica", "code"=>"JM"),
	array("name"=>"Jordan", "code"=>"JO"),
	array("name"=>"Japan", "code"=>"JP"),
	array("name"=>"Kenya", "code"=>"KE"),
	array("name"=>"Kyrgyzstan", "code"=>"KG"),
	array("name"=>"Cambodia", "code"=>"KH"),
	array("name"=>"Kiribati", "code"=>"KI"),
	array("name"=>"Comoros", "code"=>"KM"),
	array("name"=>"Saint Kitts and Nevis", "code"=>"KN"),
	array("name"=>"Korea", "code"=>"KP"),
	array("name"=>"Korea", "code"=>"KR"),
	array("name"=>"Kuwait", "code"=>"KW"),
	array("name"=>"Cayman Islands", "code"=>"KY"),
	array("name"=>"Kazakhstan", "code"=>"KZ"),
	array("name"=>"Lao People's Democratic Republic", "code"=>"LA"),
	array("name"=>"Lebanon", "code"=>"LB"),
	array("name"=>"Saint Lucia", "code"=>"LC"),
	array("name"=>"Liechtenstein", "code"=>"LI"),
	array("name"=>"Sri Lanka", "code"=>"LK"),
	array("name"=>"Liberia", "code"=>"LR"),
	array("name"=>"Lesotho", "code"=>"LS"),
	array("name"=>"Lithuania", "code"=>"LT"),
	array("name"=>"Luxembourg", "code"=>"LU"),
	array("name"=>"Latvia", "code"=>"LV"),
	array("name"=>"Libyan Arab Jamahiriya", "code"=>"LY"),
	array("name"=>"Morocco", "code"=>"MA"),
	array("name"=>"Monaco", "code"=>"MC"),
	array("name"=>"Moldova", "code"=>"MD"),
	array("name"=>"Montenegro", "code"=>"ME"),
	array("name"=>"Madagascar", "code"=>"MG"),
	array("name"=>"Marshall Islands", "code"=>"MH"),
	array("name"=>"Macedonia", "code"=>"MK"),
	array("name"=>"Mali", "code"=>"ML"),
	array("name"=>"Myanmar", "code"=>"MM"),
	array("name"=>"Mongolia", "code"=>"MN"),
	array("name"=>"Macao", "code"=>"MO"),
	array("name"=>"Northern Mariana Islands", "code"=>"MP"),
	array("name"=>"Martinique", "code"=>"MQ"),
	array("name"=>"Mauritania", "code"=>"MR"),
	array("name"=>"Montserrat", "code"=>"MS"),
	array("name"=>"Malta", "code"=>"MT"),
	array("name"=>"Mauritius", "code"=>"MU"),
	array("name"=>"Maldives", "code"=>"MV"),
	array("name"=>"Malawi", "code"=>"MW"),
	array("name"=>"Mexico", "code"=>"MX"),
	array("name"=>"Malaysia", "code"=>"MY"),
	array("name"=>"Mozambique", "code"=>"MZ"),
	array("name"=>"Namibia", "code"=>"NA"),
	array("name"=>"New Caledonia", "code"=>"NC"),
	array("name"=>"Niger", "code"=>"NE"),
	array("name"=>"Norfolk Island", "code"=>"NF"),
	array("name"=>"Nigeria", "code"=>"NG"),
	array("name"=>"Nicaragua", "code"=>"NI"),
	array("name"=>"Netherlands", "code"=>"NL"),
	array("name"=>"Norway", "code"=>"NO"),
	array("name"=>"Nepal", "code"=>"NP"),
	array("name"=>"Nauru", "code"=>"NR"),
	array("name"=>"Niue", "code"=>"NU"),
	array("name"=>"New Zealand", "code"=>"NZ"),
	array("name"=>"Oman", "code"=>"OM"),
	array("name"=>"Panama", "code"=>"PA"),
	array("name"=>"Peru", "code"=>"PE"),
	array("name"=>"French Polynesia", "code"=>"PF"),
	array("name"=>"Papua New Guinea", "code"=>"PG"),
	array("name"=>"Philippines", "code"=>"PH"),
	array("name"=>"Pakistan", "code"=>"PK"),
	array("name"=>"Poland", "code"=>"PL"),
	array("name"=>"Saint Pierre and Miquelon", "code"=>"PM"),
	array("name"=>"Pitcairn", "code"=>"PN"),
	array("name"=>"Puerto Rico", "code"=>"PR"),
	array("name"=>"Palestinian Territory", "code"=>"PS"),
	array("name"=>"Portugal", "code"=>"PT"),
	array("name"=>"Palau", "code"=>"PW"),
	array("name"=>"Paraguay", "code"=>"PY"),
	array("name"=>"Qatar", "code"=>"QA"),
	array("name"=>"Reunion", "code"=>"RE"),
	array("name"=>"Romania", "code"=>"RO"),
	array("name"=>"Serbia", "code"=>"RS"),
	array("name"=>"Russian Federation", "code"=>"RU"),
	array("name"=>"Rwanda", "code"=>"RW"),
	array("name"=>"Saudi Arabia", "code"=>"SA"),
	array("name"=>"Solomon Islands", "code"=>"SB"),
	array("name"=>"Seychelles", "code"=>"SC"),
	array("name"=>"Sudan", "code"=>"SD"),
	array("name"=>"Sweden", "code"=>"SE"),
	array("name"=>"Singapore", "code"=>"SG"),
	array("name"=>"Saint Helena", "code"=>"SH"),
	array("name"=>"Slovenia", "code"=>"SI"),
	array("name"=>"Svalbard and Jan Mayen", "code"=>"SJ"),
	array("name"=>"Slovakia", "code"=>"SK"),
	array("name"=>"Sierra Leone", "code"=>"SL"),
	array("name"=>"San Marino", "code"=>"SM"),
	array("name"=>"Senegal", "code"=>"SN"),
	array("name"=>"Somalia", "code"=>"SO"),
	array("name"=>"Suriname", "code"=>"SR"),
	array("name"=>"Sao Tome and Principe", "code"=>"ST"),
	array("name"=>"El Salvador", "code"=>"SV"),
	array("name"=>"Syrian Arab Republic", "code"=>"SY"),
	array("name"=>"Swaziland", "code"=>"SZ"),
	array("name"=>"Turks and Caicos Islands", "code"=>"TC"),
	array("name"=>"Chad", "code"=>"TD"),
	array("name"=>"French Southern Territories", "code"=>"TF"),
	array("name"=>"Togo", "code"=>"TG"),
	array("name"=>"Thailand", "code"=>"TH"),
	array("name"=>"Tajikistan", "code"=>"TJ"),
	array("name"=>"Tokelau", "code"=>"TK"),
	array("name"=>"Timor-Leste", "code"=>"TL"),
	array("name"=>"Turkmenistan", "code"=>"TM"),
	array("name"=>"Tunisia", "code"=>"TN"),
	array("name"=>"Tonga", "code"=>"TO"),
	array("name"=>"Turkey", "code"=>"TR"),
	array("name"=>"Trinidad and Tobago", "code"=>"TT"),
	array("name"=>"Tuvalu", "code"=>"TV"),
	array("name"=>"Taiwan", "code"=>"TW"),
	array("name"=>"Tanzania", "code"=>"TZ"),
	array("name"=>"Ukraine", "code"=>"UA"),
	array("name"=>"Uganda", "code"=>"UG"),
	array("name"=>"United States Minor Outlying Islands", "code"=>"UM"),
	array("name"=>"United States", "code"=>"US"),
	array("name"=>"Uruguay", "code"=>"UY"),
	array("name"=>"Uzbekistan", "code"=>"UZ"),
	array("name"=>"Holy See (Vatican City State)", "code"=>"VA"),
	array("name"=>"Saint Vincent and the Grenadines", "code"=>"VC"),
	array("name"=>"Venezuela", "code"=>"VE"),
	array("name"=>"Virgin Islands", "code"=>"VG"),
	array("name"=>"Virgin Islands", "code"=>"VI"),
	array("name"=>"Vietnam", "code"=>"VN"),
	array("name"=>"Vanuatu", "code"=>"VU"),
	array("name"=>"Wallis and Futuna", "code"=>"WF"),
	array("name"=>"Samoa", "code"=>"WS"),
	array("name"=>"Yemen", "code"=>"YE"),
	array("name"=>"Mayotte", "code"=>"YT"),
	array("name"=>"South Africa", "code"=>"ZA"),
	array("name"=>"Zambia", "code"=>"ZM"),
	array("name"=>"Zimbabwe", "code"=>"ZW")
	);
	return $countries;
}

?>