<?php

namespace Clickpdx\Core\Http;
/**
 * @class Request
 * @author - Jose Bernal
 * @date - 2013-01-08
 *
 **/
 
class Server {
	private $Hostname;
	private $Ip;
	public function _construct() {
		$this->Ip = NULL;
	}	
	public function setHostname( $string ) {
		$this->Hostname = $string;	
	}
	public function getHostname() {
		return $this->Hostname;
	}
}