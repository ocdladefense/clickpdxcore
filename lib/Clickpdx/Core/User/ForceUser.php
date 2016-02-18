<?php

namespace Clickpdx\Core\User;

use InvalidLoginException;

class ForceUser extends User
{	
	private $forceContactData;

	public function __construct(array $forceData)
	{
		// print entity_toString($forceData);
		
		$this->forceContactData = $forceData;
		
		if(empty($forceData['Ocdla_Username__c']))
			throw new \Exception('Class User: username not given.');
	}
	
	public function save()
	{
		$data = $this->forceContactData;
		// The Salesforce Id associated with this Contact
		
		$sf_Data = array();
	  // Bind data
	  $ocdlaParams = array(
	  	'id'						=> $data['Id'],
	  	'username'			=> $data['Ocdla_Username__c'],
	  	'active' 				=> 1,
	  	'name_company' 	=> $data['Ocdla_Organization__c'],
	  	'name_last' 		=> $data['LastName'],
	  	'name_first' 		=> $data['FirstName'],
	  	'name_first'		=> $data['FirstName'],
	  	'bar_number' 		=> $data['Ocdla_Bar_Number__c'],
	  	'is_member' 		=> 1,
	  	'sf_Data' 			=> \serialize($sf_Data)
	  );
	  
	  $lodParams = array(
	  	'username'			=> ucfirst($data['Ocdla_Username__c']),
	  	'name_full' 		=> $data['FirstName'] . " " .$data['LastName'],
	  	'email'					=> $data['OrderApi__Work_Email__c'],
	  );
	  
	  $ocdlaEmailParams = array(
	  	'id'						=> $data['Id'],
	  	'type' 					=> 'email',
	  	'email'					=> $data['OrderApi__Work_Email__c'],
	  	'publish'				=> 1,
	  );
	  
		
	  // exit;
		// username 
		print "Inserting new OCDLA member record with username, {$data['Ocdla_Username__c']}... <br />";
		\db_query('INSERT INTO {members} (id, username, active, name_company, name_last, name_first, bar_number, is_member, sf_Data) VALUES (:id, :username, :active, :name_company, :name_last, :name_first, :bar_number, :is_member, :sf_Data) ON DUPLICATE KEY UPDATE username=VALUES(username), active=VALUES(active), name_company=VALUES(name_company), name_last=VALUES(name_last), name_first=VALUES(name_first), bar_number=VALUES(bar_number), sf_Data=VALUES(sf_Data)',$ocdlaParams,'pdo');

		print "Inserting new OCDLA email record... <br />";
		print "Testing email will be used!... <br />";
		print "Testing email: admin@ocdla.org... <br />";
		\db_query('INSERT INTO {member_contact_info} (contact_id, type, value, publish) VALUES(:id,:type,:email,:publish) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`),publish=VALUES(publish)',$ocdlaEmailParams,'pdo');
		
		print "Inserting new Library of Defense record for LOD access... <br />";
		\db_query('INSERT INTO {lodusers} (user_name, user_real_name, user_email, user_token) VALUES(:username,:name_full,:email,"186f1c0392511e0513ec89859aea19e6") ON DUPLICATE KEY UPDATE user_token=VALUES(user_token),user_name=VALUES(user_name),user_real_name=VALUES(user_real_name), user_email=VALUES(user_email)',$lodParams,'pdo');
	  
	  print "The records were created successfully!<br />";  
	  print "You can now <a href='https://members.ocdla.org/password-reset' target='_new'>Create a password for this user</a> and test their <a href='https://auth.ocdla.org/login' target='_new'>login</a>.";
		print "<h2>Data used was:</h2>";
	  print entity_toString($data);
		return $this;

	  throw new \Exception("There was an error updating this contact.");
	}	
	
}