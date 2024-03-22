<?php /*
<fusedoc>
	<description>
		Core component of Fuseboxy framework
	</description>
	<io>
		<in>
			<string name="$mode" scope="Framework" optional="yes" comments="for unit-test of helper" />
			<string name="$configPath" scope="Framework" optional="yes" default="../../../config/fusebox_config.php" />
			<string name="$helperPath" scope="Framework" optional="yes" default="./F.php" />
		</in>
		<out />
	</io>
</fusedoc>
*/
class Framework {


	// constant : mode
	const FUSEBOX_UNIT_TEST          = 101;
	// constant : error
	const FUSEBOX_CONFIG_NOT_FOUND   = 501;
	const FUSEBOX_CONFIG_NOT_DEFINED = 502;
	const FUSEBOX_HELPER_NOT_FOUND   = 503;
	const FUSEBOX_HELPER_NOT_DEFINED = 504;
	const FUSEBOX_MISSING_CONFIG     = 505;
	const FUSEBOX_INVALID_CONFIG     = 506;
	const FUSEBOX_ERROR              = 507;
	const FUSEBOX_PAGE_NOT_FOUND     = 508;
	// constant : others
	const FUSEBOX_REDIRECT           = 901;


	// properties (configurable)
	public static $mode;
	public static $startTick;
	public static $configPath = __DIR__.'/../config/fusebox_config.php';
	public static $helperPath = __DIR__.'/F.php';




