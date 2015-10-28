<?php

namespace Clickpdx\Core;

class Mail {


	private $recipients;
	private $from;
	private $subject;
	private $headers;
	private $message;
	private $HeadersText;
	

	public function __construct( $recipients = array(), $subject, $message = null) {
		$this->headers = array('MIME-Version: 1.0','Content-type: text/plain; charset=utf-8','Content-Disposition: inline','From: web@pacinfo.com');
		$message_id = "Message-Id: <".time() .'-' . md5($sender . $recipient) . '@'.$_SERVER['SERVER_NAME'] . ">";
		$this->addHeader( $message_id );
		$this->recipients = $recipients;
		$this->subject = $subject;
		$this->message = $message;
	}
	public function addHeader( $string ) {
		$this->headers[]=$string;
	}
	public function send() {
		$this->HeadersText = implode("\r\n",$this->headers);	
		$sent = mail($this->recipients, $this->subject, $this->message, $this->HeadersText);
	}//method send
}//class Mail