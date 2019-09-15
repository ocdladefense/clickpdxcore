<?php

namespace Clickpdx\Core\Http;
/**
 * @class Request
 * @author - Jose Bernal
 * @date - 2013-01-08
 *
 **/


	/**
	 * @Function createFullUri
	 * @Param $Uri - A relative or absolute Uri
	 * @Return String $NormalizedUri - this method will always return a String
	 * Otherwise it will throw an Exception of type MalformedUri
	 * this is most likely the case where only a `/` is passed as the parameter
	 * @Note - we need a method to return the full Uri to the current $request->ServerName
	 * @Note - if this method is passed a string that begins with 'http' it is assumed to be an already-fully-formed Uri, in which case that Uri is itself returned
*/
class Url {

	public static function createFullUri( $Uri = NULL )
	{
		if(strpos( $Uri, 'http' ) === 0 )
		{
			return $Uri;
		}
		$ServerName = $_SERVER['SERVER_NAME'];
		$IsHttps = empty( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] === 'off' ? FALSE : TRUE;
		$Protocol = $IsHttps ? 'https' : 'http';
		// $HasQuerystring = !empty( $_SERVER['QUERY_STRING'] ) ? TRUE : FALSE;
		// $Querystring = $HasQuerystring ? '?' . $_SERVER['QUERY_STRING'] : NULL;
		
		
		$ScriptPath = $Uri;
		// SCRIPT_NAME will make sure that / evaluates to /index.html or /index.php
		if(is_null($ScriptPath)) {
			$ScriptPath = $_SERVER['SCRIPT_NAME'];
		}
		if(empty($ScriptPath)) {
			throw new Exception( 'Invalid Uri pass to '.__METHOD__ );
		}
		// Perform any necessary transformations on the $Uri
		// Remove any preceding slashes from the Uri
		$ScriptPath = preg_replace( '/^\/+/', '', $ScriptPath );
		if(empty( $ScriptPath )) throw new Exception( 'Invalid Uri pass to '.__METHOD__ );
		
		$NormalizedUri = $Protocol . '://' . $ServerName . '/'. $ScriptPath;
		return $NormalizedUri;
		//		$ScriptName = $_SERVER['SCRIPT_NAME'];	// querystring
	
		// hashtag
	
		// script_path
	
		// filename
	
		// protocol
		// $protocol  = 
		// server
		$uri = "http://" . $_SERVER['SERVER_NAME'] . $path . '?' .$querystring;
		return $uri;
	
	}

}

