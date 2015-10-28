<?php
// namespace Stringifier;
function entity_toString($entity)
{
	if(!is_array($entity))
	{
		return $entity;
	}
	return "<pre>".print_r($entity,true)."</pre>";
}

function tokenize($str,$tokens)
{
	foreach($tokens as $token=>$replace)
	{
		$str=str_replace('{'.$token.'}',$replace,$str);
	}
	return $str;
}