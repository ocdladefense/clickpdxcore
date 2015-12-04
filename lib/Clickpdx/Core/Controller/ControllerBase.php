<?php

namespace Clickpdx\Core\Controller;

class ControllerBase
{
	/**
	 * var container
	 *
	 * The Dependency Injection Container
	 *
	 * The DIC should be assigned here so we can 
	 * access container services such as the mailer,
	 * session, database connection, etc.
	 */
	private $container;
	
	protected $request;
	
	protected $logArray = array();
	
	protected $user;
	
	public function setContainer($container)
	{
		global $request, $user;
		$this->user = $user;
		$this->container = $container;
		$this->request = $request;
	}
	
	protected function getUser()
	{
		return $this->user;
	}
	
	protected function mail($recipients,$subject,$message)
	{
		$mailer = $this->container->getMailer($recipients,$subject,$message);
		return $mailer->send();
	}
	
	protected function log($msg)
	{
		$this->logArray[] = $msg;
	}
	
	protected function getLog()
	{
		return implode('<br />',$this->logArray);
	}
}