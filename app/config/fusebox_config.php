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
	 *  Upload directory (OPTIONAL)
	 *  ===> for file upload (e.g. scaffold, webform)
	 *  ===> set it to 777 mode
	 **/
	'uploadDir' => dirname(dirname(__DIR__)).'/upload/',
	'uploadUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) ).'upload/',


	/**
	 *  Files to auto-include (OPTIONAL)
	 *  ===> using path pattern (please refer to glob function)
	 *  ===> if element is anonymous function, it will be run once
	 **/
	'autoLoad' => array(
		dirname(dirname(__DIR__)).'/vendor/autoload.php',
		dirname(__DIR__).'/model/',
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
	'errorController' => dirname(__DIR__).'/controller/error_controller.php',


	/**
	 *  Use beauty-url (OPTIONAL)
	 *  ===> apply F::url() to all links
	 *  ===> so that url will have no script name and command variable (index.php?fuseaction=)
	 *  ===> controller (if any) and action (if any) will be the first two items after base path
	 *  ===> remember to modify {.htaccess} if doing url-rewrite (set "RewriteEngine On")
	 **/
	'urlRewrite' => false,


	/**
	 *  Force HTTPS (OPTIONAL)
	 *  ===> auto-redirect page to HTTPS when neccessary
	 *  ===> only perform simple redirection and will not retain form data
	 **/
	'forceHttps' => false,


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
	 *  Database setting for ORM component (OPTIONAL)
	 **/
	'db' => array(/*
		'host'     => DB_HOST,
		'name'     => DB_NAME,
		'username' => DB_UID,
		'password' => DB_PWD,
	*/),


	/**
	 *  reCAPTCHA setting for Captcha component (OPTIONAL)
	 **/
	'captcha' => array(/*
		'siteKey'   => CAPTCHA_SITE,
		'secretKey' => CAPTCHA_SECRET,
	*/),


	/**
	 *  Proxy setting for Util::httpRequest (OPTIONAL)
	 **/
	'proxy' => array(/*
		'http'  => HTTP_PROXY,
		'https' => HTTPS_PROXY,
	*/),


	/**
	 *  Encrypt setting for Util::crypt (OPTIONAL)
	 *  ===> specify as [string] for [encrypt-key] only
	 **/
	'encrypt' => array(/*
		'key'    => ENCRYPT_KEY,
		'vendor' => ENCRYPT_VENDOR,  // mcrypt or openssl
		'algo'   => ENCRYPT_ALGO,    // (e.g.) ~MCRYPT_RIJNDAEL_256~, BF-ECB, ...
		'mode'   => ENCRYPT_MODE,    // (e.g.) ~MCRYPT_MODE_ECB~, ... (used as options for openssl)
		'iv'     => ENCRYPT_IV,      // initial vector
	*/),


	/**
	 *  SMTP setting for Util::mail (OPTIONAL)
	 **/
	'smtp' => array(/*
		'debug'    => 0,            // debugging (0 = no message; 1 = error & message; 2 = messages only)
		'secure'   => SMTP_SECURE,  // secure transfer enabled (ssl, tsl, etc.)
		'auth'     => SMTP_AUTH,    // authentication enabled (boolean)
		'host'     => SMTP_HOST,
		'port'     => SMTP_PORT,
		'username' => SMTP_UID,
		'password' => SMTP_PWD,
	*/),


	/**
	 *  Multi-language settings for I18N (OPTIONAL)
	 *  ===> default [en] when not specified
	 *  ===> used by I18N & Enum (etc.)
	 **/
	'i18n' => array(/*
		'locales' => I18N_LOCALE_ALL,  // (e.g.) en,zh-hk,zh-cn
		'current' => I18N_LOCALE,      // (e.g.) en
	*/),


);