<?php

namespace Clickpdx\Core;

class Mail
{
	private $recipients;
	
	private $from;
	
	private $subject;
	
	private $headers;
	
	private $message;
	
	private $HeadersText;
	
	private $sender = 'admin@members.ocdla.org';
	
	public function __construct($recipients = array(), $subject=null, $message = null)
	{
		$this->domain = $_SERVER['SERVER_NAME'];
		$this->headers = array(
			'MIME-Version: 1.0',
			'Content-type: text/plain; charset=utf-8',
			'Content-Disposition: inline',
			"From: {$this->sender}"
		);

		$this->recipients = is_array($recipients)?implode(',',$recipients):$recipients;
		
		$messageId = "Message-Id: <".time() .'-' . md5($this->sender . $this->recipients) . '@'.$this->domain . ">";
		
		$this->addHeader($messageId);
		$this->subject = $subject;
		$this->message = $message;
	}
	
	public function addHeader($string)
	{
		$this->headers[]=$string;
	}
	
	public function setSender($sender)
	{
		$this->sender = $sender;
	}
	
	public function setDomain($domain)
	{
		$this->domain = $domain;
	}
	
	public function send()
	{	
		return \mail($this->recipients, $this->subject, $this->message, implode("\r\n",$this->headers));
	}
}