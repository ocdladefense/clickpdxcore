<?php


namespace Ocdla
{

	function session_destroy()
	{
		// Unset all of the session variables.
		$_SESSION = array();
		// also log the user out of the LOD site by resetting LOD cookies

		// Finally, destroy the session.
		\session_destroy();
		\usleep(1000);
	}

}


namespace {

	use \Clickpdx\Core\System\Settings; 

	function getSessionName()
	{
		return Settings::get('session.cookie_name');
	}


	function user_is_authenticated($sessionid=null)
	{
		global $sess;
		return $sess->hasAuthenticatedSession();
	}

	class InvalidOCDLASessionException extends Exception {

			// Redefine the exception so message isn't optional
			public function __construct($message) {
					// some code
		
					// make sure everything is assigned properly
					parent::__construct($message);
			}

			// custom string representation of object
			public function __toString() {
					return __CLASS__ . ":{$this->message}\n";
			}
	}


	function session_update_session( $uid = null ) {
		global $user;
		
		$sessionId = $_COOKIE[\getSessionName()];
		
		$uid = isset($uid) ? $uid : $user->uid;
		
		$timeout = 60 * 60 * 24 * 30;
	
		$query = new DBQuery(
			array(
				"type" => "update",
				"tablenames" => array(
					0 => array(
						"name" => "my_aspnet_Sessions",
						"op" => "",
						"fields" => array()
					)
				),
				"fields" => array(
					"Expires" => OCDLA_SESSION_EXPIRES,
					"Timeout" => $timeout,
					"UserID" => $uid,
				),
				"where" => array(
					"SessionID='{$sessionId}'"
				)
			)//params
		);
		
		$query->exec();
	}


	function session_session_start() {
		$sessionId = $_COOKIE[\getSessionName()];
	
		// check for a valid session first
		if( !session_has_valid_session() ) {
			throw new InvalidOCDLASessionException('The OCDLA Session is Invalid.');
		}
		
		
		$query = new DBQuery(
			$params = array(
				"type" => "select",
				"tablenames" => array(
						0 => array(
						"name"		=>	"my_aspnet_Sessions",
						"op" 			=> 	"",
						"fields" 	=> 	array()
					),//table sessions
	
				),//$tablenames
				"where" => array(
					"my_aspnet_Sessions.SessionID='{$sessionId}'"
				),
			)
		);
		
		
		$results = $query->exec();
		$row = $results[0];
		$session = (object)$row;	
	}


	/**
	 * Test whether the client has a valid ASP_NET_SessionId
	 * Scripts should use the return value to determine whether
	 * to redirect the client to the ASPSessionCheck.aspx page
	 */
	function session_has_valid_session() {
		$sessionId = $_COOKIE[\getSessionName()];
		
		if( !isset($sessionId) ) return false;
		
		$query = new DBQuery(
			$params = array(
				"type" => "select",
				"tablenames" => array(
						0 => array(
						"name"		=>	"my_aspnet_Sessions",
						"op" 			=> 	"",
						"fields" 	=> 	array()
					),//table sessions
	
				),//$tablenames
				"where" => array(
					"my_aspnet_Sessions.SessionID='{$sessionId}'"
				)
			)
		);
		
		
		$results = $query->exec();
		
		
		return $query->getNumRows() > 0;
	}



	function session_regenerate_session() {
		$cookieName = \getSessionName();
		$sessionId = $_COOKIE[$cookieName];
	
		/**
		 * assume that the ASP.Net_SessionId cookie was set
		 * since all paths to the login page go through the ASPSessionCheck.aspx file
		 *
		 */
		if( !isset($sessionId) ) {
			throw new Exception('OCDLA Login: the ASP.Net_SessionId was not properly set.');
		}
		// the ASP_NET_SessionId cookie value may be set
		// but the session may no longer be valid
		// query the sessions table to make sure the session is still valid
		// if the session is no longer valid then we need to recreate it and
		// reload this page, thus recreating the session id
		// so that the new session can be validated

			// Unset all of the session variables.
			$_SESSION = array();
		
			// If it's desired to kill the session, also delete the session cookie.
			// Note: This will destroy the session, and not just the session data!
		 setcookie($cookieName, '', time()-3600,'/',Settings::get('session.cookie_domain'));
		
			// Finally, destroy the session.
			session_destroy();
			usleep(1000000);
	}

}