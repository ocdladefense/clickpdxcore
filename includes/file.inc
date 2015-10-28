<?php

function drupalLoadFile($file)
{
	if(file_exists($file))
	{
		include($file);
	}
}

function drupalGetDir($path)
{
	$parts=removeLastElement(explode('/',$path));
	return implode('/',$parts);
}

function removeLastElement($arr)
{
	array_pop($arr);
	return $arr;
}