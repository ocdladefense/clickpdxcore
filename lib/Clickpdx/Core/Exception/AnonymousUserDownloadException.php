<?php
namespace Clickpdx\Core\Exception;


class AnonymousUserDownloadException extends \Exception
{
	public function __construct($msg){
		parent::__construct($msg);
	}
}