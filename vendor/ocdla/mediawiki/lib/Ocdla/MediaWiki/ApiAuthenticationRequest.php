<?php


namespace Ocdla\MediaWiki;

use Ocdla\Http\LodCookie as LodCookie;
use Ocdla\Http\LodTestCookie as LodTestCookie;
use Ocdla\MediaWikiException\AuthenticationException as AuthenticationException;

/*
$MediaWikiApiResponseCodes = array(
"NoName" => "This user did not provide a username or the username parameter (lgname) was not provided to the API.",
"Illegal" => "The provided username was illegal.",
"NotExists" => "The username you provided doesn't exist",
"EmptyPass" => "You didn't set the lgpassword parameter or you left it empty.",
"WrongPass" => "The password you provided was incorrect.",
"WrongPluginPass" => "Same as WrongPass, returned when an authentication plugin rather than MediaWiki itself rejected the password.",
"CreateBlocked" => "The wiki tried to automatically create a new account for you, but your IP address has been blocked from account creation.",
"Throttled" => "You've logged in too many times in a short time.",
"Blocked" => "User is blocked.",
"mustbeposted" => "The login module requires a POST request.",
"NeedToken" => "Either you did not provide the login token or the sessionid cookie. Request again with the token and cookie given in this response."
);
*/

class ApiAuthenticationRequest
{

	protected $apiUrl;
	protected $uid;
	
	protected $debug = false;
	
	protected $success;
	protected $stages;
	protected $headers;
	protected $username;
	protected $password;
	
	// full path to cookie file
	protected $cookie;
	protected $cookiefile;
	
	protected $initial_request_body;
	protected $confirm_request_body;
	protected $initial_response_body;
	protected $confirm_response_body;
	
	protected $lgtoken;
	protected $sessionid;
	protected $cookieprefix;
	protected $UserID;
	protected $UserName;
	protected $ConfirmedToken;
	
	const API_RESPONSE_FORMAT = 'xml';
	
	public function setUid($uid){
		$this->uid = $uid;
	}
	
	public function setEndpoint($apiUrl){
		$this->apiUrl = $apiUrl;
	}
	
	public function __construct( $apiUrl, $UserID, $username, $password )
	{	
		$this->apiUrl = $apiUrl;
		$this->cookie = new LodCookie($UserID);
		$this->cookiefile = $this->cookie->getFilePath();
	
		$this->success 			= false;
		$this->username 		= $username;
		$this->password 		= $password;
	
		$this->stages = array(
			'init' => array(
				'action'			=> 'login',
				'lgname'			=> $username,
				'lgpassword'	=> $password,
				'format'			=> self::API_RESPONSE_FORMAT,
			),
			'confirm' => array(
				'action'			=> 'login',
				'lgname'			=> $username,
				'lgpassword'	=> $password,
				'format'			=> self::API_RESPONSE_FORMAT,
			),
		);
	}
	
	public function execute()
	{
		$this->sendInitialRequest();
	
		$this->sendAuthenticationRequest();

		$this->setMediaWikiAuthCookies();
		
		if( !$this->getStatus() )
		{
			throw new AuthenticationException( $this->confirm_request_body, $this->confirm_response_body );
		}
	}
	
	protected function sendInitialRequest()
	{
		ttail("cURL stages:\n".print_r( $this->stages,true),'mediawikiapi');

		$init_body = \formatRequestBody( $this->stages['init'] );
		//	print_r($init_body);
		// send the initial request	
		$init = \cinit( $this->apiUrl, $init_body, $this->cookiefile );
		//		print_r($init);exit;
		$headers = explode("\r\n\r\n",$init['header']);
		ttail( 'Headers are: '.print_r($headers,TRUE),'mediawikiapi');
		$this->initial_response_body = \parseResponse( $init['response_body'] );
		ttail( 'SSO initial response: '.print_r($this->initial_response_body,TRUE),'mediawikiapi' );
		$this->lgtoken = $this->initial_response_body['token'];
	}
	
	protected function getResult()
	{
		return $this->confirm_response_body['result'];
	}
	public function getStatus()
	{
		return $this->success;
	}
	protected function sendAuthenticationRequest()
	{
		// confirm the request
		$this->confirm_request_body = \formatRequestBody($this->stages['confirm']+array('lgtoken'=>$this->lgtoken));
		ttail('Second request body is: '.print_r($this->confirm_request_body,true),'mediawikiapi');
	
		$confirm = \cinit( $this->apiUrl, $this->confirm_request_body, $this->cookiefile );
		$this->confirm_response_body = \parseResponse( $confirm['response_body'] );
		ttail( 'SSO confirm response:'.print_r($this->confirm_response_body, TRUE),'mediawikiapi' );
		$this->success = $this->confirm_response_body['result'] == 'Success' ? true : false;
	}
	public function getInfo()
	{
		$info = print_r( $this->confirm_response_body, true);
		return $info;
	}
	public function setMediaWikiAuthCookies()
	{
		// set any remaining cookies
		$cookies = array( 'sessionid' => 'session', 'lguserid'=>'UserID', 'lgusername' => 'UserName', 'lgtoken' => 'Token');
		foreach( $cookies AS $responsekey => $cookiename )
		{
			setcookie( $this->confirm_response_body['cookieprefix'] . $cookiename, $this->confirm_response_body[$responsekey], time() + 2592000, "/", ".ocdla.org" );
		}
	}


}