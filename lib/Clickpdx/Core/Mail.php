<?php

namespace Clickpdx\Core;

class Mail
{
	/**
	 * Static variables
	 *
	 * These members should be declared as static since they will
	 * most likely remain the same throughout multiple emails.
	 */
	private static $domain;
	
	private static $headers;

	private static $from;
	
	private static $sender;
	
	private $recipients;
	
	private $subject;
	
	private $message;
	
	public static function newFromMailerAttributes(array $settings)
	{
		self::$domain = $settings['domain'];
		self::$headers = array(
			'MIME-Version: 1.0',
			'Content-Type: text/plain; charset=utf-8',
			'Content-Disposition: inline',
			"Return-Path: {$settings['reply_to']}",
			"Reply-To: {$settings['reply_to']}",
			"From: {$settings['from']}",
		);
		self::setSender($settings['reply_to']);
		self::setFrom($settings['from']);
		return new Mail;
	}

	public function addHeaders($header)
	{
		$header = is_array($header)?$header:array($header);
		return array_merge(self::$headers,$header);
	}
	
	private static function getSender()
	{
		return self::$sender;
	}
	
	private static function getFrom()
	{
		return self::$from;
	}
	
	private static function setSender($sender)
	{
		self::$sender = $sender;
	}
	
	private static function setFrom($from)
	{
		self::$from = $from;
	}
	
	private static function formatHeaders($headers)
	{
		return implode("\r\n",$headers);
	}
	
	private static function getSendmailOptions()
	{
		return "-f".self::getSender();
	}
	
	public function send($recipients,$subject,$message)
	{	
		$recipients = is_array($recipients)?implode(',',$recipients):$recipients;
		$rand = time() .'-' . md5(self::getSender() . $recipients) . '@'.self::$domain;
		$messageId = "Message-Id: <{$rand}>";
		$refId = "X-Entity-Ref-ID: <{$rand}>";
		$headers = $this->addHeaders(array($messageId,$refId));

		return \mail($recipients, $subject, $message, self::formatHeaders($headers), self::getSendmailOptions());
	}
	
	public function __toString()
	{
		return \entity_toString(self::$headers);
	}
}