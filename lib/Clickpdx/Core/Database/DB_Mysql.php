<?php

namespace Clickpdx\Core\Database;

class DB_Mysql
{
	protected $user;
	protected $pass;
	protected $dbhost;
	protected $dbname;
	protected $dbh; // Database connection handle
		
	public function __construct($user, $pass, $dbhost, $dbname)
	{
		$this->user = $user;
		$this->pass = $pass;
		$this->dbhost = $dbhost;
		$this->dbname = $dbname;
	}	
	public function __toString()
	{
		return ("Class: ".__CLASS__." Host: {$this->dbhost} Db: {$this->dbname} User: {$this->user} Pass: ***");
	}
	protected function connect()
	{
		$this->dbh = \mysql_connect($this->dbhost, $this->user, $this->pass);
		if(!\is_resource($this->dbh)) {
			throw new \Exception(\mysql_error());
		}
		if(!mysql_select_db($this->dbname, $this->dbh)) {
			throw new \Exception("Database {$this->dbname} doesn't exist.");
		}
		mysql_set_charset('utf8');
	}
	
	public function prepare( $query ) {
		if(!$this->dbh)
		{
			$this->connect();
		}
		return new DB_MysqlStatement( $this->dbh, $query );
	}
	
	public function execute( $query ) {
		if( !$this->dbh ) {
			$this->connect();
		}
		if( !$ret ) {
			throw new \Exception;
		}
		elseif( !is_resource( $ret ) ) {
			return TRUE;
		} else {
			$stmt = new DB_MysqlStatement( $this->dbh, $query );
			// $stmt->result = $ret;
			return $stmt;
		}
	}		
}


class DB_MysqlStatement {
	protected $result;
	public $binds;
	public $query;
	private $actual_query;
	protected $dbh;

	public function getQuery()
	{
		return $this->actual_query;
	}
	
	public function __construct( $dbh, $query ) {
		$this->query = $query;
		$this->dbh = $dbh;
		if(!is_resource($dbh)){
			throw new \Exception('Not a valid database connection.');
		}
	}
	
	public function getParams(){
		return $this->binds;
	}
	
	private function parameterValueQuote($val)
	{
		return ("'".\mysql_real_escape_string($val)."'");
	}
	
	private function parameterValueSqlFormat($vals)
	{
		if(is_array($vals))
		{
			$return = array_map(function($val){
				return $this->parameterValueQuote($val);
				},$vals);
			return implode(',',$return);
		}
		else return $this->parameterValueQuote($vals);
	}
	
	public function execute() {
		$args = func_get_args();
		if( is_array($args[0]) ) 
		{
			$binds = $args[0];
		}
		else
		{
			$binds = $args;
		}
		$query = $this->query;
		if($binds)
		{
			foreach($binds AS $index => $value)
			{
				$this->binds[$index + 1] = str_replace(':','\:',$value);  
			}
			$patterns = array();
			$replacements = array();
			foreach ($this->binds AS $ph => $pv)
			{
				$patterns[] = "/(?<!\\\\):$ph/";
				$replacements[] = $this->parameterValueSqlFormat($pv);
			}
			$query = \preg_replace($patterns, $replacements, $query);
		}
		$this->actual_query = $query;
		$this->result = \mysql_query($query, $this->dbh);
		if(!$this->result)
		{
			throw new \Exception('<h2>There was an error executing the query:</h2>'.'<pre>'.$query."</pre>With parameters:<br /><pre> ".print_r($binds,true)."</pre>". "<h3>The Error was: </h3><pre>". \mysql_error()."</pre>");
		}
		return $this;
	}
	
	public function	fetch_row()
	{
		if(!$this->result) throw new \Exception('Query not executed.');
		return mysql_fetch_row($this->result);
	}
	
	public function fetch_assoc()
	{
		return mysql_fetch_assoc($this->result);
	}

	public function fetchall_assoc() {
		$retval = array();
		while($row = $this->fetch_assoc()) {
			$retval[] = $row;
		}
		return $retval;
	}
}