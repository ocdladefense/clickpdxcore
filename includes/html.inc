<?php

namespace simpleFormats;



function simpleList($in,$type='comma')
{
	return implode(HtmlFormats::getText($type),$in);
}


class HTMLFormats
{
	const comma = ', ';

	const br = '<br />';
	
	const p = '<p>';
	
	public static function getTag($str)
	{
		return constant(__CLASS__.'::'.$str);
	}
	
	public static function getText($str)
	{
		return constant(__CLASS__.'::'.$str);
	}
}