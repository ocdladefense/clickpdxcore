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
	
	
	public function getMailTransportSettings()
	{
		
	}
	
	public function getMailer($recipients, $subject=null, $message=null)
	{
    if (isset(self::$shared['mailer']))
    {
      return self::$shared['mailer'];
    }
		$recipients = isset($recipients)?$recipients:$this->parameters['mailer.defaultRecipient'];
		return self::$shared['mailer'] = 
			new \Clickpdx\Core\Mail($recipients,$subject,$message);
	}
}