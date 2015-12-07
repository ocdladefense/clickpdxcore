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
	
	private $baseUrl;
	
	protected $logArray = array();
	
	protected $user;
	
	protected $settings = array();
	
	public function setContainer($container)
	{
		global $request, $user, $base_url;
		$this->user = $user;
		$this->container = $container;
		$this->request = $request;
		$this->baseUrl = $base_url;
	}
	
	public function initSettings(array $settings)
	{
		$this->settings = $settings;
	}
	
	protected function setting($key,$value=null)
	{
		return $this->settings[$key];
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
	
	protected function redirect($url)
	{
		\clickpdx_goto($url);
	}
	
	protected function createUrl($url)
	{
		return $this->baseUrl . '/' . $url;
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