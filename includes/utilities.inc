<?php
// utilities.inc

class Curl {
	protected $ch;
	protected $response;
	protected $header;
	protected $response_body;
	protected $response_header;
	
	public function __construct( $url, $post_body = null, $cookiefile = null ) {
		$this->ch = curl_init($url);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_ENCODING, "UTF-8" );
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_VERBOSE, 0);
		curl_setopt($this->ch, CURLOPT_HEADER, 1);
	}


	public function exec() {
		$response = curl_exec($this->ch);
		$header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
		$this->response_header = substr($response, 0, $header_size);
		$this->response_body = substr($response, $header_size);
		curl_close ($this->ch);
		return $this;
	}
	
	public function toString() {
		return $this->response_body;
	}
	
	public function setCookie( $cookieFile ) {
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $cookiefile);
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $cookiefile);
		return $this;	
	}
	
	public function setPostFields( $post_body = null ) {
		curl_setopt ($this->ch, CURLOPT_POST, true);
		curl_setopt( $this->ch, CURLOPT_ENCODING, "UTF-8" );
		curl_setopt ($this->ch, CURLOPT_POSTFIELDS, $post_body);
		return $this;
	}
}


class StringWrapper {
	public function __construct() {}
	
	static function getWords( $string = null, $offset = 0, $length = null ) {
		if( is_null( $string ) ) return false;
		$split = explode(' ',$string);
		$slice = array_slice( $split, $offset, $length );
		return implode(' ',$slice);
	}
}