	/**
	<fusedoc>
		<description>
			autoload files (or run anonymous function)
		</description>
		<io>
			<in>
				<!-- server variables -->
				<string name="SCRIPT_NAME" scope="$_SERVER" />
				<!-- framework config -->
				<structure name="autoload" scope="$fusebox">
					<string name="+" optional="yes" comments="pattern" example="/path/to/my/site/app/model/*.php" />
					<function name="+" optional="yes" comments="function to run" example="function(){ $foo = 'bar'; }" />
				</structure>
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function autoLoad() {
		global $fusebox;
		if ( !empty($fusebox->config['autoLoad']) ) {
			foreach ( $fusebox->config['autoLoad'] as $pattern ) {
				$isFunction = is_callable($pattern);
				$isPatternLikeDir = ( !$isFunction and ( is_dir($pattern) or in_array(substr($pattern, -1), ['/','\\']) ) );
				$isPatternLikeFile = ( !$isFunction and !empty($pattern) and strpos($pattern, '*') === false and strtolower(substr($pattern, -4)) == '.php' );
				// call as function
				if ( $isFunction ) {
					call_user_func($pattern);
				// directory not found
				} elseif ( $isPatternLikeDir and !is_dir($pattern) ) {
					throw new Exception("Autoload directory not found ({$pattern})", self::FUSEBOX_INVALID_CONFIG);
				// file not found
				} elseif ( $isPatternLikeFile and empty(glob($pattern)) ) {
					throw new Exception("Autoload file not found ({$pattern})", self::FUSEBOX_INVALID_CONFIG);
				// load files (when directory or file exists)
				} elseif ( !empty($pattern) ) {
					// when only specified directory
					// ===> load php files but not others
					if ( $isPatternLikeDir ) $pattern = rtrim($pattern, '/\\').'/*.php';
					// go through each item
					foreach ( glob($pattern) as $path ) {
						// when not file
						// ===> simply do nothing
						if ( !is_file($path) ) {
							// do nothing...
						// when file is php
						// ===> load & run as php
						} elseif ( is_file($path) and pathinfo(strtolower($path), PATHINFO_EXTENSION) == 'php' ) {
							require_once $path;
						// when file is other type
						// ===> display the content (for security purpose)
						} elseif ( is_file($path) ) {
							echo file_get_contents($path);
						}
					} // foreach-glob-pattern
				}
			} // foreach-pattern
		} // if-autoload
	}




	/**
	<fusedoc>
		<description>
			fix config
		</description>
		<io>
			<in>
				<!-- framework config -->
				<structure name="config" scope="$fusebox" />
			</in>
			<out>
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="appPath|vendorPath|baseDir|baseUrl|uploadDir|uploadUrl" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function fixConfig() {
		global $fusebox;
		// unify slash & append trailing-slash
		foreach ( ['appPath','vendorPath','baseDir','baseUrl','uploadDir','uploadUrl'] as $pathName ) {
			if ( !empty($fusebox->config[$pathName]) ) {
				$fusebox->config[$pathName]  = str_replace('\\', '/', $fusebox->config[$pathName]);
				$fusebox->config[$pathName] .= ( substr($fuseboxy->config[$pathName], -1) != '/' ) ? '/' : '';
			}
		}
	}




	/**
	<fusedoc>
		<description>
			initiate fusebox API object
			===> use {global} instead of {$_GLOBALS}
			===> make developer easier to access the object (without typing too much)
		</description>
		<io>
			<in />
			<out>
				<object name="$fusebox|$fuseboxy" scope="~global~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initAPI() {
		global $fusebox, $fuseboxy;
		$fusebox = $fuseboxy = new StdClass();
		$fuseboxy = &$fusebox;
	}




	/**
	<fusedoc>
		<description>
			load config and assign default value
		</description>
		<io>
			<in>
				<!-- property -->
				<string name="$configPath" scope="self" />
			</in>
			<out>
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="commandVariable" />
					<string name="appPath" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function loadConfig() {
		global $fusebox;
		// validate config file
		if ( file_exists(self::$configPath) ) {
			$fusebox->config = include self::$configPath;
		} else {
			if ( !headers_sent() ) header("HTTP/1.0 500 Internal Server Error");
			throw new Exception("Config file not found (".self::$configPath.")", self::FUSEBOX_CONFIG_NOT_FOUND);
		}
		if ( !is_array($fusebox->config) ) {
			if ( !headers_sent() ) header("HTTP/1.0 500 Internal Server Error");
			throw new Exception("Config file must return an Array", self::FUSEBOX_CONFIG_NOT_DEFINED);
		}
		// define config default value (when necessary)
		$fusebox->config['commandVariable'] = isset($fusebox->config['commandVariable']) ? $fusebox->config['commandVariable'] : 'fuseaction';
		$fusebox->config['appPath'] = isset($fusebox->config['appPath']) ? $fusebox->config['appPath'] : (str_replace('\\', '/', dirname(dirname(__FILE__))).'/');
	}




	/**
	<fusedoc>
		<description>
			load framework helper utility
			===> when {$fusebox} api is ready
		</description>
		<io>
			<in>
				<!-- property -->
				<string name="$helperPath" scope="self" />
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function loadHelper() {
		global $fusebox;
		// check helper path
		if ( !file_exists(self::$helperPath) ) {
			if ( !headers_sent() ) header("HTTP/1.0 500 Internal Server Error");
			throw new Exception("Helper class file not found (".self::$helperPath.")", self::FUSEBOX_HELPER_NOT_FOUND);
		}
		// load helper
		require_once self::$helperPath;
		// validate after load
		if ( !class_exists('F') ) {
			if ( !headers_sent() ) header("HTTP/1.0 500 Internal Server Error");
			throw new Exception("Helper class (F) not defined", self::FUSEBOX_HELPER_NOT_DEFINED);
		}
	}




	/**
	<fusedoc>
		<description>
			formUrl2arguments
			===> default merging POST & GET scope
			===> user could define array of scopes to merge
		</description>
		<io>
			<in>
				<structure name="config" scope="$fusebox">
					<boolean name="formUrl2arguments" optional="yes" comments="use default scopes & precedence (form-over-url)" />
					<array name="formUrl2arguments" optional="yes" comments="custom scopes & precedence (e.g. url-over-form, including cookies, etc.)">
						<structure name="+" comments="variable scopes" example="$_GET|$_POST|$_COOKIES|.." />
					</array>
				</structure>
			</in>
			<out>
				<structure name="$arguments">
					<mixed name="*" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function formUrl2arguments() {
		global $fusebox, $arguments;
		if ( isset($fusebox->config['formUrl2arguments']) and !empty($fusebox->config['formUrl2arguments']) ) {
			global $arguments;
			// config default
			if ( $fusebox->config['formUrl2arguments'] === true or $fusebox->config['formUrl2arguments'] === 1 ) {
				$fusebox->config['formUrl2arguments'] = array($_GET, $_POST);
			}
			// copy variables from scope to container (precedence = first-come-first-serve)
			if ( is_array($fusebox->config['formUrl2arguments']) ) {
				$arguments = array();
				foreach ( $fusebox->config['formUrl2arguments'] as $scope ) $arguments += $scope;
			// validation
			} else {
				if ( !headers_sent() ) header("HTTP/1.0 500 Internal Server Error");
				throw new Exception("Config {formUrl2arguments} must be Boolean or Array", self::FUSEBOX_INVALID_CONFIG);
			}
		}
	}




	/**
	<fusedoc>
		<description>
			main function
			===> run specific controller and action
		</description>
		<io>
			<in>
				<string name="controller" scope="$fusebox" />
			</in>
			<out>
				<number name="$startTick" scope="self" comments="millisecond" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function run() {
		global $fusebox, $fuseboxy, $arguments;
		try {
			// mark start time (ms)
			self::$startTick = microtime(true)*1000;
			// main process...
			self::initAPI();
			self::loadConfig();
			self::validateConfig();
			self::loadHelper();
			self::setMyself();
			self::autoLoad();
			self::urlRewrite();
			self::formUrl2arguments();
			self::setControllerAction();
			// do not run when no controller specified
			// ===> e.g. when default-command is empty
			// ===> otherwise, load controller and run!
			if ( !empty($fusebox->controller) ) {
				$__controllerPath = F::appPath('controller/'.str_ireplace('-', '_', $fusebox->controller).'_controller.php');
				F::pageNotFound( !file_exists($__controllerPath) );
				include $__controllerPath;
			}
		} catch (Exception $e) {
			F::error($e);
		}
	}




	/**
	<fusedoc>
		<description>
			get controller & action out of command
		</description>
		<io>
			<in>
				<!-- url variables -->
				<string name="~commandVariable~" scope="$_GET|$_POST" />
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="commandVariable" example="fuseaction" />
					<string name="defaultCommand" example="home.index" />
				</structure>
			</in>
			<out>
				<!-- framework api object -->
				<string name="controller" scope="$fusebox" />
				<string name="action" scope="$fusebox" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function setControllerAction() {
		global $fusebox;
		// if no command was defined, use {defaultCommand} in config
		if ( !empty($_GET[$fusebox->config['commandVariable']]) ) {
			$command = $_GET[$fusebox->config['commandVariable']];
		} elseif ( !empty($_POST[$fusebox->config['commandVariable']]) ) {
			$command = $_POST[$fusebox->config['commandVariable']];
		} elseif ( !empty($fusebox->config['defaultCommand']) ) {
			$command = $fusebox->config['defaultCommand'];
		} else {
			$command = false;
		}
		// parse controller & action
		$parsed = F::parseCommand($command);
		// modify fusebox-api variable
		$fusebox->controller = $parsed['controller'];
		$fusebox->action = $parsed['action'];
	}




	/**
	<fusedoc>
		<description>
			prepare api variables
		</description>
		<io>
			<in>
				<!-- server variables -->
				<string name="SCRIPT_NAME" scope="$_SERVER" />
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="commandVariable" />
					<boolean name="urlRewrite" />
				</structure>
			</in>
			<out>
				<!-- framework api object -->
				<string name="self" scope="$fusebox" example="/my/site/index.php" />
				<string name="myself" scope="$fusebox" example="/my/site/index.php?fuseaction=" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function setMyself() {
		global $fusebox;
		if ( !empty($fusebox->config['urlRewrite']) ) {
			$fusebox->self = dirname($_SERVER['SCRIPT_NAME']);
			$fusebox->self = str_replace('\\', '/', $fusebox->self);
			if ( substr($fusebox->self, -1) != '/' ) $fusebox->self .= '/';
			$fusebox->myself = $fusebox->self;
		} else {
			$fusebox->self = $_SERVER['SCRIPT_NAME'];
			$fusebox->myself = "{$fusebox->self}?{$fusebox->config['commandVariable']}=";
		}
	}




	/**
	<fusedoc>
		<description>
			extract command and url variables from beauty-url
			===> work closely with {$fusebox->config['route']} and F::url()
		</description>
		<io>
			<in>
				<!-- server variables -->
				<string name="SCRIPT_NAME" scope="$_SERVER" />
				<string name="REQUEST_URI" scope="$_SERVER" />
				<string name="REDIRECT_QUERY_STRING" scope="$_SERVER" optional="yes" />
				<string name="~REDIRECT_QUERY_STRING~" scope="$_GET|$_REQUEST" optional="yes" />
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="commandVariable" />
					<boolean name="urlRewrite" />
					<structure name="route" optional="yes" comments="url-rewrite pattern" />
				</structure>
			</in>
			<out>
				<!-- manipulated php variables -->
				<string name="QUERY_STRING" scope="$_SERVER" />
				<mixed name="~queryStringVariables~" scope="$_GET|$_REQUEST" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function urlRewrite() {
		global $fusebox;
		// request <http://{HOST}/{APP}/foo/bar> will have <REQUEST_URI=/{APP}/foo/bar>
		// request <http://{HOST}/foo/bar> will have <REQUEST_URI=/foo/bar>
		// request <http://{HOST}/foo/bar?a=1&b=2> will have <REQUEST_URI=/foo/bar?a=1&b=2>
		$isRoot = dirname($_SERVER['SCRIPT_NAME']) == rtrim($_SERVER['REQUEST_URI'], '/');
		// only process when necessary
		if ( !empty($fusebox->config['urlRewrite']) and !$isRoot ) {
			// remove dummy url param (when necessary)
			if ( isset($_SERVER['REDIRECT_QUERY_STRING']) ) {
				if ( isset($_GET[$_SERVER['REDIRECT_QUERY_STRING']]) ) unset($_GET[$_SERVER['REDIRECT_QUERY_STRING']]);
				if ( isset($_REQUEST[$_SERVER['REDIRECT_QUERY_STRING']]) ) unset($_REQUEST[$_SERVER['REDIRECT_QUERY_STRING']]);
			}
			// cleanse the route config (and keep the sequence)
			if ( isset($fusebox->config['route']) ) {
				$fixedRoute = array();
				foreach ( $fusebox->config['route'] as $urlPattern => $qsReplacement ) {
					// clean unnecessary spaces
					$urlPattern = trim($urlPattern);
					$qsReplacement = trim($qsReplacement);
					// prepend forward-slash (when necessary)
					if ( substr($urlPattern, 0, 1) !== '/' and substr($urlPattern, 0, 2) != '\\/' ) {
						$urlPattern = '/'.$urlPattern;
					}
					// remove multi-(forward-)slash
					do { $urlPattern = str_replace('//', '/', $urlPattern); } while ( strpos($urlPattern, '//') !== false );
					// escape forward-slash
					$urlPattern = str_replace('/', '\\/', $urlPattern);
					// fix double-escaped forward-slash
					$urlPattern = str_replace('\\\\/', '\\/', $urlPattern);
					// put into container
					$fixedRoute[$urlPattern] = $qsReplacement;
				}
				$fusebox->config['route'] = $fixedRoute;
			}
			// start to parse the path
			$qs = rtrim($_SERVER['REQUEST_URI'], '/');
			// (1) unify slash
			// e.g.  /my/site//foo\bar\999??a=1&b=2&&c=3&  ------->  /my/site//foo/bar/999??a=1&b=2&&c=3&
			$qs = str_replace('\\', '/', $qs);
			// (2) dupe slash, question-mark, and and-sign
			// e.g.  /my/site//foo/bar/999??a=1&b=2&&c=3&  ------->  /my/site/foo/bar/999??a=1&b=2&&c=3&
			// e.g.  /my/site/foo/bar/999??a=1&b=2&&c=3&  -------->  /my/site/foo/bar/999?a=1&b=2&&c=3&
			// e.g.  /my/site/foo/bar/999??a=1&b=2&&c=3&  -------->  /my/site/foo/bar/999?a=1&b=2&c=3&
			$qs = preg_replace('/\/+/', '/', $qs);
			$qs = preg_replace('/\?+/', '?', $qs);
			$qs = preg_replace('/&+/' , '&', $qs);
			// (3) extract (potential) query-string from path
			// e.g.  /my/site/index.php  ------------------------->  \my\site
			// e.g.  \my\site  ----------------------------------->  /my/site
			// e.g.  /my/site  ----------------------------------->  /my/site/
			// e.g.  /my/site/foo/bar/999?a=1&b=2&c=3&  ---------->  foo/bar/999?a=1&b=2&c=3&
			$baseDir = dirname($_SERVER['SCRIPT_NAME']);
			$baseDir = str_replace('\\', '/', $baseDir);
			if ( substr($baseDir, -1) != '/' ) $baseDir .= '/';
			$baseDirPattern = preg_quote($baseDir, '/');
			$qs = preg_replace("/{$baseDirPattern}/", '', $qs, 1);
			// (4) append leading slash to path
			// e.g.  foo/bar/999?a=1&b=2&c=3&  ------------------->  /foo/bar/999?a=1&b=2&c=3&
			if ( substr($qs, 0, 1) != '/' ) $qs = '/'.$qs;                       
			// (5) check if there is route match, and apply the first match
			// e.g.  /foo/bar/([0-9]+)(.*)  ---------------------->  fuseaction=foo.bar&xyz=$1&$2
			$hasRouteMatch = false;
			$routes = F::config('route') ? F::config('route') : array();
			foreach ( $routes as $urlPattern => $qsReplacement ) {
				// if path-like-query-string match the route pattern...
				if ( !$hasRouteMatch and preg_match("/{$urlPattern}/", $qs) ) {
					// turn it into true query-string
					// e.g.  /foo/bar/999?a=1&b=2&c=3&  ---------->  fuseaction=foo.bar&xyz=999?a=1&b=2&c=3&
					$qs = preg_replace("/{$urlPattern}/", $qsReplacement, $qs);
					// mark flag
					$hasRouteMatch = true;
				}
			}
			// (6) unify query-string delim (replace first question-mark only)
			// e.g.  /foo/bar/999?a=1&b=2&c=3&  ------------------>  /foo/bar/999&a=1&b=2&c=3&
			$qs = preg_replace('/\?/', '&', $qs, 1);
			// (7) if match none of the route, then turn path into query-string
			if ( !$hasRouteMatch ) {
				$qs = str_replace('/', '&', trim($qs, '/'));
				$arr = explode('&', $qs);
				if ( count($arr) == 1 and $arr[0] == '' ) $arr = array();
				$qs = '';
				// turn path-like-query-string into query-string
				// ===> extract (at most) first two elements for command-variable
				// ===> treat as command-variable when element was unnamed (no equal-sign)
				// ===> treat as url-param when element was named (has equal-sign)
				if ( count($arr) and strpos($arr[0], '=') === false ) {  // 1st time
					$qs .= ( $fusebox->config['commandVariable'] . '=' . array_shift($arr) );
				}
				if ( count($arr) and strpos($arr[0], '=') === false ) {  // 2nd time
					$qs .= ( '.' . array_shift($arr) );
				}
				// join remaining elements into query-string
				$qs .= ( '&' . implode('&', $arr) );
			}
			// (8) remove unnecessary query-string delimiter
			// e.g.  fuseaction=foo.bar&xyz=999&a=1&b=2&c=3&  ---------->  fuseaction=foo.bar&xyz=999&a=1&b=2&c=3
			$qs = trim($qs, '&');
			// (9) dupe query-string delimiter again
			$qs = preg_replace('/&+/' , '&', $qs);
			// (10) put parameters of query-string into GET scope
			$qsArray = explode('&', $qs);
			foreach ( $qsArray as $param ) {
				$param = explode('=', $param, 2);
				$paramKey = isset($param[0]) ? urldecode($param[0]) : '';
				$paramVal = isset($param[1]) ? urldecode($param[1]) : '';
				if ( !empty($paramKey) ) {
					// simple parameter
					if ( strpos($paramKey, '[') === false ) {
						$_GET[$paramKey] = $paramVal;
					// array parameter
					} else {
						$arrayDepth = substr_count($paramKey, '[');
						$arrayKeys = explode('[', str_replace(']', '', $paramKey));
						foreach ( $arrayKeys as $i => $singleArrayKey ) {
							if ( $i == 0 ) $pointer = &$_GET;
							if ( $singleArrayKey != '' ) {
								$pointer[$singleArrayKey] = isset($pointer[$singleArrayKey]) ? $pointer[$singleArrayKey] : array();
								$pointer = &$pointer[$singleArrayKey];
							} else {
								$pointer[count($pointer)] = isset($pointer[count($pointer)]) ? $pointer[count($pointer)] : array();
								$pointer = &$pointer[count($pointer)-1];
							}
							if ( $i+1 == count($arrayKeys) ) $pointer = $paramVal;
						}
						unset($pointer);
					}
				}
			}
			// (11) update REQUEST and SERVER scopes
			// ===> only update query-string when request coming as beauty-url
			// e.g.  /my/site/foo/bar/a=1/b=2/c=3  ------------------------------->  update query-string
			// e.g.  /my/site/index.php?fuseaction=foo.bar&a=1&b=2&c=3  ---------->  do not update query-string
			$_REQUEST += $_GET;
			$isBeautyURL = ( $_SERVER['SCRIPT_NAME'] != substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['SCRIPT_NAME'])) );
			if ( $isBeautyURL ) $_SERVER['QUERY_STRING'] = $qs;
		} // if-url-rewrite
	}




	/**
	<fusedoc>
		<description>
			validate config
		</description>
		<io>
			<in>
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="commandVariable" optional="yes" />
					<string name="appPath" optional="yes" />
					<string name="errorController" optional="yes" />
				</structure>
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function validateConfig() {
		global $fusebox;
		// check required config
		foreach ( array('commandVariable','appPath') as $key ) {
			if ( empty($fusebox->config[$key]) ) {
				if ( !headers_sent() ) header("HTTP/1.0 500 Internal Server Error");
				throw new Exception("Fusebox config variable {{$key}} is required", self::FUSEBOX_MISSING_CONFIG);
			}
		}
		// check command-variable
		if ( in_array(strtolower($fusebox->config['commandVariable']), array('controller','action')) ) {
			if ( !headers_sent() ) header("HTTP/1.0 500 Internal Server Error");
			throw new Exception("Config {commandVariable} can not be 'controller' or 'action'", self::FUSEBOX_INVALID_CONFIG);

		}
		// check app-path
		if ( !is_dir($fusebox->config['appPath']) ) {
			if ( !headers_sent() ) header("HTTP/1.0 500 Internal Server Error");
			throw new Exception("Directory specified in config {appPath} does not exist ({$fusebox->config['appPath']})", self::FUSEBOX_INVALID_CONFIG);
		}
		// check error-controller
		if ( !empty($fusebox->config['errorController']) and !is_file($fusebox->config['errorController']) ) {
			if ( !headers_sent() ) header("HTTP/1.0 500 Internal Server Error");
			throw new Exception("Error controller does not exist ({$fusebox->config['errorController']})", self::FUSEBOX_INVALID_CONFIG);
		}
	}


} // class