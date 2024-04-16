<?php /*
<fusedoc>
	<io>
		<in>
			<string name="error" scope="$fusebox" />
			<structure name="$options" optional="yes">
				<mixed name="~customOption~" comments="specified to {F::error|F::pageNotFound} method explicitly" />
			</structure>
		</in>
		<out>
			<structure name="flash" scope="$arguments" comments="for global layout">
				<string name="type" />
				<string name="icon" />
				<string name="message" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
// do nothing...
if ( empty($fusebox->error) ) {


// just show textual message (when ajax request)
} elseif ( F::ajaxRequest() ) {
	echo $fusebox->error;


// show error with layout (when normal request)
} else {
	$arguments['flash'] = array(
		'type' => ( $fusebox->error == 'page not found' ) ? 'warning' : 'danger',
		'icon' => 'fa fa-exclamation-circle mr-1',
		'message' => $fusebox->error,
	);
	// useful variables
	$controllerLayout = F::appPath("view/{$fusebox->controller}/layout.php");
	$globalLayout = F::appPath('view/global/layout.php');
	// show message with login form
	if ( F::is('account.*,auth.*') and is_file($controllerLayout) ) include $controllerLayout;
	// show message with global layout
	elseif ( is_file($globalLayout) ) include $globalLayout;
	// show message with nothing
	else echo $fusebox->error;


}