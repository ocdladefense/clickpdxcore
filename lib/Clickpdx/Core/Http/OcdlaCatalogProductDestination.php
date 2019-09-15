<?php

namespace Clickpdx\Core\Http;


class OcdlaCatalogProductDestination extends UrlDestination {

	private $service;
	
	static $server = 'www.ocdla.org';
	
	private $msg;
	
	static $path = '/detail_newocdla.cfm';

	public function __construct($ItemCode) {
		parent::__construct(self::$path);
		$this->ItemCode = $ItemCode;
		$this->setServer(self::$server);
		$this->setQueryString();
	}
	protected function setQueryString($str = null){
		parent::setQueryString('i='.$this->ItemCode);
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

}