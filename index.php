<?php
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
// load env settings
include __DIR__.'/_env/dev.php';
// load framework
$frameworkInAppPath = __DIR__.'/app/framework/fuseboxy.php';
$frameworkInVendorPath = __DIR__.'/vendor/fuseboxy/fuseboxy-core/app/framework/fuseboxy.php';
include is_file($frameworkInAppPath) ? $frameworkInAppPath : $frameworkInVendorPath;
// run!!
Framework::$configPath = __DIR__.'/app/config/fuseboxy_config.php';
Framework::run();