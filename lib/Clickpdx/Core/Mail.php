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
	
	private $sender = 'info@clickpdx.com';
	
	public function __construct($recipients = array(), $subject=null, $message = null)
	{
		$this->headers = array('MIME-Version: 1.0','Content-type: text/plain; charset=utf-8','Content-Disposition: inline','From: info@clickpdx.com');
		
		$message_id = "Message-Id: <".time() .'-' . md5($this->sender . implode($recipients)) . '@'.$_SERVER['SERVER_NAME'] . ">";
		
		$this->addHeader($message_id);
		$this->recipients = is_array($recipients)?implode(',',$recipients):$recipients;
		$this->subject = $subject;
		$this->message = $message;
	}
	
	public function addHeader($string)
	{
		$this->headers[]=$string;
	}
	
	public function send()
	{
		$this->HeadersText = implode("\r\n",$this->headers);	
		return \mail($this->recipients, $this->subject, $this->message, $this->HeadersText);
	}
}