<?php


function getSessionName()
{
	return system_get_setting('session_cookie_name');
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
				"SessionID='{$_COOKIE['OCDLA_SessionId']}'"
			)
		)//params
	);//$session
	// tail( $query->getQuery() );
	$query->exec();
}//update_session


function session_session_start() {
	// global $session;
	
	// check for a valid session first
	if( !session_has_valid_session() ) throw new InvalidOCDLASessionException('The OCDLA Session is Invalid.');
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
				"my_aspnet_Sessions.SessionID='{$_COOKIE['OCDLA_SessionId']}'"
			),
		)//$params
	);//$query
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
	if( !isset($_COOKIE['OCDLA_SessionId']) ) return false;
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
				"my_aspnet_Sessions.SessionID='{$_COOKIE['OCDLA_SessionId']}'"
			)
		)//$params
	);//$session
	$results = $query->exec();
	if( $query->getNumRows() > 0 ) return true;
	else return false;
}



function session_regenerate_session() {
	/**
	 * assume that the ASP.Net_SessionId cookie was set
	 * since all paths to the login page go through the ASPSessionCheck.aspx file
	 *
	 */
	if( !isset($_COOKIE['OCDLA_SessionId']) ) {
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
			 setcookie('OCDLA_SessionId', '', time()-3600,'/','.ocdla.org');
		
		// Finally, destroy the session.
		session_destroy();
		usleep(1000000);
		
		// setcookie( 'OCDLA_SessionId', $_COOKIE['ASP_NET_SessionId'], time()+60*60*24*30, '/', '.ocdla.org');
}