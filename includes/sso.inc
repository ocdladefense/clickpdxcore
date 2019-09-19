<?php
use Ocdla\MediaWiki\ApiAuthenticationRequest as ApiAuthenticationRequest;
use Ocdla\MediaWikiException\AuthenticationException as AuthenticationException;



function doesMediaWikiPreventEmailUsernames()
{
	global $wgInvalidUsernameCharacters;
	
	global $wgPreventEmailUsernames;
	
	if(isset($wgPreventEmailUsernames))
	{
		return $wgPreventEmailUsernames;
	}
	
	if(!isset($wgInvalidUsernameCharacters) || false===strpos($wgInvalidUsernameCharacters,'@'))
	{
		return false;
	}
	
	else return true;
}

function getValidMediaWikiUsername($username)
{
	if(!doesMediaWikiPreventEmailUsernames())
	{
		return ucfirst($username);
	}
	$n = strpos($username,'@');
	if(false!==$n)
	{
		$lod_username = substr($username,0,$n);
	}
	else
	{
		$lod_username=$username;
	}
	$lod_username = ucfirst($lod_username);
	return $lod_username;
}



function sso_lod_login_submit($request, $session = null)
{	
	$UserID = $session->getUserID();
	$username = $request->get('UserName');
	$lodUsername = getValidMediaWikiUsername($username);
	$password = $request->get('password');
	try
	{
		// $mwAuthTest = new ApiAuthenticationRequest(SSO_WIKI_API_TEST,$UserID,$lodUsername);
		// if ($mwAuthTest->doAuthentication())
		// {
		// 	$session->setAppStatus('lodTest',true);
		// }
		$mwAuth = new ApiAuthenticationRequest(SSO_WIKI_API,$UserID,$lodUsername,$password);
		if (true === ($lodAuth = $mwAuth->doAuthentication()))
		{
			$session->setAppStatus('lodProd',true);		
			return $lodAuth;
		}
		else return false;
	}
	catch(\Exception $e)
	{
		mail(NOTIFICATION_EMAIL,'admin@ocdla.org','MediaWiki Authentication Error',$e->getMessage());
		return false;
	}
}



function lod_user_is_logged_in()
{
 if(isset($_COOKIE[LOD_COOKIE_PREFIX.'_session']) || isset($_COOKIE[LOD_COOKIE_PREFIX.'session']))
 {
 	return true;
 }
 return false;
}


function page_sso_ocdla_login(&$vars)
{
	global $request, $user;
	$referrer = $request->getRequestValue('referrer');
	
	
	if(user_is_authenticated() && lod_user_is_logged_in())
	{
		tail( 'User is authenticated.  Redirecting to profile page...' );
		clickpdx_goto( 'https://www.ocdla.org/profiles/index.php' );
	}
	

	if(!session_has_valid_session())
	{	
		if(isset($_GET['ref']))
		{
			$location = '/ASPSessionCheck.aspx?action=forceLogin&'.$_SERVER["QUERY_STRING"];
		}
		elseif(isset($_GET['referrer']))
		{
			$location = '/ASPSessionCheck.aspx?action=forceLogin&ref=' . $referrer;// .'&'. $_SERVER["QUERY_STRING"];
		}
		else
		{
			$location = '/ASPSessionCheck.aspx?action=forceLogin&ref=' . $_SERVER['PHP_SELF'] .'&'. $_SERVER["QUERY_STRING"];
		}
		ttail('x redirecting to... '.$locationm,'lod');
		clickpdx_goto( $location );
	}
	
	
	$output = clickpdx_get_form( 'ocdla_login_sso_form' );
	$LoginMsg = _get_login_message(array('referrer' => $referrer));
	$content = "<h2>Member Login</h2><span>{$LoginMsg}</span>";
	$content .= $output;
	$content .= <<<EOF
	<p>If you cannot remember your password, <a href="mailto:info@ocdla.org">email</a> or call OCDLA.</p>
	
	<p>If you are not an OCDLA member, <a href="/shop-membr-index.shtml">JOIN HERE.</a></p>
EOF;
	return $content;
}



function _get_login_message($data = array())
{
	$LoginMsg = null;
	if( null === $data['referrer'] )
	{
		$LoginMsg = 'Please enter your OCDLA Username and Password.';		
	}
	else if(
		strpos($data['referrer'],'https://lod.ocdla.org')===0
		|| strpos($data['referrer'],'https://libraryofdefense.ocdla.org')===0
	)
	{
		$LoginMsg = 'You must be logged in to view this page of the Library of Defense.';
	}
	return $LoginMsg;
}


function ocdla_login_sso_form_validate(&$form,&$form_state)
{
	global $user;
	tail( 'validating login...' .print_r( $form_state, TRUE));
	$username = $form_state['values']['UserName'];
	$password = $form_state['values']['password'];

	if( empty($username) || empty($password) ) {
		$form_state['messages'] = array(
			array(
			'classes' => array('msg','notice'),
			'msg' => array( "Please supply both a username and password" ),
			),
		);
		clickpdx_set_message( 'You must include both a username and password.', 'error', 'alert' );
		return FALSE;	
	}

	/**
	 * test to see if we can create a new instance of the User class
	 * using the given credentials
	 */
	 // here we are basically validating the login
	try
	{
		$u = new User(
			$params = array(
				"username" => $username,
				"password" => md5($password)
			)//params
		);
	}
	catch(InvalidLoginException $e) {
		clickpdx_set_message( 'The login failed.', 'error', 'alert' );
		return FALSE;
	}
	catch(Exception $e) {
		clickpdx_set_message( 'The login failed.', 'error', 'alert' );
		return FALSE;
	}
	tail('Login validation: credentials match user with id: ' .$u->get_user_id());
	$form_state['uid'] = $u->get_user_id();
	
	return TRUE;
}


function ocdla_login_sso_form_submit( $form, &$form_state )
{
	global $user, $request;
	session_update_session($form_state['uid']);
	$user = user_load();
	
	/**
	 * assume that the ASP.Net_SessionId cookie was set
	 * since all paths to the login page go through the ASPSessionCheck.aspx file
	 *
	 */
	if(!isset($_COOKIE['OCDLA_SessionId']))
	{
		throw new Exception('OCDLA Login: the ASP.Net_SessionId was not properly set.');
	}

	// setcookie('OCDLA_SessionId', '', time()-3600,'/','.ocdla.org');
	setcookie( 'OCDLA_SessionId', $_COOKIE['OCDLA_SessionId'], time()+60*60*24*30, '/', '.ocdla.org');
	
	/**
	 * redirect to the user's intended destination
	 **/
	if( empty($form_state['values']['reff']))
	{
		$form_state['values']['reff'] = 'profiles/index.php';
	}
	
	// set the redirect value here
	$form_state['redirect'] = array(
		'type' => 'external',
		'value' => $form_state['values']['reff'],
		'protocol' => $form_state['values']['protocol'],
		'server' => $form_state['values']['server'],
	);	 
}