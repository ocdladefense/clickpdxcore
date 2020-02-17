<?php

namespace Clickpdx\Core;

use \Clickpdx\Core\System\Settings; 



class Application
{
	private $dic;
	
	private $userId;
	
	private $user;
	
	private $output;
	
	public $headers_sent = array();
	
	public $headers = array();
	
	
	
	public static function returnBestGuessContext()
	{	
		return new \Clickpdx\Core\Http\HttpRequest();
	}
	
	public static function newFromContext($context)
	{
		$app = new Application(new \Clickpdx\Core\DependencyInjection\DependencyInjectionContainer());
		return $app->setContext($context);
	}
	
	public function __construct($dic)
	{
		$this->dic = $dic;
	}
	
	public function setContext($context)
	{
		global $request;
		$this->request = $request = $context;
		return $this;
	}
	
	/**
	 * Session init.
	 *
	 * Initialize a Session for this user/User-Agent.
	 */
	public function loadSession()
	{
		global $sess;
		
    $params = array(
    	'cookieName' 				=> Settings::get('session.cookie_name','OCDLA_SessionId'),
    	'cookieDomain' 			=> Settings::get('session.cookie_domain','.ocdla.org'),
    	'cookiePath'				=> Settings::get('session.cookie_path','/'),
    	'cookieExpiry' 			=> Settings::get('session.cookie_expiry',60*60*24*30),
    	'cookieSameSite' 		=> Settings::get('session.cookie_samesite','None'),
    	'cookieSecure'	 		=> Settings::get('session.cookie_secure',true)
    );
    
    // ini_get('session.save_handler');
		$this->sess = $sess = $this->dic->getSessionHandler($params);   
		
		session_name($params['cookieName']);
		
		if(php_get_version() < 7) {
			session_set_cookie_params(
				$params["cookieExpiry"],
				$params["cookiePath"],
				$params["cookieDomain"]
			);

		} else {
			session_set_cookie_params(array(
				'lifetime'			=> $params['cookieExpiry'],
				'path' 					=> $params['cookiePath'],
				'domain'				=> $params['cookieDomain'],
				'SameSite'			=> $params['cookieSameSite'],
				'Secure'				=> $params['cookieSecure']
			));
		} 
		

		session_start(); 
		
		if(headers_sent()) {
			$this->headers_sent = headers_list();
		} else {

			foreach(headers_list() as $header) {
				$pair = explode(':',$header,2);
				// print_r($pair);print "<br />";
				if($pair[0] == "Set-Cookie") {
					$pair[1] .= "; SameSite=None; Secure";
					header(implode(':',$pair));
				}
			}
			
			$this->headers = headers_list();
		}

	}

	/**
	 * User object.
	 *
	 * Load the user object associated with this request.
	 * User can either be an Anonymous user (if not authenticated)
	 * Or an authenticated user with a valid autoId from the <members> table.
	 */
	public function loadUser()
	{
		global $user, $UserID;
		$this->user = $user = \user_load();
		$this->userId = $UserID = $user->getMemberId();
	}


	public function addRoutes(array $routeArgs)
	{
		foreach($routeArgs as $key => $router)
		{
//			print get_class($router);
		//	if(get_class($router)=="Closure") print 'foo';
			/**
			 * Add a menu item programmatically.
			 *
			 * $key, $function, $args
			 *
			 */
			\menu_item_add(array(
				$key=>$router->bindTo($this->dic)));//$routeArgs);
		}
	}
	/**
	 * Process a Route
	 *
	 * For the purposes of this function, a "route"
	 * is a programming *concept: here a route can
	 * be inferred from the global $request object, a 
	 * specific path, a given Route object a simply a callback
	 * function.
	 */
	public function processRoute($routeArg=null)
	{
		global $path;
		if(is_array($routeArg))
		{
			$foo = each($routeArg);
			if(is_callable($foo['value']))
			{
				\menu_item_add($routeArg);
				$requestedRoute = $foo['key'];
				// return $foo['value']();
			}
		}
		else if(isset($routeArg))
		{
			$requestedRoute = $routeArg;
		}
		/**
		 * Set the path
		 *
		 * Establish the path based on the `q` environment variable.
		 */
		$requestedRoute = isset($requestedRoute)?$requestedRoute:$_GET['q'];
		$this->path = $path = \drupal_get_path($requestedRoute,false,$form_state_path);

		/**
		 * Process the path.
		 *
		 * Process the given path again the available menu items.
		 * Returning the appropriate page not found or access denied errors.
		 */
		// $this->routingService = new \Clickpdx\Routing\RouteProcessor($this->container->getOutputRenderer());
		Routing\RouteProcessor::setContainer($this->dic);
		return $this->output = Routing\RouteProcessor::clickpdx_process_router($path);
	}
	
	public function writeOutput($stdOut=true)
	{
		
	}
	
	public function emailOutput($out)
	{
		return $this->dic->getMailer()->mail($this->settings->getDefaultRecipient(),"Output of {$this->getPath()}",$out);
	}
	
	public function close()
	{
		/**
		 * System messages.
		 *
		 * Messages have already been displayed. No need to display them twice.
		 */
		unset( $_SESSION['messages'] );
	}
}