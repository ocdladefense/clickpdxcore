<?php

namespace Clickpdx\Core\Routing;

class RouteException extends \Exception
{
	public function __construct($msg)
	{
		parent::__construct($msg);
	}
}