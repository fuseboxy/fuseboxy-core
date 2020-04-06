<?php
/**
 *  Define fusebox configuration
 *  ===> all things defined here could be accessed by {$fusebox->config} or F::config() later
 **/

return array(


	/**
	 *  Default page (OPTIONAL)
	 *  ===> command is in [controller].[action] format
	 *  ===> if no [action] was specified, fusebox will automatically resolve it to 'index'
	 *  ===> if set to [false], fusebox will load nothing by default
	 **/
	'defaultCommand' => 'home',


	/**
	 *  For resolving command parameter (REQUIRED)
	 *  ===> use 'fuseaction' in remembrance of original Fusebox framework
	 *  ===> feel free to use another other name
	 **/
	'commandVariable' => 'fuseaction',


	/**
	 *  Directory to load controller, model, view, etc. (REQUIRED)
	 **/
	'appPath' => dirname(__DIR__).'/',


	/**
	 *  Directory for Composer package (REQUIRED)
	 **/
	'vendorPath' => dirname(dirname(__DIR__)).'/vendor/',


	/**
	 *  For path of image, js, css, etc. (OPTIONAL)
	 **/
	'baseDir' => dirname(dirname(__DIR__)).'/',
	'baseUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) ),


	/**
	 *  Files to auto-include (OPTIONAL)
	 *  ===> using path pattern (please refer to glob function)
	 *  ===> if element is anonymous function, it will be run once
	 **/
	'autoLoad' => array(/*
		dirname(__DIR__).'/model/*.php',
	*/),


	/**
	 *  Create an associative array which combines $_GET and $_POST (OPTIONAL)
	 *  ===> allow evaluating $_GET and $_POST variables by a single token without including $_COOKIE in the mix
	 **/
	'formUrl2arguments' => true,


	/**
	 *  Controller to handle error (OPTIONAL)
	 *  ===> use by F::error() and F::pageNotFound()
	 *  ===> controller will receive {$fusebox->error} as argument
	 *  ===> error will be thrown as exception when this is not defined
	 **/
	'errorController' => dirname(__DIR__).'/controller/error_controller.php',


	/**
	 *  Upload directory (OPTIONAL)
	 *  ===> for scaffold file upload
	 *  ===> set it to 777 mode
	 **/
	'uploadDir' => dirname(dirname(__DIR__)).'/upload/',
	'uploadUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) ).'upload/',


	/**
	 *  Use beauty-url (OPTIONAL)
	 *  ===> apply F::url() to all links
	 *  ===> there will be no script name
	 *  ===> controller (if any) and action (if any) will be the first two items after base path
	 *  ===> remember to modify .htaccess if doing url-rewrite (uncomment the line 'RewriteEngine on')
	 **/
	'urlRewrite' => false,


	/**
	 *  Route setting for beauty-url (OPTIONAL)
	 *  ===> only applicable when {urlRewrite=true}
	 *  ===> using regex and back-reference to turn path-like-query-string into query-string (forward-slash will be escaped)
	 *  ===> mapped parameters will go to {$_GET} scope; un-mapped string will not be parsed
	 *  ===> first match expression will be used; so please take the sequence into consideration
	 *  ===> array-key is pattern which match {$_SERVER['REQUEST_URI']} (with or without leading slash)
	 *  ===> array-value is transformed query-string (without leading question mark)
	 **/
	'route' => array(/*
		'/article/(\d)' => 'fuseaction=article.view&id=$1',
	*/),


	/**
	 *  reCAPTCHA setting for Captcha component (OPTIONAL)
	 **/
	'captcha' => array(/*
		'siteKey'   => '',
		'secretKey' => '',
	*/),


	/**
	 *  Proxy setting for Util::httpRequest (OPTIONAL)
	 **/
	'httpProxy' => '',
	'httpsProxy' => '',


	/**
	 *  Encrypt setting for Util::crypt (OPTIONAL)
	 **/
	'encrypt' => array(/*
		'key'     => '',
		'mode'    => '',
		'cipher'  => '',
	*/),


	/**
	 *  SMTP setting for Util::mail (OPTIONAL)
	 **/
	'smtp' => array(/*
		'debug'    => 0,   // debugging: 1 = errors and messages, 2 = messages only
		'auth'     => '',  // authentication enabled
		'secure'   => '',  // secure transfer enabled
		'host'     => '',
		'port'     => '',
		'username' => '',
		'password' => '',
	*/),


);