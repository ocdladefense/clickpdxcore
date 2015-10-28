<?php

namespace Clickpdx;

class InvalidLoginException extends \Exception
{
	public function __construct($msg)
	{
		parent::__construct($msg);
	}
}