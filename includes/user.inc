<?php
use Clickpdx\Core\User\User;


function user_has_role($string, $uid=null)
{
	global $user;
	$u = isset($uid) ? user_load($uid) : $user;
	if($string!=='admin'&&user_has_role('admin')) return true;
	return in_array($string, $u->getRoles());
}



function user_load($uid=null)
{
	global $sess;
	$uid = isset($uid)?$uid:$sess->getUserID();
	return User::newFromUid($uid);
}