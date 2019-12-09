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
	 *  Delimiter of command parameter (REQUIRED)
	 *  ===> should match {defaultCommand} config
	 *  ===> only support dot (.), dash (-), and underscore (_)
	 **/
	'commandDelimiter' => '.',


	/**
	 *  Directory to load controller, model, view, etc. (REQUIRED)
	 **/
	'appPath' => dirname(__DIR__).'/',


	/**
	 *  For path of stylesheet, script, etc. (OPTIONAL)
	 **/
	'baseUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) ),


	/**
	 *  Directories or files to auto-include (OPTIONAL)
	 *  ===> if directory is specified, only PHP will be included
	 *  ===> if directory is specified, the include will be non-recursive
	 *  ===> full path is required
	 *  ===> wildcard is allowed
	 **/
	'autoLoad' => array(
		dirname(__DIR__).'/model/*',
	),


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
	'errorController' => false,


	/**
	 *  Upload directory (OPTIONAL)
	 *  ===> for scaffold file upload
	 *  ===> set it to 777 mode
	 **/
	'uploadDir' => dirname(dirname(__DIR__)).'/upload/',
	'uploadBaseUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) ).'upload/',


	/**
	 *  Temp directory (OPTIONAL)
	 *  ===> for cache or log
	 *  ===> set it to 777 mode
	 **/
	'tmpDir' => dirname(dirname(__DIR__)).'/tmp/',


	/**
	 *  Use beauty-url (OPTIONAL)
	 *  ===> apply F::url() to all links
	 *  ===> there will be no script name
	 *  ===> controller (if any) and action (if any) will be the first two items after base path
	 *  ===> remember to modify .htaccess if doing url-rewrite (uncomment the line 'RewriteEngine on')
	 **/
	'urlRewrite' => false,


	/**
	 *  Route setting (OPTIONAL)
	 *  ===> only applicable when {urlRewrite=true}
	 *  ===> using regex and back-reference to turn path-like-query-string into query-string (forward-slash will be escaped)
	 *  ===> mapped parameters will go to {$_GET} scope; un-mapped string will not be parsed
	 *  ===> first match expression will be used; so please take the sequence into consideration
	 *  ===> array-key is pattern which match {$_SERVER['REQUEST_URI']} (with or without leading slash)
	 *  ===> array-value is transformed query-string (without leading question mark)
	 **/
	'route' => array(/*
		'/article/(\d)' => 'fuseaction=article.view&id=$1',
		'([\s\S]+)' => 'fuseaction=cms.view&path=$1',
	*/),


	/**
	 *  SMTP setting (OPTIONAL)
	 *  ===> for Util::sendMail()
	 **/
	'smtp' => array(
		'debug'    => 0,   // debugging: 1 = errors and messages, 2 = messages only
		'auth'     => '',  // authentication enabled
		'secure'   => '',  // secure transfer enabled
		'host'     => '',
		'port'     => '',
		'username' => '',
		'password' => '',
	),


	/**
	 *  reCAPTCHA setting (OPTIONAL)
	 *  ===> for Captcha component
	 **/
	'captcha' => array(
		'siteKey'   => '',
		'secretKey' => '',
	),


	/**
	 *  Encrypt setting (OPTIONAL)
	 *  ===> for Util::crypt()
	 **/
	'encrypt' => array(
		'key'     => '',
		'mode'    => '',
		'cipher'  => '',
	),


);