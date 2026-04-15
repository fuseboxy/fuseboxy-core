<?php
/**
 *  Define fuseboxy configuration
 *  ===> all things defined here could be accessed by {$fuseboxy->config} or F::config() later
 **/

return [


	/**
	 *  Default page (OPTIONAL)
	 *  ===> command is in {~controller~.~action~} format
	 *  ===> when {~action~} not specified, it is {index} by default
	 *  ===> when {false}, fuseboxy will load nothing by default
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
	'vendorPath' => dirname(__DIR__, 2).'/vendor/',


	/**
	 *  For path of image, js, css, etc. (OPTIONAL)
	 **/
	'baseDir' => dirname(__DIR__, 2).'/',
	'baseUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) ),


	/**
	 *  Upload directory (OPTIONAL)
	 *  ===> for file upload (e.g. scaffold, webform)
	 *  ===> set it to 777 mode
	 *  ===> (e.g.) [Local] /server/path/to/my/upload/directory/
	 *  ===> (e.g.) [FTP] ftp://{username}:{password}@{host}:{port}/path/to/my/upload/folder/
	 **/
	'uploadDir' => dirname(__DIR__, 2).'/upload/',                                                                    // UPLOAD_DIR,
	'uploadUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) ).'upload/',  // UPLOAD_URL,
	'tmpDir'    => rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/').'/',                                        // TMP_DIR,


	/**
	 *  Files to auto-include (OPTIONAL)
	 *  ===> using path pattern (please refer to glob function)
	 *  ===> when element is function, it will be run once
	 **/
	'autoLoad' => [
		dirname(__DIR__, 2).'/vendor/autoload.php',
		dirname(__DIR__).'/model/*.php',
	],


	/**
	 *  Controller to handle error (OPTIONAL)
	 *  ===> used by F::error() and F::pageNotFound()
	 *  ===> controller will receive {$fuseboxy->error} as argument
	 *  ===> when false, error will be thrown as exception
	 *  ===> when true, default using {~appPath~/controller/error_controller.php}
	 *  ===> to customize, make a copy of {~vendorPath~/fuseboxy/fuseboxy-core/app/controller/error_controller.php} to {~appPath~/controller/error_controller.php}
	 **/
	'errorController' => true,


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
	'route' => [/*
		'/article/(\d)' => 'fuseaction=article.view&id=$1',
	*/],


	/**
	 *  Database setting for ORM component (OPTIONAL)
	 *  ===> can either be single database or multiple databases
	 *  ===> database {default} is mandatory in multiple databases config
	 *  [Examples]
	 *	// single database
	 *	'db' => array(
	 *		'host' => '...',
	 *		'name' => '...',
	 *		...
	 *	);
	 *	// multiple databases
	 *	'db' => array(
	 *		'default' => [ 'host' => '...', 'name' => '...', ... ],
	 *		'foo' => [ ... ],
	 *		'bar' => [ ... ],
	 *	);  
	 **/
	'db' => [/*
		'host'     => DB_HOST,
		'name'     => DB_NAME,
		'username' => DB_UID,
		'password' => DB_PWD,
	*/],


	/**
	 *  reCAPTCHA setting for Captcha component (OPTIONAL)
	 **/
	'captcha' => [/*
		'siteKey'   => CAPTCHA_SITE,
		'secretKey' => CAPTCHA_SECRET,
	*/],


	/**
	 *  Encryption key for Util::crypt (OPTIONAL)
	 **/
	'encrypt' => null, // ENCRYPT_KEY,


	/**
	 *  SMTP setting for Util::mail (OPTIONAL)
	 **/
	'smtp' => [/*
		'debug'    => 0,            // debugging (0 = no message; 1 = error & message; 2 = messages only)
		'secure'   => SMTP_SECURE,  // secure transfer enabled (SSL, TLS, etc.)
		'auth'     => SMTP_AUTH,    // authentication enabled (boolean)
		'host'     => SMTP_HOST,
		'port'     => SMTP_PORT,
		'username' => SMTP_UID,
		'password' => SMTP_PWD,
		'options'  => SMTP_OPTIONS,
	*/],


	/**
	 *  Multi-language settings for I18N (OPTIONAL)
	 *  ===> default [en] when not specified
	 *  ===> used by I18N & Enum (etc.)
	 **/
	'i18n' => [/*
		'locales' => I18N_ALL_LOCALES,     // (e.g.) en,zh-hk,zh-cn
		'current' => I18N_CURRENT_LOCALE,  // (e.g.) en
	*/],


];