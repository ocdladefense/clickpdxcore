<?php

namespace Clickpdx\Core;

class Application
{
	private $dic;
	
	private $userId;
	
	private $user;
	
	private $output;
	
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
		$this->sess = $sess = $this->dic->getSessionHandler();
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

	public function processRoute($route=null)
	{
		global $path;
		/**
		 * Set the path
		 *
		 * Establish the path based on the `q` environment variable.
		 */
		$route = isset($route)?$route:$_GET['q'];
		$this->path = $path = \drupal_get_path($route,false,$form_state_path);


		/**
		 * Process the path.
		 *
		 * Process the given path again the available menu items.
		 * Returning the appropriate page not found or access denied errors.
		 */
		return $this->output = \clickpdx_process_router($path);
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