<?php
use Doctrine\Common\ClassLoader;


namespace Clickpdx\Core\DependencyInjection;

use \Clickpdx\Core\System\Settings; 

class DependencyInjectionContainer
{
	/**
	 * var parameters
	 *
	 * Parameters/Settings.
	 *
	 * Pass parameters or other settings to this DIC.
	 * These are typically settings unique to this particular installation.
	 */
	protected static $parameters = array();
	
	/**
	 * var shared, static
	 *
	 * Shared objects.
	 *
	 * Store instantiated objects statically 
	 * so we don't waste resources re-instantiating them.
	 */
	protected static $shared = array();
	
	public function __construct(array $parameters = array())
	{
		self::$parameters = $parameters;
	}
	
	/**
	 * Session init.
	 *
	 * Initialize a Session for this user/User-Agent.
	 */
	public function getSessionHandler()
	{
    if (isset(self::$shared['sessionHandler']))
    {
      return self::$shared['sessionHandler'];
    }

    $params = array(
    	'cookieName' 			=> Settings::get('session.cookie_name','OCDLA_SessionId'),
    	'cookieDomain' 		=> Settings::get('session.cookie_domain','.ocdla.org'),
    	'cookiePath'			=> Settings::get('session.cookie_path','/'),
    	'cookieExpiry' 		=> Settings::get('session.cookie_expiry',60*60*24*30),
    );
    
		return self::$shared['sessionHandler'] = new \Ocdla\Session($params);
	}
	
	/**
	 *
	function user_load($uid=null)
	{
		global $sess;
		$uid = isset($uid)?$uid:$sess->getUserID();
		return User::newFromUid($uid);
	}
	*/
	public function getUserHandler()
	{
    if (isset(self::$shared['userHandler']))
    {
      return self::$shared['userHandler'];
    }
    \Clickpdx\Core\User\User::setSessionHandler($this->getSessionHandler());
		return self::$shared['userHandler'] = new \Clickpdx\Core\User\User();
	}

	/** 
	 * Theme Engine
	 *
	 * The loader has to be accessed regularly to alter
	 * how template files are loaded.
	 */
	public function getThemeEngine()
	{
    if (isset(self::$shared['themeEngine']))
    {
      return self::$shared['themeEngine'];
    }
		return self::$shared['themeEngine'] = new \Clickpdx\Core\Theme\TwigThemeEngine($this->getRenderEngine());
	}	
	

	
	/** 
	 * Theme layer.
	 *
	 * Initialize a theme helper using Twig.  Twig will parse all of our templates.
	 */
	// Twig_Autoloader::register();
	public function getRenderEngine()
	{
		global $twig;
    if (isset(self::$shared['renderEngine']))
    {
      return self::$shared['renderEngine'];
    }
		return self::$shared['renderEngine'] = $twig = new \Twig_Environment();
	}
	
	
	public function getOutputRenderer()
	{
    if (isset(self::$shared['outputRender']))
    {
      return self::$shared['outputRender'];
    }
		return self::$shared['renderEngine'] = new \Clickpdx\Core\Output\HtmlHtml($this->getThemeEngine());
	}
	
	public function getMailTransportSettings() {}
	
	public function getMailer()
	{
    if (isset(self::$shared['mailer']))
    {
      return self::$shared['mailer'];
    }
		$settings = array(
			'domain' => $_SERVER['SERVER_NAME'],
			'from' => EMAIL_FROM,
			'reply_to' => EMAIL_RETURN_PATH
		);
		return self::$shared['mailer'] = \Clickpdx\Core\Mail::newFromMailerAttributes($settings);
	}
}