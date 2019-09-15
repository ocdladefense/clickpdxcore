<?php

namespace Clickpdx\Core\Exception;

class InvalidLoginException extends \Exception
{
	public function __construct($msg)
	{
		parent::__construct($msg);
	}
}