<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$boxy->error" />
		</in>
		<out />
	</io>
</fusedoc>
*/

// just show textual message (when ajax request)
// ===> rely on javascript to style the message
if ( F::ajaxRequest() ) {
	die( $boxy->error );

// show error message with global layout (when normal request)
} else {
	// define flash type
	if ( $boxy->error == 'page not found' ) {
		$arguments['flash'] = array('type' => 'warning', 'message' => "<i class='fa fa-exclamation-circle'></i> <strong>".F::fuseaction()."</strong> - {$boxy->error}");
	} else {
		$arguments['flash'] = array('type' => 'danger', 'message' => "<i class='fa fa-exclamation-circle'></i> <strong>".F::fuseaction()."</strong> - {$boxy->error}");
	}
	// layout
	include F::config('appPath').'view/global/layout.php';
}