<?php
class TestFuseboxyCore extends UnitTestCase {


	function __construct() {
		if ( !class_exists('Framework') ) {
			include dirname(dirname(__FILE__)).'/app/framework/fuseboxy.php';
		}
		if ( !class_exists('F') ) {
			include dirname(dirname(__FILE__)).'/app/framework/F.php';
		}
		Framework::$mode = Framework::FUSEBOX_UNIT_TEST;
	}


	function test__Framework__autoLoad() {
		global $fusebox;
		Framework::createAPIObject();
		// check invalid file
		try {
			$hasError = false;
			$fusebox->config['autoLoad'] = array(dirname(__FILE__).'/no/such/file.php');
			Framework::autoLoad();
		} catch (Exception $e) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_INVALID_CONFIG );
		}
		$this->assertTrue($hasError);
		// check invalid directory
		try {
			$hasError = false;
			$fusebox->config['autoLoad'] = array(dirname(__FILE__).'/no/such/directory/');
			Framework::autoLoad();
		} catch (Exception $e) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_INVALID_CONFIG );
		}
		$this->assertTrue($hasError);
		// check invalid wildcard
		try {
			$hasError = false;
			$fusebox->config['autoLoad'] = array(dirname(__FILE__).'/no/such/directory/*.*');
			Framework::autoLoad();
		} catch (Exception $e) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_INVALID_CONFIG );
		}
		// when wildcard is invalid
		// ===> framework simply do not look through it
		// ===> but would not be able to see if the wildcarded-directory really exists
		// ===> so there is simply no errorr...
		$this->assertFalse($hasError);
		// check valid file
		try {
			$hasError = false;
			$fusebox->config['autoLoad'] = array(dirname(__FILE__).'/utility-core/empty.php');
			Framework::autoLoad();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// check valid directory
		// ===> need to create empty directory
		// ===> because git cannot track empty folder
		try {
			$hasError = false;
			if ( !is_dir(dirname(__FILE__).'/utility-core/empty/') ) {
				mkdir(dirname(__FILE__).'/utility-core/empty/');
			}
			$fusebox->config['autoLoad'] = array(dirname(__FILE__).'/utility-core/empty/');
			Framework::autoLoad();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// check valid wildcard
		try {
			$hasError = false;
			$fusebox->config['autoLoad'] = array(dirname(__FILE__).'/utility-core/non-empty/*');
			Framework::autoLoad();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// check valid wildcard (but no result)
		try {
			$hasError = false;
			$fusebox->config['autoLoad'] = array(dirname(__FILE__).'/utility-core/non-empty/*.asp');
			Framework::autoLoad();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// check valid wildcard (but empty directory)
		try {
			$hasError = false;
			$fusebox->config['autoLoad'] = array(dirname(__FILE__).'/utility-core/empty/*');
			Framework::autoLoad();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// clean-up
		$fusebox = null;
	}


	function test__Framework__formUrl2arguments() {
		global $fusebox;
		global $arguments;
		Framework::createAPIObject();
		// check disable
		$fusebox->config['formUrl2arguments'] = false;
		Framework::formUrl2arguments();
		$this->assertFalse( isset($arguments) );
		// check disable (false-equivalent)
		$fusebox->config['formUrl2arguments'] = 0;
		Framework::formUrl2arguments();
		$this->assertFalse( isset($arguments) );
		// check enable
		$_GET['foo'] = 1;
		$_POST['bar'] = 2;
		$fusebox->config['formUrl2arguments'] = true;
		Framework::formUrl2arguments();
		$this->assertTrue( isset($arguments) );
		$this->assertTrue( isset($arguments['foo']) and $arguments['foo'] === 1 );
		$this->assertTrue( isset($arguments['bar']) and $arguments['bar'] === 2 );
		$arguments = null;
		unset($_GET['foo'], $_POST['bar']);
		// check enable (true-equivalent)
		$fusebox->config['formUrl2arguments'] = 1;
		Framework::formUrl2arguments();
		$this->assertTrue( isset($arguments) );
		$arguments = null;
		// check default precedence (first-come-first-serve, url-parameter comes first)
		$_GET['foobar'] = 1;
		$_POST['foobar'] = 2;
		$fusebox->config['formUrl2arguments'] = true;
		Framework::formUrl2arguments();
		$this->assertTrue( isset($arguments['foobar']) and $arguments['foobar'] === 1 );
		$arguments = null;
		unset($_GET['foobar'], $_POST['bar']);
		// check custom precedence (first-come-first-serve, url-parameter comes first)
		$_GET['foobar'] = 1;
		$_POST['foobar'] = 2;
		$fusebox->config['formUrl2arguments'] = array($_POST, $_GET);
		Framework::formUrl2arguments();
		$this->assertTrue( isset($arguments['foobar']) and $arguments['foobar'] === 2 );
		$arguments = null;
		unset($_GET['foobar'], $_POST['foobar']);
		// check custom scope
		$_GET['foo'] = 1;
		$_POST['bar'] = 2;
		$fusebox->config['formUrl2arguments'] = array($_POST);
		Framework::formUrl2arguments();
		$this->assertFalse( isset($arguments['foo']) );
		$this->assertTrue( isset($arguments['bar']) );
		$arguments = null;
		unset($_GET['foo'], $_POST['bar']);
		// check custom scopes
		$_GET['foo'] = 1;
		$fusebox->config['formUrl2arguments'] = array($_GET, $_SERVER);
		Framework::formUrl2arguments();
		$this->assertTrue( isset($arguments['foo']) );
		$this->assertTrue( isset($arguments['HTTP_HOST']) );
		$arguments = null;
		unset($_GET['foo']);
		// check invalid config
		$hasError = false;
		try {
			$fusebox->config['formUrl2arguments'] = 'abc';
			Framework::formUrl2arguments();
		} catch (Exception $e) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_INVALID_CONFIG );
		}
		$this->assertTrue($hasError);
		$arguments = null;
		// clean-up
		$fusebox = null;
	}


	function test__Framework__loadConfig() {
		global $fusebox;
		Framework::createAPIObject();
		$originalConfigPath = Framework::$configPath;
		// default config path (success)
		$hasError = false;
		try {
			Framework::loadConfig();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// invalid config path (failure)
		$hasError = false;
		try {
			Framework::$configPath = dirname(__FILE__).'/no/such/path.php';
			Framework::loadConfig();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertTrue($hasError);
		$this->assertTrue( $e->getCode() == Framework::FUSEBOX_CONFIG_NOT_FOUND );
		// malformed config (failure)
		$hasError = false;
		try {
			Framework::$configPath = dirname(__FILE__).'/utility-core/empty.php';
			Framework::loadConfig();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertTrue($hasError);
		$this->assertTrue( $e->getCode() == Framework::FUSEBOX_CONFIG_NOT_DEFINED );
		// empty config (success)
		// ===> apply default value
		$hasError = false;
		try {
			Framework::$configPath = dirname(__FILE__).'/utility-core/config/empty_config.php';
			Framework::loadConfig();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// config with default value
		$this->assertTrue( !empty($fusebox->config['commandVariable']) ) ;
		$this->assertTrue( !empty($fusebox->config['commandDelimiter']) ) ;
		$this->assertTrue( !empty($fusebox->config['appPath']) ) ;
		// clean-up
		$fusebox = null;
		Framework::$configPath = $originalConfigPath;
	}


	function test__Framework__loadHelper() {
		global $fusebox;
		Framework::createAPIObject();
		$originalHelperPath = Framework::$helperPath;
		// check invalid path
		$hasError = false;
		try {
			Framework::$helperPath = dirname(__FILE__).'/no/such/file.php';
			Framework::loadHelper();
		} catch (Exception $e) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_HELPER_NOT_FOUND );
		}
		$this->assertTrue($hasError);
		// clean-up
		$fusebox = null;
		Framework::$helperPath = $originalHelperPath;
	}


	function test__Framework__runNothing() {
		global $fusebox;
		// run nothing
		$originalConfigPath = Framework::$configPath;
		Framework::$configPath = dirname(__FILE__).'/utility-core/config/empty_config.php';
		$hasError = false;
		try {
			ob_start();
			Framework::run();
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = true;
			$output = $e->getMessage();
		}
		$this->assertFalse($hasError);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue($output == '');
		// clean-up
		$fusebox = null;
		Framework::$configPath = $originalConfigPath;
	}


	function test__Framework__runSuccess() {
		global $fusebox;
		// run successfully
		$hasError = false;
		try {
			ob_start();
			Framework::run();
			$output = ob_get_clean();
		} catch (Exception $e) {
			$hasError = true;
			$output = $e->getMessage();
		}
		$this->assertFalse($hasError);
		$this->assertNoPattern('/PHP ERROR/i', $output);
		$this->assertTrue($output != '');
		// clean-up
		$fusebox = null;
	}


	function test__Framework__setControllerAction() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['defaultCommand'] = 'unitTest';
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		$fusebox->config['commandDelimiter'] = '.';
		// check default
		Framework::setControllerAction();
		$this->assertTrue( $fusebox->controller === 'unitTest' and $fusebox->action === 'index' );
		// check standard (by url)
		$_GET['unitTestCommand'] = 'foo.bar';
		Framework::setControllerAction();
		$this->assertTrue( $fusebox->controller === 'foo' and $fusebox->action === 'bar' );
		unset($_GET['unitTestCommand']);
		// check standard (by form)
		$_POST['unitTestCommand'] = 'abc.xyz';
		Framework::setControllerAction();
		$this->assertTrue( $fusebox->controller === 'abc' and $fusebox->action === 'xyz' );
		unset($_POST['unitTestCommand']);
		// check different command-delimiter
		$fusebox->config['commandDelimiter'] = '_';
		$_GET['unitTestCommand'] = 'aaa_bbb';
		Framework::setControllerAction();
		$this->assertTrue( $fusebox->controller === 'aaa' and $fusebox->action === 'bbb' );
		$this->assertFalse( $fusebox->controller === 'aaa_bbb' and $fusebox->action === 'index' );
		unset($_GET['unitTestCommand']);
		// clean-up
		$fusebox = null;
	}


	function test__Framework__setMyself() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		// check url-rewrite disabled (at sub-directory)
		$_SERVER['SCRIPT_NAME'] = '/unit_test/index.php';
		$fusebox->config['urlRewrite'] = false;
		Framework::setMyself();
		$this->assertTrue( isset($fusebox->self) );
		$this->assertTrue( isset($fusebox->myself) );
		$this->assertTrue( $fusebox->self == '/unit_test/index.php' );
		$this->assertTrue( $fusebox->myself == "{$fusebox->self}?unitTestCommand=" );
		unset($fusebox->self, $fusebox->myself);
		// check url-rewrite enabled (at sub-directory)
		$fusebox->config['urlRewrite'] = true;
		Framework::setMyself();
		$this->assertTrue( isset($fusebox->self) );
		$this->assertTrue( isset($fusebox->myself) );
		$this->assertTrue( $fusebox->self == '/unit_test/' );
		$this->assertTrue( $fusebox->myself == $fusebox->self );
		unset($fusebox->self, $fusebox->myself);
		// check url-rewrite disabled (at root directory)
		$_SERVER['SCRIPT_NAME'] = '/index.php';
		$fusebox->config['urlRewrite'] = false;
		Framework::setMyself();
		$this->assertTrue( isset($fusebox->self) );
		$this->assertTrue( isset($fusebox->myself) );
		$this->assertTrue( $fusebox->self == '/index.php' );
		$this->assertTrue( $fusebox->myself == "{$fusebox->self}?unitTestCommand=" );
		unset($fusebox->self, $fusebox->myself);
		// check url-rewrite enabled (at root directory)
		$fusebox->config['urlRewrite'] = true;
		Framework::setMyself();
		$this->assertTrue( isset($fusebox->self) );
		$this->assertTrue( isset($fusebox->myself) );
		$this->assertTrue( $fusebox->self == '/' );
		$this->assertTrue( $fusebox->myself == $fusebox->self );
		unset($fusebox->self, $fusebox->myself);
		// clean-up
		$fusebox = null;
	}


	function test__Framework__urlRewrite() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['defaultCommand'] = 'unit.test';
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		$fusebox->config['commandDelimiter'] = '.';
		$fusebox->config['urlRewrite'] = true;
		// check nothing to rewrite
		$_SERVER['REQUEST_URI'] = '/';
		Framework::urlRewrite();
		$this->assertTrue( empty($_GET['unitTestCommand']) );
		$this->assertTrue( empty($_SERVER['QUERY_STRING']) );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand']);
		// check rewrite with command and no parameter
		$_SERVER['REQUEST_URI'] = '/foo/bar';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=foo.bar' );
		$this->assertTrue( $_GET['unitTestCommand'] == 'foo.bar' );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand']);
		// check rewrite with custom command-delimiter
		$fusebox->config['commandDelimiter'] = '_';
		$_SERVER['REQUEST_URI'] = '/aaa/bbb/';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=aaa_bbb' );
		$this->assertTrue( $_GET['unitTestCommand'] == 'aaa_bbb' );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand']);
		$fusebox->config['commandDelimiter'] = '.';
		// check rewrite with command and parameter
		$_SERVER['REQUEST_URI'] = '/fooBar/xyz/a=1/b=2/c=3/';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=fooBar.xyz&a=1&b=2&c=3' );
		$this->assertTrue( $_GET['unitTestCommand'] == 'fooBar.xyz' );
		$this->assertTrue( $_GET['a'] == 1 and $_GET['b'] == 2 and $_GET['c'] == 3 );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['a'], $_GET['b'], $_GET['c']);
		// check rewrite with parameter but no command
		$_SERVER['REQUEST_URI'] = '/aaa=100/bbb=200/ccc=300/';
		Framework::urlRewrite();
		$this->assertTrue( empty($_GET['unitTestCommand']) );
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'aaa=100&bbb=200&ccc=300' );
		$this->assertTrue( $_GET['aaa'] == 100 and $_GET['bbb'] == 200 and $_GET['ccc'] == 300 );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['aaa'], $_GET['bbb'], $_GET['ccc']);
		// check rewrite with controller only and parameter
		$_SERVER['REQUEST_URI'] = '/unitTest/abc=123/xyz=999';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=unitTest&abc=123&xyz=999' );
		$this->assertTrue( $_GET['unitTestCommand'] == 'unitTest' );
		$this->assertTrue( $_GET['abc'] == 123 and $_GET['xyz'] == 999 );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['abc'], $_GET['xyz']);
		// check rewrite with associative-array parameter
		$_SERVER['REQUEST_URI'] = '/unit/test/foo[a]=1/foo[b]=2/foo[c]=3';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=unit.test&foo[a]=1&foo[b]=2&foo[c]=3' );
		$this->assertTrue( is_array($_GET['foo']) and $_GET['foo']['a'] == 1 and $_GET['foo']['b'] == 2 and $_GET['foo']['c'] == 3 );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['foo']);
		// check rewrite with indexed-array parameter
		// check rewrite with associative-array parameter
		$_SERVER['REQUEST_URI'] = '/unit/test/foo[0]=a/foo[1]=b/foo[2]=c';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=unit.test&foo[0]=a&foo[1]=b&foo[2]=c' );
		$this->assertTrue( is_array($_GET['foo']) and $_GET['foo'][0] == 'a' and $_GET['foo'][1] == 'b' and $_GET['foo'][2] == 'c' );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['foo']);
		// check rewrite with append-array parameter
		$_SERVER['REQUEST_URI'] = '/unit/test/foobar[]=abc/foobar[]=xyz/foobar[]=123';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=unit.test&foobar[]=abc&foobar[]=xyz&foobar[]=123' );
		$this->assertTrue( is_array($_GET['foobar']) and $_GET['foobar'][0] == 'abc' and $_GET['foobar'][1] == 'xyz' and $_GET['foobar'][2] == 123 );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['foobar']);
		// check rewrite with multi-level array parameter
		$_SERVER['REQUEST_URI'] = '/unit/test/foobar[abc][123][xyz]=999';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=unit.test&foobar[abc][123][xyz]=999' );
		$this->assertTrue( isset($_GET['foobar']['abc'][123]['xyz']) and $_GET['foobar']['abc'][123]['xyz'] == 999 );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['foobar']);
		// check rewrite with beauty-url mix with query-string
		$_SERVER['REQUEST_URI'] = '/unit/test/a=1/b=2/c=3/?x=9&y=9&z=9';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=unit.test&a=1&b=2&c=3&x=9&y=9&z=9' );
		$this->assertTrue( $_GET['unitTestCommand'] == 'unit.test' );
		$this->assertTrue( $_GET['a'] == 1 and $_GET['b'] == 2 and $_GET['c'] == 3 );
		$this->assertTrue( $_GET['z'] == 9 and $_GET['y'] == 9 and $_GET['z'] == 9 );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['a'], $_GET['b'], $_GET['c'], $_GET['x'], $_GET['y'], $_GET['z']);
		// check rewrite with beauty-url mix with query-string
		$_SERVER['REQUEST_URI'] = '/unit/test/x=9/y=9/z=9/&a=1&b=2&c=3';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=unit.test&x=9&y=9&z=9&a=1&b=2&c=3' );
		$this->assertTrue( $_GET['unitTestCommand'] == 'unit.test' );
		$this->assertTrue( $_GET['a'] == 1 and $_GET['b'] == 2 and $_GET['c'] == 3 );
		$this->assertTrue( $_GET['z'] == 9 and $_GET['y'] == 9 and $_GET['z'] == 9 );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['a'], $_GET['b'], $_GET['c'], $_GET['x'], $_GET['y'], $_GET['z']);
		// check rewrite with route
		$fusebox->config['route'] = array(
			'/article/(\d+)' => 'unitTestCommand=article.view&id=$1&abc=$2&xyz=foobar'
		);
		$_SERVER['REQUEST_URI'] = '/article/999';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=article.view&id=999&abc=&xyz=foobar' );
		$this->assertTrue( $_GET['unitTestCommand'] == 'article.view' );
		$this->assertTrue( $_GET['id'] == 999 );
		$this->assertTrue( $_GET['abc'] == '' );
		$this->assertTrue( $_GET['xyz'] == 'foobar' );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['id'], $_GET['abc'], $_GET['xyz']);
		$fusebox->config['route'] = null;
		// check route start without forward-slash
		$fusebox->config['route'] = array(
			'news/read/(\d+)' => 'unitTestCommand=news.read&id=$1'
		);
		$_SERVER['REQUEST_URI'] = '/news/read/100';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=news.read&id=100' );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['id']);
		$fusebox->config['route'] = null;
		// check route with forward-slash escaped
		$fusebox->config['route'] = array(
			'\/article\/view\/(\d+)' => 'unitTestCommand=article.view&id=$1'
		);
		$_SERVER['REQUEST_URI'] = '/article/view/12345';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=article.view&id=12345' );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['id']);
		$fusebox->config['route'] = null;
		// check multiple routes (should match the first one)
		$fusebox->config['route'] = array(
			'([\s\S]+)' => 'unitTestCommand=cms.view&path=$1',
			'/post/(\d+)' => 'unitTestCommand=article.view&id=$1',
		);
		$_SERVER['REQUEST_URI'] = '/post/111';
		Framework::urlRewrite();
		$this->assertTrue( $_SERVER['QUERY_STRING'] == 'unitTestCommand=cms.view&path=post/111' );
		$this->assertFalse( $_SERVER['QUERY_STRING'] == 'unitTestCommand=article.view&id=111' );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand'], $_GET['id']);
		$fusebox->config['route'] = null;
		// check rewrite disabled
		$fusebox->config['urlRewrite'] = false;
		$_SERVER['REQUEST_URI'] = '/foo/bar';
		Framework::urlRewrite();
		$this->assertTrue( empty($_GET['unitTestCommand']) );
		$this->assertTrue( empty($_SERVER['QUERY_STRING']) );
		$_SERVER['REQUEST_URI'] = $_SERVER['QUERY_STRING'] = null;
		unset($_GET['unitTestCommand']);
		// clean-up
		$fusebox = null;
	}


	function test__Framework__validateConfig() {
		global $fusebox;
		Framework::createAPIObject();
		// default config should cover all essentials
		$originalConfigPath = Framework::$configPath;
		Framework::$configPath = dirname(__FILE__).'/utility-core/config/empty_config.php';
		Framework::loadConfig();
		$hasError = false;
		try {
			Framework::validateConfig();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// missing command-variable
		$tmp = $fusebox->config['commandVariable'];
		$fusebox->config['commandVariable'] = null;
		$hasError = false;
		try {
			Framework::validateConfig();
		} catch (Exception $e) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_MISSING_CONFIG );
			$this->assertPattern('/commandVariable/i', $e->getMessage());
		}
		$this->assertTrue($hasError);
		$fusebox->config['commandVariable'] = $tmp;
		// missing command-delimiter
		$tmp = $fusebox->config['commandDelimiter'];
		$fusebox->config['commandDelimiter'] = null;
		$hasError = false;
		try {
			Framework::validateConfig();
		} catch (Exception $e) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_MISSING_CONFIG );
			$this->assertPattern('/commandDelimiter/i', $e->getMessage());
		}
		$this->assertTrue($hasError);
		$fusebox->config['commandDelimiter'] = $tmp;
		// missing app-path
		$tmp = $fusebox->config['appPath'];
		$fusebox->config['appPath'] = null;
		$hasError = false;
		try {
			Framework::validateConfig();
		} catch (Exception $e) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_MISSING_CONFIG );
			$this->assertPattern('/appPath/i', $e->getMessage());
		}
		$this->assertTrue($hasError);
		$fusebox->config['appPath'] = $tmp;
		// invalid error-controller
		$fusebox->config['errorController'] = '/path/not/exist/error_controller.php';
		$hasError = false;
		try {
			Framework::validateConfig();
		} catch (Exception $e) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_INVALID_CONFIG );
			$this->assertPattern('/error controller does not exist/i', $e->getMessage());
		}
		$this->assertTrue($hasError);
		// valid error-controller
		$fusebox->config['errorController'] = dirname(__FILE__).'/utility-core/empty.php';
		$hasError = false;
		try {
			Framework::validateConfig();
		} catch (Exception $e) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// clean-up
		$fusebox = null;
		Framework::$configPath = $originalConfigPath;
	}


	function test__F__ajaxRequest() {
		global $fusebox;
		Framework::createAPIObject();
		Framework::loadConfig();
		// check correct value
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		$this->assertTrue(F::ajaxRequest());
		// check case-sensitive
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XmlHttpRequest';
		$this->assertTrue(F::ajaxRequest());
		// check wrong value
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'fooBarRequest';
		$this->assertFalse(F::ajaxRequest());
		// check missing parameter
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		$this->assertFalse(F::ajaxRequest());
		// clean-up
		$fusebox = null;
	}


	function test__F__command() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		$fusebox->config['commandDelimiter'] = '.';
		// check empty default command
		$fusebox->config['defaultCommand'] = false;
		Framework::setControllerAction();
		$this->assertFalse( F::command() );
		$this->assertFalse( F::command('controller') );
		$this->assertFalse( F::command('action') );
		// check non-empty default command
		$fusebox->config['defaultCommand'] = 'unit.test';
		Framework::setControllerAction();
		$this->assertTrue( F::command() === 'unit.test' );
		// check standard (by url)
		$_GET['unitTestCommand'] = 'foo.bar';
		Framework::setControllerAction();
		$this->assertTrue( F::command() === 'foo.bar' );
		$this->assertTrue( F::command('controller') === 'foo' );
		$this->assertTrue( F::command('action') === 'bar' );
		unset($_GET['unitTestCommand']);
		// check standard (by form)
		$_POST['unitTestCommand'] = 'abc.xyz';
		Framework::setControllerAction();
		$this->assertTrue( F::command() === 'abc.xyz' );
		$this->assertTrue( F::command('controller') === 'abc' );
		$this->assertTrue( F::command('action') === 'xyz' );
		unset($_POST['unitTestCommand']);
		// check key case-sensitive
		$_GET['unitTestCommand'] = 'foo.bar';
		Framework::setControllerAction();
		$this->assertTrue( F::command('CONTROLLER') === 'foo' );
		$this->assertTrue( F::command('ACTION') === 'bar' );
		// check invalid key
		$this->assertTrue( F::command('xxx') === false );
		$this->assertTrue( F::command('controller.action') === false );
		$this->assertFalse( F::command('controller.action') === 'foo.bar' );
		// check multiple delimiters
		$_GET['unitTestCommand'] = 'aaa.bbb.ccc';
		Framework::setControllerAction();
		$this->assertTrue( F::command() === 'aaa.bbb.ccc' );
		$this->assertTrue( F::command('controller') === 'aaa' );
		$this->assertTrue( F::command('action') === 'bbb.ccc' );
		$this->assertFalse( F::command('action') === 'bbb' or F::command('action') === 'ccc' );
		unset($_GET['unitTestCommand']);
		// check command-delimiter
		$_GET['unitTestCommand'] = 'foo-bar';
		Framework::setControllerAction();
		$this->assertTrue( F::command('controller') === 'foo-bar' and F::command('action') === 'index' );
		$fusebox->config['commandDelimiter'] = '-';
		Framework::setControllerAction();
		$this->assertTrue( F::command() === 'foo-bar' );
		$this->assertTrue( F::command('controller') === 'foo' );
		$this->assertTrue( F::command('action') === 'bar' );
		unset($_GET['unitTestCommand']);
		// clean-up
		$fusebox = null;
	}


	function test__F__config() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['defaultCommand'] = 'unit.test';
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		$fusebox->config['commandDelimiter'] = '.';
		// check return all
		$this->assertTrue( F::config() == $fusebox->config );
		$this->assertTrue( F::config(null) == $fusebox->config );
		$this->assertTrue( F::config(false) == $fusebox->config );
		$this->assertTrue( F::config(0) == $fusebox->config );
		$this->assertTrue( F::config('') == $fusebox->config );
		// check return one
		$this->assertTrue( F::config('defaultCommand') == $fusebox->config['defaultCommand'] );
		// check valid config
		$fusebox->config['foo'] = 'bar';
		$this->assertTrue( F::config('foo') == 'bar' );
		$this->assertFalse( F::config('FOO') == 'bar' );
		// check invalid config
		$this->assertTrue( F::config('foobar') == null );
		// clean-up
		$fusebox = null;
	}


	function test__F__error() {
		global $fusebox;
		Framework::createAPIObject();
		Framework::loadConfig();
		Framework::setControllerAction();
		$fusebox->config['errorController'] = false;
		// check has error
		$hasError = false;
		try {
			F::error('check-has-error', true);
		} catch ( Exception $e ) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_ERROR );
		}
		$this->assertTrue($hasError);
		// check no error
		$hasError = false;
		try {
			F::error('check-no-error', false);
		} catch ( Exception $e ) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// clean-up
		$fusebox = null;
	}


	function test__F__is() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['defaultCommand'] = 'unit.test';
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		$fusebox->config['commandDelimiter'] = '.';
		// check default command
		Framework::setControllerAction();
		$this->assertTrue( F::is('unit.test') );
		// check standard (by url)
		$_GET['unitTestCommand'] = 'foo.bar';
		Framework::setControllerAction();
		$this->assertTrue( F::is('foo.bar') );
		// check all-match controller
		$this->assertTrue( F::is('*.bar') );
		// check all-match action
		$this->assertTrue( F::is('foo.*') );
		// check all-match
		$this->assertTrue( F::is('*.*') );
		// check multiple commands
		$this->assertTrue( F::is('abc.xyz,foo.bar') );
		$this->assertTrue( F::is('abc.xyz,foo.*') );
		$this->assertTrue( F::is('abc.xyz,*.bar') );
		$this->assertTrue( F::is('abc.xyz,*.*') );
		// check case-sensitive
		$this->assertFalse( F::is('FOO.bar') );
		$this->assertFalse( F::is('foo.BAR') );
		// check multiple delimiters
		$_GET['unitTestCommand'] = 'aaa.bbb.ccc';
		Framework::setControllerAction();
		$this->assertTrue( F::is('aaa.*') );
		$this->assertTrue( F::is('*.bbb.ccc') );
		unset($_GET['unitTestCommand']);
		// check standard (by form)
		$_POST['unitTestCommand'] = 'abc.xyz';
		Framework::setControllerAction();
		$this->assertTrue( F::is('abc.xyz') );
		unset($_POST['unitTestCommand']);
		// check command-delimiter
		$_GET['unitTestCommand'] = 'foo-bar';
		Framework::setControllerAction();
		$this->assertTrue( F::is('foo-bar') );
		$this->assertTrue( F::is('foo-bar.index') );
		$this->assertFalse( F::is('*-*') );
		$this->assertFalse( F::is('foo-*') );
		$this->assertFalse( F::is('*-bar') );
		$fusebox->config['commandDelimiter'] = '-';
		Framework::setControllerAction();
		$this->assertFalse( F::is('foo-bar.index') );
		$this->assertTrue( F::is('foo-bar') );
		$this->assertTrue( F::is('*-*') );
		$this->assertTrue( F::is('foo-*') );
		$this->assertTrue( F::is('*-bar') );
		unset($_GET['unitTestCommand']);
		// clean-up
		$fusebox = null;
	}


	function test__F__invoke() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['defaultCommand'] = 'unit.test';
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		$fusebox->config['commandDelimiter'] = '.';
		$fusebox->config['appPath'] = dirname(__FILE__).'/utility-core/';
		Framework::setControllerAction();
		// check valid command
		$this->assertTrue( F::is('unit.test') );
		ob_start();
		F::invoke('unitTest');
		$output = trim( ob_get_clean() );
		$this->assertTrue( $output === 'This is unit test controller' );
		$this->assertTrue( F::is('unit.test') );
		ob_start();
		F::invoke('unitTest.anotherPage');
		$output = trim( ob_get_clean() );
		$this->assertTrue( $output === 'This is another page' );
		// check invalid controller
		$hasError = false;
		try {
			F::invoke('foo.bar');
		} catch ( Exception $e ) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_PAGE_NOT_FOUND );
		}
		$this->assertTrue($hasError);
		// check invalid action (valid controller)
		$hasError = false;
		try {
			F::invoke('unitTest.fooBar');
		} catch ( Exception $e ) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_PAGE_NOT_FOUND );
		}
		$this->assertTrue($hasError);
		// check nested invoke
		ob_start();
		F::invoke('unitTest.nestedInvoke');
		$output = trim( ob_get_clean() );
		$this->assertPattern('/This is simple invoke/', $output);
		$this->assertPattern('/This is nested invoke/', $output);
		// clean-up
		$fusebox = null;
	}


	function test__F__isCLI() {
		// no way to test yet
	}


	function test__F__isInvoke() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['defaultCommand'] = 'unit.test';
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		$fusebox->config['commandDelimiter'] = '.';
		$fusebox->config['appPath'] = dirname(__FILE__).'/utility-core/';
		Framework::setControllerAction();
		// check simple invoke
		ob_start();
		F::invoke('unitTest.simpleInvoke');
		$output = trim( ob_get_clean() );
		$this->assertPattern('/invokeQueueLength=1/', $output);
		$this->assertTrue( substr_count($output, 'invokeQueueLength') == 1 );
		// check nested invoke
		ob_start();
		F::invoke('unitTest.nestedInvoke');
		$output = trim( ob_get_clean() );
		$this->assertPattern('/invokeQueueLength=2/', $output);
		$this->assertTrue( substr_count($output, 'invokeQueueLength') == 2 );
		// clean-up
		$fusebox = null;
	}


	function test__F__pageNotFound() {
		global $fusebox;
		Framework::createAPIObject();
		Framework::loadConfig();
		Framework::setControllerAction();
		$fusebox->config['errorController'] = false;
		// check page-not-found
		$hasError = false;
		try {
			F::pageNotFound();
		} catch ( Exception $e ) {
			$hasError = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_PAGE_NOT_FOUND );
		}
		$this->assertTrue($hasError);
		// check condition not match
		$hasError = false;
		try {
			F::pageNotFound(false);
		} catch ( Exception $e ) {
			$hasError = true;
		}
		$this->assertFalse($hasError);
		// clean-up
		$fusebox = null;
	}


	function test__F__parseCommand() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['commandDelimiter'] = '.';
		// check standard
		$tmp = F::parseCommand('foo.bar');
		$this->assertTrue( $tmp['controller'] === 'foo' and $tmp['action'] === 'bar' );
		// check action default value
		$tmp = F::parseCommand('foobar');
		$this->assertTrue( $tmp['controller'] === 'foobar' and $tmp['action'] === 'index' );
		// check case-sensitive
		$tmp = F::parseCommand('FooBar');
		$this->assertTrue( $tmp['controller'] != 'foobar' );
		// check more than one delimiters
		$tmp = F::parseCommand('aaa.bbb.ccc');
		$this->assertTrue( $tmp['controller'] === 'aaa' and $tmp['action'] === 'bbb.ccc' );
		// check different delimiter
		$tmp = F::parseCommand('foo-bar');
		$this->assertTrue( $tmp['controller'] === 'foo-bar' and $tmp['action'] === 'index' );
		// check different delimiter
		$fusebox->config['commandDelimiter'] = '-';
		$tmp = F::parseCommand('foo-bar');
		$this->assertTrue( $tmp['controller'] === 'foo' and $tmp['action'] === 'bar' );
		// clean-up
		$fusebox = null;
	}


	function test__F__redirect() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['defaultCommand'] = 'unit.test';
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		$fusebox->config['commandDelimiter'] = '.';
		Framework::setMyself();
		// check redirect to internal command
		try {
			$hasRedirect = false;
			F::redirect('foo.bar');
		} catch (Exception $e) {
			$hasRedirect = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_REDIRECT );
			$this->assertPattern('/'.preg_quote(F::url('foo.bar'), '/').'/i', $e->getMessage());
		}
		$this->assertTrue($hasRedirect);
		// check redirect to external url
		try {
			$hasRedirect = false;
			F::redirect('http://www.google.com');
		} catch (Exception $e) {
			$hasRedirect = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_REDIRECT );
			$this->assertPattern('/'.preg_quote('http://www.google.com', '/').'/i', $e->getMessage());
		}
		$this->assertTrue($hasRedirect);
		// check delay redirect
		try {
			$hasRedirect = false;
			F::redirect('https://www.google.com', true, 999);
		} catch (Exception $e) {
			$hasRedirect = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_REDIRECT );
			$this->assertPattern('/'.preg_quote('https://www.google.com', '/').'/i', $e->getMessage());
			$this->assertPattern('/Refresh:999/i', $e->getMessage());
		}
		$this->assertTrue($hasRedirect);
		// check no delay redirect
		try {
			$hasRedirect = false;
			F::redirect('foo.bar');
		} catch (Exception $e) {
			$hasRedirect = true;
			$this->assertTrue( $e->getCode() == Framework::FUSEBOX_REDIRECT );
			$this->assertPattern('/Location:/i', $e->getMessage());
			$this->assertNoPattern('/Refresh:/i', $e->getMessage());
		}
		// check no redirect
		try {
			$hasRedirect = false;
			F::redirect('foo.bar', false);
		} catch (Exception $e) {
			$hasRedirect = true;
		}
		$this->assertFalse($hasRedirect);
		// clean-up
		$fusebox = null;
	}


	function test__F__url() {
		global $fusebox;
		Framework::createAPIObject();
		$fusebox->config['defaultCommand'] = 'unit.test';
		$fusebox->config['commandVariable'] = 'unitTestCommand';
		$fusebox->config['commandDelimiter'] = '.';
		// url-rewrite : disabled
		$fusebox->config['urlRewrite'] = false;
		Framework::setMyself();
		// no rewrite : check default command
		$this->assertTrue( F::url() === $fusebox->self );
		// no rewrite : check command only
		$this->assertTrue( F::url('foo') === "{$fusebox->self}?unitTestCommand=foo");
		$this->assertTrue( F::url('foo.bar') === "{$fusebox->self}?unitTestCommand=foo.bar");
		// no rewrite : check url with parameter
		$this->assertTrue( F::url('foo&abc=123') === "{$fusebox->self}?unitTestCommand=foo&abc=123");
		$this->assertTrue( F::url('foo.bar&aaa=1&bbb=2&ccc=3') === "{$fusebox->self}?unitTestCommand=foo.bar&aaa=1&bbb=2&ccc=3");
		// no rewrite : sub-directory
		$_SERVER['SCRIPT_NAME'] = '/unit_test/index.php';
		Framework::setMyself();
		$this->assertTrue( F::url('foo.bar') == '/unit_test/index.php?unitTestCommand=foo.bar' );
		// no rewrite : root directory
		$_SERVER['SCRIPT_NAME'] = '/index.php';
		Framework::setMyself();
		$this->assertTrue( F::url('foo.bar') == '/index.php?unitTestCommand=foo.bar' );
		// url-rewrite : enabled
		$fusebox->config['urlRewrite'] = true;
		Framework::setMyself();
		// url-rewrite : check default command
		$this->assertTrue( F::url() === $fusebox->self );
		// url-rewrite : check command only
		$this->assertTrue( F::url('foo') === "{$fusebox->self}foo" );
		$this->assertTrue( F::url('foo.bar') === "{$fusebox->self}foo/bar" );
		$this->assertTrue( F::url('aaa.bbb.ccc') === "{$fusebox->self}aaa/bbb.ccc" );
		// url-rewrite : check url with parameter
		$this->assertTrue( F::url('foo&abc=123') === "{$fusebox->self}foo/abc=123" );
		$this->assertTrue( F::url('foo.bar&aaa=1&bbb=2&ccc=3') === "{$fusebox->self}foo/bar/aaa=1/bbb=2/ccc=3" );
		$this->assertTrue( F::url('xxx.yyy.zzz&aaa=1&bbb=2&ccc=3') === "{$fusebox->self}xxx/yyy.zzz/aaa=1/bbb=2/ccc=3" );
		// no rewrite : sub-directory
		$_SERVER['SCRIPT_NAME'] = '/unit_test/index.php';
		Framework::setMyself();
		$this->assertTrue( F::url('foo.bar') == '/unit_test/foo/bar/' or F::url('foo.bar') == '/unit_test/foo/bar' );
		// no rewrite : root directory
		$_SERVER['SCRIPT_NAME'] = '/index.php';
		Framework::setMyself();
		$this->assertTrue( F::url('foo.bar') == '/foo/bar/' or F::url('foo.bar') == '/foo/bar' );
		// url-rewrite : check command delimiter
		$fusebox->config['commandDelimiter'] = '-';
		$this->assertFalse( F::url('foo.bar') === "{$fusebox->self}foo/bar" );
		$this->assertTrue( F::url('foo-bar') === "{$fusebox->self}foo/bar" );
		$this->assertTrue( F::url('foo-bar&abc=123') === "{$fusebox->self}foo/bar/abc=123" );
		$fusebox->config['commandDelimiter'] = '.';
		// url-rewrite : check rewrite (no route matched)
		$fusebox->config['route'] = array('/post/(\d+)' => 'unitTestCommand=post.read&id=$1');
		$this->assertTrue( F::url('news.read&id=100') === "{$fusebox->self}news/read/id=100" );
		// url-rewrite : check rewrite (has route matched)
		$fusebox->config['route'] = array('/article/(\d+)' => 'unitTestCommand=article.view&id=$1');
		$this->assertTrue( F::url('article.view&id=99') === "{$fusebox->self}article/99");
		$this->assertFalse( F::url('article.view&id=99&nocache='.time()) === "{$fusebox->self}article/99");
		// url-rewrite : check rewrite (has route matched & more variables)
		$fusebox->config['route'] = array('/news/(\S+)/(\d{4})/(\d{2})' => 'unitTestCommand=news.list&category=$1&year=$2&month=$3');
		$this->assertTrue( F::url('news.list&category=local&year=1997&month=07') == "{$fusebox->self}news/local/1997/07" );
		$this->assertTrue( F::url('news.list&month=09&category=global&year=2001') == "{$fusebox->self}news/global/2001/09" );
		// url-rewrite : check rewrite (has multiple routes matched ===> use first one)
		$fusebox->config['route'] = array(
			'/foo/(\d+)' => 'unitTestCommand=foo.bar&id=$1',
			'/bar/(\d+)' => 'unitTestCommand=foo.bar&id=$1',
		);
		$this->assertTrue( F::url('foo.bar&id=123') == "{$fusebox->self}foo/123");
		// url-rewrite : check rewrite (auto-escape & auto-prepend slash)
		$fusebox->config['route'] = array('\/forum\/(\S+)' => 'unitTestCommand=forum&tag=$1');
		$this->assertTrue( F::url('forum&tag=beauty') === "{$fusebox->self}forum/beauty");
		$fusebox->config['route'] = array('forum/(\S+)' => 'unitTestCommand=forum&tag=$1');
		$this->assertTrue( F::url('forum&tag=finance') === "{$fusebox->self}forum/finance");
		$fusebox->config['route'] = null;
		// clean-up
		$fusebox = null;
	}


} // TestFuseboxyCore