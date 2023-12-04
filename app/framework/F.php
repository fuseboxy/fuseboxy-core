<?php
// Helper component for Fuseboxy framework
class F {


	/**
	<fusedoc>
		<description>
			check whether this is (jQuery) ajax request
		</description>
		<io>
			<in>
				<string name="HTTP_X_REQUESTED_WITH" scope="$_SERVER" optional="yes" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function ajaxRequest() {
		return ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' );
	}




	/**
	<fusedoc>
		<description>
			display alert message without aborting operation
		</description>
		<io>
			<in>
				<string_or_structure name="$flash">
					<string name="type" optional="yes" default="primary" comments="primary|secondary|success|info|warning|danger|light|dark" />
					<string name="icon" optional="yes" />
					<string name="heading" optional="yes" />
					<string name="message" optional="yes" />
					<string name="remark" optional="yes" />
				</string_or_structure>
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function alert($flash='alert', $condition=true) {
		echo self::alertOutput($flash, $condition);
	}




	/*
	<fusedoc>
		<description>
			obtain alert message output
		</description>
		<io>
			<in>
				<string_or_structure name="$flash">
					<string name="type" optional="yes" default="primary" comments="primary|secondary|success|info|warning|danger|light|dark" />
					<string name="id" optional="yes" comments="div[id]" />
					<string name="icon" optional="yes" />
					<string name="heading" optional="yes" />
					<string name="message" optional="yes" />
					<string name="remark" optional="yes" />
				</string_or_structure>
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function alertOutput($flash='alert', $condition=true) {
		// check whether to show message
		if ( !$condition ) return null;
		// fix param & set default
		if ( !empty($flash) ) :
			if ( !is_array($flash) ) $flash = array('message' => $flash);
			if ( empty($flash['type']) ) $flash['type'] = 'primary';
		endif;
		// capture output
		ob_start();
		if (
			!empty($flash['icon']) or
			!empty($flash['remark']) or
			!empty($flash['heading']) or
			!empty($flash['message'])
		) :
			?><div id="<?php echo $flash['id'] ?? ''; ?>" class="alert alert-<?php echo $flash['type']; ?>"><?php
				if ( !empty($flash['icon']) ) :
					?><span class="mr-2"><i class="<?php echo $flash['icon']; ?>"></i></span><?php
				endif;
				if ( !empty($flash['heading']) ) :
					?><strong class="mr-1"><?php echo $flash['heading']; ?></strong><?php
				endif;
				if ( !empty($flash['message']) ) :
					?><span><?php echo $flash['message']; ?></span><?php
				endif;
				if ( !empty($flash['remark']) ) :
					?><small class="float-right text-muted pt-1"><?php echo $flash['remark']; ?></small><?php
				endif;
			?></div><?php
		endif;
		// done!
		return ob_get_clean();
	}




	/**
	<fusebox>
		<description>
			obtain correct path of the file (or directory)
			===> look for [app] directory first
			===> then look for [vendor] directory (of composer packages)
		</description>
		<io>
			<in>
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="appPath" example="/path/to/my/site/app/" />
				</structure>
				<!-- parameter -->
				<string name="$relPath" optional="yes" comments="file path relative to app directory" example="view/global/layout.php" />
			</in>
			<out>
				<string name="~return~" comments="absolute path (relative path with base directory prepended)" example="/path/to/my/site/app/view/global/layout.php" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function appPath($relPath=null) {
		global $fusebox;
		// if nothing specified
		// ===> simply return config
		if ( empty($relPath) ) return $fusebox->config['appPath'];
		// look into app path
		$appPathFile = $fusebox->config['appPath'].$relPath;
		if ( file_exists($appPathFile) ) return $appPathFile;
		// if file not found in app path
		// ===> look through each fuseboxy module under vendor path
		$glob = glob($fusebox->config['vendorPath'].'fuseboxy/*/app/'.$relPath);
		if ( !empty($glob[0]) ) return $glob[0];
		// file not found
		// ===> return non-exist path
		// ===> let php show the warning
		return $appPathFile;
	}




	/**
	<fusedoc>
		<description>
			obtain current controller and/or action
		</description>
		<io>
			<in>
				<!-- framework api -->
				<string name="controller" example="home" scope="$fusebox" />
				<string name="action" example="index" scope="$fusebox" />
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="defaultCommand" example="home.index" />
				</structure>
				<!-- parameter -->
				<string name="$key" optional="yes" comments="controller|action" />
			</in>
			<out>
				<string name="~return~" example="home.index|home|index" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function command($key=null) {
		global $fusebox;
		if ( empty($fusebox->config['defaultCommand']) ) return false;
		if ( empty($key) ) return $fusebox->controller.'.'.$fusebox->action;
		if ( strtolower($key) == 'controller' ) return $fusebox->controller;
		if ( strtolower($key) == 'action' ) return $fusebox->action;
		return null;
	}




	/**
	<fusedoc>
		<description>
			obtain framework config
		</description>
		<io>
			<in>
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<mixed name="*" />
				</structure>
				<!-- parameter -->
				<string name="$key" optional="yes" example="defaultCommand|db|smtp|.." />
			</in>
			<out>
				<mixed name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function config($key=null) {
		global $fusebox;
		return empty($key) ? $fusebox->config : ( $fusebox->config[$key] ?? null );
	}




	/**
	<fusedoc>
		<description>
			show error, send header, and abort operation
			===> throw exception when unit test
			===> load error-controller & abort operation (when error-controller specified)
			===> simply show message & abbort operation (when no error-controller)
		</description>
		<io>
			<in>
				<!-- constant -->
				<number name="$mode" scope="Framework" example="101" />
				<!-- config -->
				<structure name="config" scope="$fusebox">
					<path name="errorController" example="/path/to/my/site/app/controller/error_controller.php" />
				</structure>
				<!-- parameters -->
				<string name="$msg" optional="yes" default="error" />
				<boolean name="$condition" optional="yes" default="true" />
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function error($msg='error', $condition=true) {
		global $fusebox;
		if ( $condition ) {
			if ( !headers_sent() ) header("HTTP/1.0 403 Forbidden");
			// set error message to API object
			$fusebox->error = $msg;
			// throw header-string as exception in order to abort operation without stopping unit-test
			if ( Framework::$mode == Framework::FUSEBOX_UNIT_TEST ) throw new Exception(self::command()." - ".$fusebox->error, Framework::FUSEBOX_ERROR);
			// show error with (customize-able) error-controller
			elseif ( !empty($fusebox->config['errorController']) ) exit( include $fusebox->config['errorController'] );
			// simply show error as text
			else exit($fusebox->error);
		}
	}




	/**
	<fusedoc>
		<description>
			obtain execution time
		</description>
		<io>
			<in>
				<!-- framework -->
				<number name="$startTick" scope="Framework" comments="millisecond" />
				<!-- parameter -->
				<string name="$unit" default="ms" comments="ms|s" />
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function et($unit='ms') {
		$unit = strtolower($unit);
		// check unit
		if ( !in_array($unit, ['ms','s']) ) {
			throw new Exception('Invalid unit of time', Framework::FUSEBOX_ERROR);
		// not started yet
		} elseif ( !isset(Framework::$startTick) ) {
			return false;
		}
		// calculation
		$et = round(microtime(true)*1000-Framework::$startTick);
		if ( $unit == 's' ) $et = $et / 1000;
		// done!
		return $et;
	}
	// alias method
	public function runtime($unit='ms') { return self::et($unit); }




	/**
	<fusedoc>
		<description>
			turn form variables to url variables
			===> make it browser-back-button-friendly
			===> redirect to same page with form variables moved to query-string
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function form2url($delim='|') {
		if ( !empty($_POST) ) {
			$qs = $_SERVER['QUERY_STRING'];
			foreach ( $_POST as $key => $val ) {
				$val = is_array($val) ? implode($delim, $val) : $val;
				$qs .= "&{$key}={$val}";
			}
			exit( header("Location: {$_SERVER['PHP_SELF']}?{$qs}") );
		}
	}




	/**
	<fusedoc>
		<description>
			invoke specific command
			--
			[Example use case]
			===> home page has {Latest News} and {Contact Us} sections
			===> specify [home.index] and [home.news] and [home.contact] actions in controller
			===> page [home.index] invokes [home.news] and [home.contact] to show both at same page
			===> page [home.news] and [home.contact] can also be accessed individually as separate pages
		</description>
		<io>
			<in>
				<string name="$commandWithQueryString" example="product.view&id=999" />
				<structure name="$arguments" optional="yes" default="~emptyArray~" />
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- manipulated api object -->
				<string name="controller" scope="$fusebox" />
				<string name="action" scope="$fusebox" />
				<!-- command stack -->
				<array name="invokeQueue" scope="$fusebox">
					<string name="+" comments="command" example="product.view|product.recommend|.." />
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function invoke($commandWithQueryString, $arguments=[]) {
		global $fusebox;
		// create stack container to keep track of command-in-run
		// ===> first item of invoke queue should be original command
		// ===> second item onward will be the command(s) called by F::invoke()
		if ( !isset($fusebox->invokeQueue) ) $fusebox->invokeQueue = array();
		$fusebox->invokeQueue[] = "{$fusebox->controller}.{$fusebox->action}";
		// split new command & query-string (if any)
		$commandWithQueryString = str_replace('?', '&', $commandWithQueryString);
		$arr = explode('&', $commandWithQueryString);
		$command = $arr[0] ?? '';
		$queryString = $arr[1] ?? '';
		// parse new command
		$command = self::parseCommand($command);
		$fusebox->controller = $command['controller'];
		$fusebox->action = $command['action'];
		$controllerPath = "{$fusebox->config['appPath']}/controller/{$fusebox->controller}_controller.php";
		// merge query-string & arguments
		parse_str($queryString, $queryString);
		$arguments = array_merge($queryString, $arguments);
		// check controller existence
		F::pageNotFound( !file_exists($controllerPath) );
		// run new command
		include $controllerPath;
		// trim stack after run
		// ===> reset to original command
		$originalCommand = self::parseCommand( array_pop($fusebox->invokeQueue) );
		$fusebox->controller = $originalCommand['controller'];
		$fusebox->action = $originalCommand['action'];
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			obtain output when invoking specific command
		</description>
		<io>
			<in>
				<string name="$command" example="home.news" />
				<structure name="$arguments" optional="yes" default="~emptyArray~" />
			</in>
			<out>
				<string name="~return~" format="html" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function invokeOutput($command, $arguments=[]) {
		ob_start();
		self::invoke($command, $arguments);
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			check whether this is an internal invoke
			===> first request, which is not internal, was invoked by framework core (fuseboxy.php)
		</description>
		<io>
			<in>
				<!-- framework -->
				<array name="invokeQueue" scope="$fusebox" optional="yes" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function invokeRequest() {
		global $fusebox;
		return !empty($fusebox->invokeQueue);
	}




	/**
	<fusedoc>
		<description>
			case-sensitive check on command (with wildcard), for example...
			===> specific controller + action ===> F::is('site.index')
			===> specific controller ===> F::is('site.*')
			===> specific action ===> F::is('*.index')
		</description>
		<io>
			<in>
				<list name="$commandPatternList" delim="," example="home.index,news.*,*.news" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function is($commandPatternList) {
		global $fusebox;
		// allow checking multiple command-patterns
		if ( !is_array($commandPatternList) ) {
			$commandPatternList = explode(',', $commandPatternList);
		}
		// check each user-provided command-pattern
		foreach ( $commandPatternList as $commandPattern ) {
			$commandPattern = self::parseCommand($commandPattern);
			// consider match when either one is ok
			if ( in_array($commandPattern['controller'], array('*', $fusebox->controller)) and in_array($commandPattern['action'], array('*', $fusebox->action)) ) {
				return true;
			}
		}
		// no match
		return false;
	}




	/**
	<fusedoc>
		<description>
			show 404 not found page
			===> throw exception when unit test
			===> load error-controller & abort operation (when error-controller specified)
			===> simply display message & abort operation (when no error-controller)
			--
			for a customized 404 page
			===> check in error-controller for [page not found] message
			===> then load 404 custom page
		</description>
		<io>
			<in>
				<boolean name="$condition" optional="yes" default="true" />
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function pageNotFound($condition=true) {
		global $fusebox;
		if ( $condition ) {
			if ( !headers_sent() ) header("HTTP/1.0 404 Not Found");
			$fusebox->error = 'Page not found';
			if ( Framework::$mode == Framework::FUSEBOX_UNIT_TEST ) throw new Exception(self::command()." - ".$fusebox->error, Framework::FUSEBOX_PAGE_NOT_FOUND);
			elseif ( !empty($fusebox->config['errorController']) ) exit( include $fusebox->config['errorController'] );
			else exit($fusebox->error);
		}
	}




	/**
	<fusedoc>
		<description>
			extract controller & action from command
		</description>
		<io>
			<in>
				<string name="$command" example="home|product.view|.." />
			</in>
			<out>
				<structure name="~return~">
					<string name="controller" example="home" />
					<string name="action" example="index" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function parseCommand($command) {
		global $fusebox;
		// split command by delimiter (when not empty)
		if ( !empty($command) ) {
			$arr = explode('.', $command, 2);
			return array(
				'controller' => $arr[0],
				'action' => !empty($arr[1]) ? $arr[1] : 'index'
			);
		// both are false when command is empty
		} else {
			return array(
				'controller' => false,
				'action' => false,
			);
		}
	}




	/**
	<fusedoc>
		<description>
			redirect to specific command
			===> command might include query-string
			===> throw exception when unit test
			===> redirect by browser header & abort operation (when header not sent yet)
			===> redirect by javascript & abort operation (when header already sent)
		</description>
		<io>
			<in>
				<string name="$command" example="product.index|product.view&id=999|.." />
				<boolean name="$condition" default="true" />
				<number name="$delay" default="0" comments="number of seconds to wait before redirection" />
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function redirect($command, $condition=true, $delay=0) {
		// transform command to url
		$url = self::url($command);
		// only redirect when condition is true
		if ( $condition ) {
			// must use Location when no delay because Refresh doesn't work on ajax-request
			$headerString = empty($delay) ? "Location:{$url}" : "Refresh:{$delay};url={$url}";
			// throw header-string as exception in order to abort operation without stopping unit-test
			if ( Framework::$mode == Framework::FUSEBOX_UNIT_TEST ) throw new Exception($headerString, Framework::FUSEBOX_REDIRECT);
			// invoke redirect at server-side
			elseif ( !headers_sent() ) exit( header($headerString) );
			// invoke redirect at client-side (when header already sent)
			else exit("<script>window.setTimeout(function(){document.location.href='{$url}';},{$delay}*1000);</script>");
		}
	}




	/**
	<fusedoc>
		<description>
			determine the protocol which client browser is using
		</description>
		<io>
			<in>
				<string name="HTTP_X_FORWARDED_PROTO" scope="$_SERVER" optional="yes" />
				<string name="HTTPS" scope="$_SERVER" optional="yes" />
				<string name="REQUEST_SCHEME" scope="$_SERVER" optional="yes" />
			</in>
			<out>
				<string name="~return~" value="http|https" />
			</out>
		</io>
	</fusedoc>
	*/
	private static function requestScheme() {
		if ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ) return $_SERVER['HTTP_X_FORWARDED_PROTO'];
		if ( isset($_SERVER['HTTPS']) and in_array($_SERVER['HTTPS'], ['on','1']) ) return 'https';
		if ( isset($_SERVER['REQUEST_SCHEME']) ) return $_SERVER['REQUEST_SCHEME'];
		return 'http';
	}




	/**
	<fusedoc>
		<description>
			transform url (with param)
			===> append fusebox-myself to url
			===> turn it into beautify-url (if enable)
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="config" scope="$fusebox">
					<string name="commandVariable" example="fuseaction" />
					<boolean name="urlRewrite" />
					<structure name="route" comments="url-rewrite patterns" />
				</structure>
				<!-- parameter -->
				<string name="$commandWithQueryString" optional="yes" example="product.view&id=10" />
			</in>
			<out>
				<string name="~returnNormalURL~" oncondition="when {urlRewrite=true}" example="/my/site/index.php?fuseaction=product.view&id=10" />
				<string name="~returnBeautifyURL~" oncondition="when {urlRewrite=false}" example="/my/site/product/view/id=10" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function url($commandWithQueryString=null) {
		global $fusebox;
		// validation : no command defined
		// ===> simply return self (no matter url-rewrite or not)
		if ( empty($commandWithQueryString) ) {
			return $fusebox->self;
		// validation : external url
		// ===> simply return without any transformation
		} elseif ( false
			or $commandWithQueryString[0] == '/' 
			or substr(strtolower(trim($commandWithQueryString)), 0, 7) == 'http://' 
			or substr(strtolower(trim($commandWithQueryString)), 0, 8) == 'https://' 
		) {
			return $commandWithQueryString;
		// validation : no rewrite with query-string
		// ===> prepend self and command-variable
		} elseif ( empty($fusebox->config['urlRewrite']) ) {
			return $fusebox->myself.$commandWithQueryString;
		}
		// rewrite (with or without query-string)
		// ===> transform to beauty-url
		// ===> check route as well
		$qs = explode('&', $commandWithQueryString);
		// first element has command-delimiter and no equal-sign
		// ===> first element is command
		// ===> replace first occurrence of delimiter with slash (if any)
		if ( strpos($qs[0], '=') === false ) {
			$qs[0] = explode('.', $qs[0], 2);
			$qs[0] = implode('/', $qs[0]);
		}
		// turn query-string into path-like-query-string
		$qsPath = implode('/', $qs);
		$qsPath = preg_replace('~^/+|/+$|/(?=/)~', '', $qsPath);  // remove multi-slash
		$qsPath = trim($qsPath, '/');  // trim leading and trailing slash
		// further beautify the url according to route pattern
		// ===> e.g. convert <article&type=abc&id=1> to <article/abc/1> instead of <article/type=abc/id=1>
		if ( !empty($fusebox->config['route']) ) {
			// compare it against each route pattern
			foreach ( $fusebox->config['route'] as $routePattern => $routeReplacement ) {
				// parse route-replacement
				$arr = explode('&', $routeReplacement);
				$routeReplacement = array();
				foreach ( $arr as $keyEqVal ) {
					list($key, $val) = explode('=', $keyEqVal, 2);
					$routeReplacement[$key] = $val;
				}
				// parse input-url
				$arr = explode('&', self::config('commandVariable').'='.$commandWithQueryString);
				$inputUrl = array();
				foreach ( $arr as $keyEqVal ) {
					list($key, $val) = explode('=', $keyEqVal, 2);
					$inputUrl[$key] = $val;
				}
				// check whether all variables matched
				$routeReplacementKeys = array_keys($routeReplacement);
				$inputUrlKeys = array_keys($inputUrl);
				sort($routeReplacementKeys);
				sort($inputUrlKeys);
				$isAllVariablesMatched = ( $routeReplacementKeys == $inputUrlKeys );
				// check whether command matched
				$commandVar = self::config('commandVariable');
				$isCommandMatched = ( isset($routeReplacement[$commandVar]) and isset($inputUrl[$commandVar]) and preg_match('/'.preg_quote($routeReplacement[$commandVar]).'/', $inputUrl[$commandVar]) );
				// only proceed when all variables matched and command matched
				if ( $isAllVariablesMatched and $isCommandMatched ) {
					// get each back-reference value
					$backRef = array();
					foreach ( $routeReplacement as $key => $val ) {
						// check back-reference format
						if ( substr($val, 0, 1) == '$' and is_numeric(substr($val, 1)) and strpos($val, '.') === false ) {
							$backRef[$val] = $inputUrl[$key];
						}
					}
					// go through each pair of brackets in route-pattern
					// ===> replace it with corresponding back-reference value
					$result = str_replace("\/", '/', $routePattern);
					preg_match_all("/\(.*?\)/", $routePattern, $matches);
					if ( !empty($matches) ) {
						foreach ( $matches[0] as $i => $backRefKey ) {
							if ( isset($backRef['$'.($i+1)]) ) {
								$backRefVal = $backRef['$'.($i+1)];
								$result = preg_replace('/'.preg_quote($backRefKey).'/', $backRefVal, $result, 1);
							}
						}
					}
					// append the base-url
					$result = $fusebox->self.$result;
					$result = str_replace('//', '/', $result);
					return $result;
				} // isAllVariablesMatched-and-isCommandMatched
			} // foreach-fuseboxConfig-route
		} // if-fuseboxConfig-route
		// if no route defined or no match
		// ===> simply prepend self to query-string-path
		return $fusebox->self.$qsPath;
	}


} // class