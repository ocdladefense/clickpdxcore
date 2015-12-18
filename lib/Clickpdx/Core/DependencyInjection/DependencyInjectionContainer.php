<?php

namespace Clickpdx\Core\DependencyInjection;

class DependencyInjectionContainer
{
	/**
	 * var parameters
	 *
	 * Parameters/Settings.
	 *
	 * Pass parameters or other settings to this DIC.
	 * These are typically settings unique to this particular installation.
	 */
	protected $parameters = array();
	
	/**
	 * var shared, static
	 *
	 * Shared objects.
	 *
	 * Store instantiated objects statically 
	 * so we don't waste resources re-instantiating them.
	 */
	protected static $shared = array();
	
	public function __construct(array $parameters = array())
	{
		$this->parameters = $parameters;
	}
	
	/**
	 * Session init.
	 *
	 * Initialize a Session for this user/User-Agent.
	 */
	public function getSessionHandler()
	{
    if (isset(self::$shared['sessionHandler']))
    {
      return self::$shared['sessionHandler'];
    }
		return self::$shared['sessionHandler'] = new \Ocdla\Session();
	}
	
	/**
	 *

	function user_load($uid=null)
	{
		global $sess;
		$uid = isset($uid)?$uid:$sess->getUserID();
		return User::newFromUid($uid);
	}
	*/
	public function getUserHandler()
	{
    if (isset(self::$shared['userHandler']))
    {
      return self::$shared['userHandler'];
    }
    \Clickpdx\Core\User\User::setSessionHandler($this->getSessionHandler());
		return self::$shared['userHandler'] = new \Clickpdx\Core\User\User();
	}
	
	
	public function getMailTransportSettings() {}
	
	public function getMailer()
	{
    if (isset(self::$shared['mailer']))
    {
      return self::$shared['mailer'];
    }
		$settings = array(
			'domain' => $_SERVER['SERVER_NAME'],
			'from' => EMAIL_FROM,
			'reply_to' => EMAIL_RETURN_PATH
		);
		return self::$shared['mailer'] = \Clickpdx\Core\Mail::newFromMailerAttributes($settings);
	}
}