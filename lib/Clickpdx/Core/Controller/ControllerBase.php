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
	
	protected $logArray = array();
	
	public function setContainer($container)
	{
		$this->container = $container;
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