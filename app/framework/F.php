<?php
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
				<string_or_array name="$flash">
					<string name="type" optional="yes" default="primary" comments="primary|secondary|success|info|warning|danger|light|dark" />
					<string name="icon" optional="yes" />
					<string name="heading" optional="yes" />
					<string name="message" optional="yes" />
					<string name="remark" optional="yes" />
					<string name="remarkClass" optional="yes" />
				</string_or_array>
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function alert($flash='alert', $condition=true) {
		// check whether to show message
		if ( !$condition ) return null;
		// fix param & set default
		if ( !empty($flash) ) :
			if ( !is_array($flash) ) $flash = array('message' => $flash);
			if ( empty($flash['type']) ) $flash['type'] = 'primary';
		endif;
		// when no content
		// ===> simply display nothing
		if ( empty($flash['icon']) and empty($flash['heading']) and empty($flash['message']) and empty($flash['remark']) ) return;
		// when has any content
		// ===> capture output & return
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
				?><small class="<?php echo $flash['remarkClass'] ?? 'text-secondary'; ?>"><?php echo $flash['remark']; ?></small><?php
			endif;
		?></div><?php
	}




	/*
	<fusedoc>
		<description>
			obtain alert message output
		</description>
		<io>
			<in>
				<string_or_array name="$flash">
					<string name="type" optional="yes" default="primary" comments="primary|secondary|success|info|warning|danger|light|dark" />
					<string name="id" optional="yes" comments="div[id]" />
					<string name="icon" optional="yes" />
					<string name="heading" optional="yes" />
					<string name="message" optional="yes" />
					<string name="remark" optional="yes" />
				</string_or_array>
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function alertOutput($flash='alert', $condition=true) {
		ob_start();
		self::alert($flash, $condition);
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
				<array name="config" scope="$fuseboxy">
					<string name="appPath" example="/path/to/my/site/app/" />
				</array>
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
		if ( is_file($appPathFile) ) return $appPathFile;
		// if file not found in app path
		// ===> look through each fuseboxy module under vendor path
		if ( self::config('vendorPath') ) :
			$glob = glob(self::config('vendorPath').'fuseboxy/*/app/'.$relPath);
			if ( !empty($glob[0]) ) return $glob[0];
		endif;
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
				<string name="controller" example="home" scope="$fuseboxy" />
				<string name="action" example="index" scope="$fuseboxy" />
				<!-- framework config -->
				<array name="config" scope="$fuseboxy">
					<string name="defaultCommand" example="home.index" />
				</array>
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
		global $fuseboxy;
		// when command not found in config/url/form
		if ( empty($fuseboxy->controller) and empty($fuseboxy->action) ) return null;
		// get full command
		if ( empty($key) ) return $fuseboxy->controller.'.'.$fuseboxy->action;
		// get controller only
		if ( strtolower($key) == 'controller' ) return $fuseboxy->controller;
		// get action only
		if ( strtolower($key) == 'action' ) return $fuseboxy->action;
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
				<array name="config" scope="$fuseboxy">
					<mixed name="*" />
				</array>
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
		global $fuseboxy;
		// when {key} not specified
		// ===> getter (all)
		if ( empty($key) ) return $fuseboxy->config;
		// when {key} specified but {val} not specified
		// ===> getter (specific)
		if ( $val == '{{undefined}}' ) return $fuseboxy->config[$key] ?? null;
		// when both {key & val} specified
		// ===> setter
		$fuseboxy->config[$key] = $val;
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
				<!-- framework -->
				<boolean name="$abortOnError" scope="Framework" />
				<!-- config -->
				<array name="config" scope="$fuseboxy">
					<string_or_boolean name="errorController" />
				</array>
				<!-- parameters -->
				<string name="$message" />
				<boolean name="$condition" optional="yes" default="true" />
				<array name="$options" optional="yes">
					<string name="headerString" optional="yes" default="HTTP/1.0 403 Forbidden" />
					<number name="errorCode" optional="yes" default="~Framework::FUSEBOXY_ERROR~" />
					<mixed name="~customOption~" comments="more custom options available for error-controller" />
				</array>
			</in>
			<out>
				<string name="$fuseboxy->error" comments="for error-controller" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function error($message, $condition=true, $options=[]) {
		global $fuseboxy;
		// check whether to proceed
		if ( !$condition ) return null;
		// default options
		$options['headerString'] = $options['headerString'] ?? 'HTTP/1.0 403 Forbidden';
		$options['errorCode'] = $options['errorCode'] ?? Framework::FUSEBOXY_ERROR;
		// send http header to browser (when necessary)
		if ( !headers_sent() ) header($options['headerString']);
		// set error message to api object
		// ===> make it available to error-controller
		$fuseboxy->error = $message;
		// determine error-controller path
		if ( is_string(self::config('errorController')) ) :
			$errorController = self::config('errorController');
		elseif ( self::config('errorController') ) :
			$errorController = self::appPath('controller/error_controller.php');
		else :
			$errorController = false;
		endif;
		// when error-controller specified
		// ===> display by error-controller
		// ===> otherwise, simply display error as text
		if ( $errorController and is_file($errorController) ) :
			include $errorController;
		elseif ( is_object($fuseboxy->error) and in_array(get_class($fuseboxy->error), ['Error','Exception'])) :
			echo $fuseboxy->error->getMessage();
		else :
			echo $fuseboxy->error;
		endif;
		// abort operation afterward (by default)
		if ( Framework::$abortOnError ) exit();
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
				<array name="$arguments" default="~emptyArray~" />
			</in>
			<out>
				<!-- manipulated api object -->
				<string name="controller" scope="$fuseboxy" />
				<string name="action" scope="$fuseboxy" />
				<!-- command stack -->
				<array name="invokeQueue" scope="$fuseboxy">
					<string name="+" comments="command" example="product.view|product.recommend|.." />
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function invoke($commandWithQueryString, $arguments=[]) {
		global $fuseboxy;
		// create stack container to keep track of command-in-run
		// ===> first item of invoke queue should be original command
		// ===> second item onward will be the command(s) called by F::invoke()
		if ( !isset($fuseboxy->invokeQueue) ) $fuseboxy->invokeQueue = array();
		$fuseboxy->invokeQueue[] = "{$fuseboxy->controller}.{$fuseboxy->action}";
		// split new command & query-string (if any)
		$commandWithQueryString = str_replace('?', '&', $commandWithQueryString);
		$arr = explode('&', $commandWithQueryString, 2);
		$command = $arr[0] ?? '';
		$queryString = $arr[1] ?? '';
		// parse new command
		$command = self::parseCommand($command);
		$fuseboxy->controller = $command['controller'];
		$fuseboxy->action = $command['action'];
		$controllerPath = self::config('appPath').'/controller/'.$fuseboxy->controller.'_controller.php';
		// put query string variables into arguments & url scope
		parse_str($queryString, $queryString);
		$originalGetScope = $_GET;
		$_GET = array_merge($_GET, $queryString);
		// when controller found
		// ===> load controller to invoke command
		if ( is_file($controllerPath) ) include $controllerPath;
		// trim queue afterward
		// ===> regardless whether successfully run or not
		// ===> restore to original command (previous command in queue)
		$originalCommand = self::parseCommand(array_pop($fuseboxy->invokeQueue));
		$fuseboxy->controller = $originalCommand['controller'];
		$fuseboxy->action = $originalCommand['action'];
		$_GET = $originalGetScope;
		// when controller not found
		// ===> command not run indeed
		// ===> throw error
		self::pageNotFound( !is_file($controllerPath) );
	}




	/**
	<fusedoc>
		<description>
			obtain output when invoking specific command
		</description>
		<io>
			<in>
				<string name="$commandWithQueryString" example="home.news&id=100" />
				<array name="$arguments" default="~emptyArray~" />
			</in>
			<out>
				<string name="~return~" format="html" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function invokeOutput($commandWithQueryString, $arguments=[]) {
		ob_start();
		self::invoke($commandWithQueryString, $arguments);
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
				<array name="invokeQueue" scope="$fuseboxy" optional="yes" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function invokeRequest() {
		global $fuseboxy;
		return !empty($fuseboxy->invokeQueue);
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
		global $fuseboxy;
		// allow checking multiple command-patterns
		if ( !is_array($commandPatternList) ) $commandPatternList = explode(',', $commandPatternList);
		// check each user-provided command-pattern
		foreach ( $commandPatternList as $commandPattern ) :
			$commandPattern = self::parseCommand($commandPattern);
			// consider match when either one is ok
			$isControllerMatched = in_array($commandPattern['controller'], [ '*', $fuseboxy->controller ]);
			$isActionMatched = in_array($commandPattern['action'], [ '*', $fuseboxy->action ]);
			if ( $isControllerMatched and $isActionMatched ) return true;
		endforeach;
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
				<array name="$options" optional="yes" default="~emptyArray~" />
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function pageNotFound($condition=true, $options=[]) {
		self::error('Page not found', $condition, array_merge($options, [
			'headerString' => 'HTTP/1.0 404 Not Found',
			'errorCode' => Framework::FUSEBOXY_PAGE_NOT_FOUND,
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
				<array name="~return~">
					<string name="controller" example="home" />
					<string name="action" example="index" />
				</array>
			</out>
		</io>
	</fusedoc>
	*/
	public static function parseCommand($command) {
		// both are false when command is empty
		if ( empty($command) ) return [ 'controller' => null, 'action' => null ];
		// split command by delimiter (when not empty)
		return [
			'controller' => str_contains($command, '.') ? explode('.', $command, 2)[0] : $command,
			'action' => str_contains($command, '.') ? explode('.', $command, 2)[1] : 'index',
		];
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
				<!-- framework -->
				<boolean name="$abortOnRedirect" scope="Framework" />
				<!-- parameters -->
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
		// when no header sent to client yet
		// ===> trigger redirect at server-side
		// ===> otherwise, trigger redirect at client-side
		// ===> (abort operation afterward)
		if ( !headers_sent() ) header($headerString);
		else echo "<script>window.setTimeout(function(){document.location.href='{$url}';},{$delay}*1000);</script>";
		if ( Framework::$abortOnRedirect ) exit();
	}




	/**
	<fusedoc>
		<description>
			determine the protocol which client browser is using
		</description>
		<io>
			<in>
				<array name="$_SERVER">
					<string name="HTTP_X_FORWARDED_PROTO" optional="yes" />
					<string name="HTTPS" optional="yes" />
					<string name="REQUEST_SCHEME" optional="yes" />
					<string name="HTTP_POST" optional="yes" />
					<string name="SHELL" optional="yes" />
					<string name="SESSIONNAME" optional="yes" />
				</array>
				<array name="$_GET" optional="yes" />
				<array name="$_POST" optional="yes" />
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
		if ( !in_array($unit, ['ms','s']) ) throw new Exception('Invalid unit for runtime', Framework::FUSEBOXY_ERROR);
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
				<string name="self" scope="$fuseboxy" />
				<string name="myself" scope="$fuseboxy" />
				<!-- config -->
				<array name="config" scope="$fuseboxy">
					<string name="commandVariable" example="fuseaction" />
					<boolean name="urlRewrite" />
					<array name="route" comments="url-rewrite patterns" />
				</array>
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
		global $fuseboxy;
		// when no command defined
		// ===> simply return self (no matter url-rewrite or not)
		if ( empty($commandWithQueryString) ) return $fuseboxy->self;
		// when external url
		// ===> simply return without any transformation
		if ( false
			or $commandWithQueryString[0] == '/'
			or substr(strtolower(trim($commandWithQueryString)), 0, 7) == 'http://'
			or substr(strtolower(trim($commandWithQueryString)), 0, 8) == 'https://'
		) return $commandWithQueryString;
		// when rewrite not enabled
		// ===> simply return ugly url (self + commandVariable + command + queryString)
		if ( !F::config('urlRewrite') ) return $fuseboxy->myself.$commandWithQueryString;
		// done!
		return self::url__beautifyByRouteMatched($commandWithQueryString) ?? self::url__beautifyBySimpleRules($commandWithQueryString);
	}




	/**
	<fusedoc>
		<description>
			beautify normal url with query string
		</description>
		<io>
			<in>
				<!-- framework api -->
				<string name="self" scope="$fuseboxy" />
				<!-- config -->
				<array name="config" scope="$fuseboxy">
					<boolean name="urlRewrite" />
				</array>
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
		global $fuseboxy;
		// rewrite (with or without query-string)
		// ===> transform to beauty-url
		// ===> check route as well
		$qs = explode('&', $commandWithQueryString);
		// first element has command-delimiter and no equal-sign
		// ===> first element is command
		// ===> replace first occurrence of delimiter with slash (if any)
		if ( strpos($qs[0], '=') === false ) :
			$qs[0] = explode('.', $qs[0], 2);
			$qs[0] = implode('/', $qs[0]);
		endif;
		// turn query-string into path-like-query-string
		// ===> e.g. convert <a=1&b=2&c=3> to </a=1/b=2/c=3>
		$qsPath = implode('/', $qs);
		// remove multi-slashes
		$qsPath = preg_replace('~^/+|/+$|/(?=/)~', '', $qsPath);
		// trim leading and trailing slash
		$qsPath = trim($qsPath, '/');
		// done!
		return $fuseboxy->self.$qsPath;
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
				<string name="self" scope="$fuseboxy" />
				<!-- config -->
				<array name="config" scope="$fuseboxy">
					<boolean name="urlRewrite" />
					<string name="commandVariable" />
					<array name="route">
						<string name="~pattern~" value="~regex~" />
					</array>
				</array>
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
		global $fuseboxy;
		// go through & compare against each pattern
		// ===> return the first match only
		foreach ( self::config('route') ?? [] as $routePattern => $routeReplacement ) :
			// parse route-replacement
			$arr = explode('&', $routeReplacement);
			$routeReplacement = array();
			foreach ( $arr as $keyEqVal ) :
				list($key, $val) = explode('=', $keyEqVal, 2);
				$routeReplacement[$key] = $val;
			endforeach;
			// parse input-url
			$arr = explode('&', self::config('commandVariable').'='.$commandWithQueryString);
			$inputUrl = array();
			foreach ( $arr as $keyEqVal ) :
				list($key, $val) = explode('=', $keyEqVal, 2);
				$inputUrl[$key] = $val;
			endforeach;
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
			if ( $isAllVarsMatched and $isCommandMatched ) :
				// get each back-reference value
				$backRef = array();
				foreach ( $routeReplacement as $key => $val ) :
					// check back-reference format
					if ( substr($val, 0, 1) == '$' and is_numeric(substr($val, 1)) and strpos($val, '.') === false ) :
						$backRef[$val] = $inputUrl[$key];
					endif;
				endforeach;
				// go through each pair of brackets in route-pattern
				// ===> replace it with corresponding back-reference value
				$result = str_replace("\/", '/', $routePattern);
				preg_match_all("/\(.*?\)/", $routePattern, $matches);
				if ( !empty($matches) ) :
					foreach ( $matches[0] as $i => $backRefKey ) :
						if ( isset($backRef['$'.($i+1)]) ) :
							$backRefVal = $backRef['$'.($i+1)];
							$result = preg_replace('/'.preg_quote($backRefKey).'/', $backRefVal, $result, 1);
						endif;
					endforeach;
				endif;
				// append the base-url
				$result = $fuseboxy->self.$result;
				$result = str_replace('//', '/', $result);
				return $result;
			endif; // isAllVarsMatched-and-isCommandMatched
		endforeach; // foreach-route
		// no match...
		return null;
	}


} // class