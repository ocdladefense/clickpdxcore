<?php

namespace Clickpdx\Core\Exception;

class DatabaseException extends \Exception
{
	private $db;
	
	private $table;
	
	private $user;
	
	private $connectionInfo;

	private $host;
	
	public function __construct($msg)
	{
		parent::__construct($msg);
	}
	
	public function setHost($host)
	{
		$this->host = $host;
	}
	
	public function setDb($db)
	{
		$this->db = $db;
	}
	
	public function setUser($user)
	{
		$this->user = $user;
	}
	
	public function __toString()
	{
		$str = array();
		$str[] = "Host: {$this->host}";
		$str[] = "Database: {$this->db}";
		$str[] = "User: {$this->user}";
		$str = "<h3>Connection info:</h3>".implode('<br />',$str);
		return parent::__toString() . $str;
	}
}