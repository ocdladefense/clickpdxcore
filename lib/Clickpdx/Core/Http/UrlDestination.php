<?php

namespace Clickpdx\Core\Http;


class UrlDestination {

	private $url = '';
	
	private $querystring;
	
	private $server;

	public function __construct($url) {
		$this->url = $url;
	}
	
	public function setServer($server){
		$this->server = $server;
	}
	public function setProtocol($proto){
		$this->protocol = $proto;
	}
	public function setDestination($url){
		$this->destination = $url;
	}
	public function getDestination(){
		return $this->url;
	}
	protected function setQueryString($querystring){
		$this->querystring = $querystring;
	}
}