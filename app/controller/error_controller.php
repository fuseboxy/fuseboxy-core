<?php /*
<fusedoc>
	<io>
		<in>
			<string_or_object name="error" scope="$fuseboxy" type="Error|Exception" optional="yes">
				<string name="getMessage()" />
			</string_or_object>
			<array name="$options" optional="yes">
				<mixed name="*" comments="specified to {F::error} method explicitly" />
			</array>
		</in>
		<out>
			<array name="flash" scope="$layout" comments="for global layout">
				<string name="type" />
				<string name="icon" />
				<string name="message" />
			</array>
		</out>
	</io>
</fusedoc>
*/
// determine closest layout
$layoutPath = class_exists('F') ? F::appPath( isset($fuseboxy->controller) ? "view/{$fuseboxy->controller}/layout.php" : 'view/global/layout.php' ) : false;
$layoutPath = is_file($layoutPath) ? $layoutPath : false;
// determine error message
$errMsg = !empty($fuseboxy->error) ? $fuseboxy->error : false;
$errMsg = ( is_object($errMsg) and in_array(get_class($errMsg), ['Error','Exception']) ) ? $errMsg->getMessage() : $errMsg;
// when no error, do nothing
if ( !$errMsg ) exit();
// when ajax request, display message as text
if ( F::ajaxRequest() ) exit($errMsg);
// otherwise, display message with layout
$layout['flash'] = [ 'type' => 'danger', 'icon' => 'bi bi-exclamation-triangle-fill', 'message' => $errMsg ];
exit( $layoutPath ? ( include $layoutPath ) : print("<pre>{$errMsg}</pre>") );