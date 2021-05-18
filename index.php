<?php
$GLOBALS['startTick'] = microtime(true)*1000;
// debug settings
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
// environment settings
date_default_timezone_set('Asia/Hong_Kong');
// for disable ie-compatible mode
header("X-UA-Compatible: IE=Edge");
// session management
session_name('FUSEBOXY');
session_start();
// load framework
include '_env.php';
include __DIR__.'/vendor/fuseboxy/fuseboxy-core/app/framework/fuseboxy.php';
Framework::$configPath = __DIR__.'/app/config/fusebox_config.php';
// run!!
Framework::run();