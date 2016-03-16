<?php
/**
 * 	Facebook Authentication Class
 *	Copyright Dalegroup Pty Ltd 2013
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts;


class auth_facebook {
	
	private $config 	= array();
	//private $user		= array();
	private $facebook;
	
	function __construct() {	

		$config 			= &singleton::get(__NAMESPACE__ . '\config');
		$error 				= &singleton::get(__NAMESPACE__ . '\error');
		
		if ($config->get('facebook_enabled')) {

			define('FACEBOOK_SDK_V4_SRC_DIR', LIB . '/facebook/src/Facebook/');
			
			try {
				require(LIB . '/facebook/autoload.php');
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'facebook_sdk', 'message' => $e->getMessage()));
			}
					
			\Facebook\FacebookSession::setDefaultApplication($config->get('facebook_app_id'), $config->get('facebook_app_secret'));
			
		}
	}

	public function get_user($array = NULL) {
		
		$config 			= &singleton::get(__NAMESPACE__ . '\config');
	
		// see if a existing session exists
		if (isset($_SESSION) && isset($_SESSION['fb_token'])) {
		
			// create new session from saved access_token
			$session = new \Facebook\FacebookSession($_SESSION['fb_token']);
			// validate the access_token to make sure it's still valid
			try {
				if (!$session->validate()) {
					$session = null;
				}
			} catch (Exception $e) {
				// catch any exceptions
				$session = null;
			}
		} else {
			$helper = new \Facebook\FacebookRedirectLoginHelper($array['url']);
					
			try {
				$session = $helper->getSessionFromRedirect();
			} catch(FacebookRequestException $ex) {
				// When Facebook returns an error
			} catch(\Exception $ex) {
				// When validation fails or other local issues
			}
		}
		
		if (isset($session) && $session) {
			try {
				$user_profile = (new \Facebook\FacebookRequest($session, 'GET', '/me'))->execute()->getGraphObject(\Facebook\GraphUser::className());
				//echo "Name: " . $user_profile->getName();
				
				$_SESSION['fb_token'] = $session->getToken();
				
				return $user_profile;
				
			} catch(FacebookRequestException $e) {
				//echo "Exception occured, code: " . $e->getCode();
				//echo " with message: " . $e->getMessage();
				return false;
			}
		
		}
		else {
			return false;
		}
	
	


		
	}
	
	public function get_login_url($array) {
		$config 			= &singleton::get(__NAMESPACE__ . '\config');
		
		$helper = new \Facebook\FacebookRedirectLoginHelper($array['url']);
		$loginUrl = $helper->getLoginUrl();

		return $loginUrl;
	}
	
	public function get_logout_url() {
		return call_user_func_array(array($this->facebook, 'getLogoutUrl'), func_get_args());
	}
	
	/*
	public function api() {
		return call_user_func_array(array($this->facebook, 'api'), func_get_args());
	}
	*/
	
	public function link_profile($facebook_id) {
		$users 			= &singleton::get(__NAMESPACE__ . '\users');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$log			= &singleton::get(__NAMESPACE__ . '\log');

	
		$count = $users->count(array('facebook_id' => $facebook_id));
	
		if ($count == 0) {
			$log_array['event_severity'] 		= 'notice';
			$log_array['event_number'] 			= E_USER_NOTICE;
			$log_array['event_description'] 	= 'Facebook Profile Linked "<a href="' . $config->get('address') . '/users/view/' . $auth->get('id') . '/">'.'Unknown User'.'</a>"';
			$log_array['event_file'] 			= __FILE__;
			$log_array['event_file_line'] 		= __LINE__;
			$log_array['event_type'] 			= 'link_profile';
			$log_array['event_source'] 			= 'auth_facebook';
			$log_array['event_version'] 		= '1';
			$log_array['log_backtrace'] 		= false;	
					
			$log->add($log_array);
		
			$update_array['facebook_id'] 		= $facebook_id;
			$update_array['id']					= $auth->get('id');
			
			$users->edit($update_array);
			
			$auth->load();
			
			return true;
		}
		else {
			return false;
		}	
	}

}

?>