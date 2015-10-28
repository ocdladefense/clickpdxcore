<?php

namespace Http;


class Redirect {

	private $service;
	
	private $server;
	
	private $querystring = array();
	
	private $msg;
	
	private $path;
	
	
	

	public function __construct() { }
	
	public function setServer($server){
		$this->server = $server;
	}
	public function setProtocol($proto){
		$this->protocol = $proto;
	}
	public function setDestination($url){
		$this->destination = $url;
	}

}