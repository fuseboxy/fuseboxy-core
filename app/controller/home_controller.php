<?php
switch ( $fusebox->action ) :


	case 'index':
		$layout['content'] = 'Hello World!';
		if ( is_file( F::appPath('view/global/layout.php') ) ) {
			include F::appPath('view/global/layout.php');
		} else echo $layout['content'];
		break;


	default:
		F::pageNotFound();


endswitch;