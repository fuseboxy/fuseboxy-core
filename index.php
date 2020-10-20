<?php
$GLOBALS['startTick'] = microtime(true) * 1000;
// debug settings
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
// environment settings
date_default_timezone_set('Asia/Taipei');
// for disable ie-compatible mode
header("X-UA-Compatible: IE=Edge");
// session management
session_name('FUSEBOXY');
session_start();
// load env settings (when necessary)
if ( is_file('_env.php') ) include '_env.php';
// load framework
if ( is_file(__DIR__.'/vendor/fuseboxy/fuseboxy-core/app/framework/fuseboxy.php') ) {
	include __DIR__.'/vendor/fuseboxy/fuseboxy-core/app/framework/fuseboxy.php';
	Framework::$configPath = __DIR__.'/app/config/fusebox_config.php';
} else include 'app/framework/fuseboxy.php';
// run!!
Framework::run();