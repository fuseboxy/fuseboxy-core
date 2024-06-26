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
		// when no content
		// ===> simply return nothing
		if ( empty($flash['icon']) and empty($flash['heading']) and empty($flash['message']) and empty($flash['remark']) ) return null;
		// when has any content
		// ===> capture output & return
		ob_start();
		?><div id="<?php echo $flash['id'] ?? ''; ?>" class="alert alert-<?php echo $flash['type']; ?>"><?php
			if ( !empty($flash['icon']) ) :
				?><i class="<?php echo $flash['icon']; ?>">&ensp;</i><?php
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
		// if nothing specified
		// ===> simply return config
		if ( empty($relPath) ) return self::config('appPath');
		// look into app path
		$appPathFile = self::config('appPath').$relPath;
		if ( file_exists($appPathFile) ) return $appPathFile;
		// if file not found in app path
		// ===> look through each fuseboxy module under vendor path
		if ( self::config('vendorPath') ) {
			$glob = glob(self::config('vendorPath').'fuseboxy/*/app/'.$relPath);
			if ( !empty($glob[0]) ) return $glob[0];
		}
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
				<string name="~return~" example="home.index|home|index|.." />
			</out>
		</io>
	</fusedoc>
	*/
	public static function command($key=null) {
		global $fusebox;
		// when command not found in config/url/form
		if ( empty($fusebox->controller) and empty($fusebox->action) ) return null;
		// get full command
		if ( empty($key) ) return $fusebox->controller.'.'.$fusebox->action;
		// get controller only
		if ( strtolower($key) == 'controller' ) return $fusebox->controller;
		// get action only
		if ( strtolower($key) == 'action' ) return $fusebox->action;
		// otherwise...
		return null;
	}




	/**
	<fusedoc>
		<description>
			getter & setter of framework config
			===> use reserved word {{undefined}} as default
			===> so that user can set config to null
		</description>
		<io>
			<in>
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<mixed name="*" />
				</structure>
				<!-- parameter -->
				<string name="$key" optional="yes" default="~null~" example="defaultCommand|db|smtp|.." />
				<mixed name="$val" optional="yes" default="{{undefined}}" />
			</in>
			<out>
				<mixed name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function config($key=null, $val='{{undefined}}') {
		global $fusebox;
		// getter (all)
		if ( empty($key) ) return $fusebox->config;
		// getter (specific)
		if ( $val == '{{undefined}}' ) return $fusebox->config[$key] ?? null;
		// setter
		$fusebox->config[$key] = $val;
		return $val;
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
				<string name="$msg" optional="yes" default="Error" />
				<boolean name="$condition" optional="yes" default="true" />
				<structure name="$options" optional="yes">
					<string name="headerString" optional="yes" default="HTTP/1.0 403 Forbidden" />
					<number name="errorCode" optional="yes" default="~Framework::FUSEBOX_ERROR~" />
					<mixed name="~customOption~" comments="more custom options available for error-controller" />
				</structure>
			</in>
			<out>
				<string name="$fusebox->error" comments="for error-controller" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function error($msg='Error', $condition=true, $options=[]) {
		global $fusebox;
		// check whether to proceed
		if ( !$condition ) return null;
		// default options
		$options['headerString'] = $options['headerString'] ?? 'HTTP/1.0 403 Forbidden';
		$options['errorCode'] = $options['errorCode'] ?? Framework::FUSEBOX_ERROR;
		// send http header to browser (when necessary)
		if ( !headers_sent() ) header($options['headerString']);
		// set error message to api object
		// ===> make it available to error-controller
		$fusebox->error = $msg;
		// when unit test
		// ===> throw exception
		// ===> (do not abort operation)
		if ( Framework::$unitTest ) throw new Exception('['.self::command().'] '.$fusebox->error, $options['errorCode']);
		// when has error-controller
		// ===> display/handle the error by error-controller
		// ===> (abort operation afterward)
		if ( self::config('errorController') ) exit( include self::config('errorController') );
		// otherwise
		// ===> simply display error as text
		// ===> (abort operation afterward)
		exit($fusebox->error);
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
		$arr = explode('&', $commandWithQueryString, 2);
		$command = $arr[0] ?? '';
		$queryString = $arr[1] ?? '';
		// parse new command
		$command = self::parseCommand($command);
		$fusebox->controller = $command['controller'];
		$fusebox->action = $command['action'];
		$controllerPath = self::config('appPath').'/controller/'.$fusebox->controller.'_controller.php';
		// put query string variables into arguments & url scope
		parse_str($queryString, $queryString);
		$arguments = array_merge($queryString, $arguments);
		$originalGetScope = $_GET;
		$_GET = array_merge($_GET, $queryString);
		// when controller found
		// ===> load controller to invoke command
		if ( file_exists($controllerPath) ) include $controllerPath;
		// trim queue afterward
		// ===> regardless whether successfully run or not
		// ===> restore to original command (previous command in queue)
		$originalCommand = self::parseCommand(array_pop($fusebox->invokeQueue));
		$fusebox->controller = $originalCommand['controller'];
		$fusebox->action = $originalCommand['action'];
		$_GET = $originalGetScope;
		// when controller not found
		// ===> command not run indeed
		// ===> throw error
		self::pageNotFound(!file_exists($controllerPath));
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
			$isControllerMatched = in_array($commandPattern['controller'], [ '*', $fusebox->controller ]);
			$isActionMatched = in_array($commandPattern['action'], [ '*', $fusebox->action ]);
			if ( $isControllerMatched and $isActionMatched ) return true;
		}
		// no match...
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
			===> look for [page not found] message at error-controller
			===> then load 404 custom page
		</description>
		<io>
			<in>
				<boolean name="$condition" optional="yes" default="true" />
				<structure name="$options" optional="yes" default="~emptyArray~" />
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function pageNotFound($condition=true, $options=[]) {
		self::error('Page not found', $condition, array_merge($options, [
			'headerString' => 'HTTP/1.0 404 Not Found',
			'errorCode' => Framework::FUSEBOX_PAGE_NOT_FOUND,
		]));
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
		// both are false when command is empty
		if ( empty($command) ) return array('controller' => null, 'action' => null);
		// split command by delimiter (when not empty)
		$arr = explode('.', $command, 2);
		return array(
			'controller' => $arr[0],
			'action' => !empty($arr[1]) ? $arr[1] : 'index'
		);
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
		// check whether to proceed
		if ( !$condition ) return null;
		// convert command to url
		$url = self::url($command);
		// when no delay
		// ===> must use {Location} to ensure ajax-request compatibility
		// ===> when delay specified
		// ===> very likely it is not invoked by ajax-request
		// ===> simply use {Refresh} to perform the redirection
		$headerString = empty($delay) ? "Location:{$url}" : "Refresh:{$delay};url={$url}";
		// when unit test
		// ===> throw header-string as exception
		// ===> (do not abort operation)
		if ( Framework::$unitTest ) throw new Exception($headerString, Framework::FUSEBOX_REDIRECT);
		// when no header sent to client yet
		// ===> trigger redirect at server-side
		// ===> (abort operation afterward)
		if ( !headers_sent() ) exit( header($headerString) );
		// otherwise
		// ===> trigger redirect at client-side
		// ===> (abort operation afterward)
		exit("<script>window.setTimeout(function(){document.location.href='{$url}';},{$delay}*1000);</script>");
	}




	/**
	<fusedoc>
		<description>
			determine the protocol which client browser is using
		</description>
		<io>
			<in>
				<structure name="$_SERVER">
					<string name="HTTP_X_FORWARDED_PROTO" optional="yes" />
					<string name="HTTPS" optional="yes" />
					<string name="REQUEST_SCHEME" optional="yes" />
					<string name="HTTP_POST" optional="yes" />
					<string name="SHELL" optional="yes" />
					<string name="SESSIONNAME" optional="yes" />
				</structure>
				<structure name="$_GET" optional="yes" />
				<structure name="$_POST" optional="yes" />
			</in>
			<out>
				<string name="~return~" value="https|http|cli" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function requestScheme() {
		if ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ) return $_SERVER['HTTP_X_FORWARDED_PROTO'];
		if ( isset($_SERVER['HTTPS']) and in_array(strtolower((string)$_SERVER['HTTPS']), ['on','1']) ) return 'https';
		if ( isset($_SERVER['REQUEST_SCHEME']) ) return strtolower($_SERVER['REQUEST_SCHEME']);
		if ( isset($_SERVER['HTTP_HOST']) or isset($_GET) or isset($_POST) ) return 'http';
		if ( isset($_SERVER['SHELL']) or ( isset($_SERVER['SESSIONNAME']) and strtolower($_SERVER['SESSIONNAME']) == 'console' ) ) return 'cli';
		return null;
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
				<!-- parameters -->
				<string name="$unit" default="ms" comments="ms|s" />
				<boolean name="$showUnit" default="false" />
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function runtime($unit=null, $showUnit=false) {
		// set default & fix format
		$unit = strtolower($unit ?? 'ms');
		// check unit
		if ( !in_array($unit, ['ms','s']) ) throw new Exception('Invalid unit for runtime', Framework::FUSEBOX_ERROR);
		// not started yet
		if ( !isset(Framework::$startTick) ) return null;
		// calculation
		$et = round(microtime(true)*1000-Framework::$startTick);
		if ( $unit == 's' ) $et = $et/1000;
		// done!
		return $et.( $showUnit ? $unit : '' );
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
				<!-- framework api -->
				<string name="self" scope="$fusebox" />
				<string name="myself" scope="$fusebox" />
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
				<string name="~returnNormalURL~" oncondition="when {urlRewrite=false}" example="/my/site/index.php?fuseaction=product.view&id=10" />
				<string name="~returnBeautifyURL~" oncondition="when {urlRewrite=true}" example="/my/site/product/view/id=10" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function url($commandWithQueryString=null) {
		global $fusebox;
		// when no command defined
		// ===> simply return self (no matter url-rewrite or not)
		if ( empty($commandWithQueryString) ) return $fusebox->self;
		// when external url
		// ===> simply return without any transformation
		if ( false
			or $commandWithQueryString[0] == '/'
			or substr(strtolower(trim($commandWithQueryString)), 0, 7) == 'http://'
			or substr(strtolower(trim($commandWithQueryString)), 0, 8) == 'https://'
		) return $commandWithQueryString;
		// when rewrite not enabled
		// ===> simply return ugly url (self + commandVariable + command + queryString)
		if ( !F::config('urlRewrite') ) return $fusebox->myself.$commandWithQueryString;
		// done!
		return self::url__beautifyByRouteMatched($commandWithQueryString) ?? self::url__beautifyBySimpleRules($commandWithQueryString);
	}




	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
				<!-- framework api -->
				<string name="self" scope="$fusebox" />
				<!-- config -->
				<structure name="config" scope="$fusebox">
					<boolean name="urlRewrite" />
				</structure>
				<!-- parameter -->
				<string name="$commandWithQueryString" optional="yes" example="product.view&id=10" />
			</in>
			<out>
				<string name="~return~" oncondition="when {urlRewrite=true}" example="/my/site/product/view/id=10" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function url__beautifyBySimpleRules($commandWithQueryString) {
		global $fusebox;
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
		// ===> e.g. convert <a=1&b=2&c=3> to </a=1/b=2/c=3>
		$qsPath = implode('/', $qs);
		// remove multi-slashes
		$qsPath = preg_replace('~^/+|/+$|/(?=/)~', '', $qsPath);
		// trim leading and trailing slash
		$qsPath = trim($qsPath, '/');
		// done!
		return $fusebox->self.$qsPath;
	}




	/**
	<fusedoc>
		<description>
			further beautify the url according to route pattern
			===> e.g. convert <article&type=abc&id=1> to <article/abc/1> instead of <article/type=abc/id=1>
		</description>
		<io>
			<in>
				<!-- framework api -->
				<string name="self" scope="$fusebox" />
				<!-- config -->
				<structure name="config" scope="$fusebox">
					<boolean name="urlRewrite" />
					<string name="commandVariable" />
					<structure name="route">
						<string name="~pattern~" value="~regex~" />
					</structure>
				</structure>
				<!-- parameter -->
				<string name="$commandWithQueryString" optional="yes" example="product.view&id=10" />
			</in>
			<out>
				<string name="~return~" oncondition="when {urlRewrite=true}" example="/my/site/product/view/id=10" />
			</out>
		</io>
	</fusedoc>
	*/

	public static function url__beautifyByRouteMatched($commandWithQueryString) {
		global $fusebox;
		// go through & compare against each pattern
		// ===> return the first match only
		foreach ( self::config('route') ?? [] as $routePattern => $routeReplacement ) {
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
			$isAllVarsMatched = ( $routeReplacementKeys == $inputUrlKeys );
			// check whether command matched
			$commandVar = self::config('commandVariable');
			$isCommandMatched = ( isset($routeReplacement[$commandVar]) and isset($inputUrl[$commandVar]) and preg_match('/'.preg_quote($routeReplacement[$commandVar]).'/', $inputUrl[$commandVar]) );
			// only proceed when all variables matched and command matched
			if ( $isAllVarsMatched and $isCommandMatched ) {
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
			} // isAllVarsMatched-and-isCommandMatched
		} // foreach-route
		// no match...
		return null;
	}


} // class