<?php
switch ( $fusebox->action ) :


	case 'index':
		$xfa['greeting'] = 'site.greeting';
		ob_start();
		include 'app/view/site/index.php';
		$content = ob_get_clean();
		include 'app/view/site/layout.php';
		break;


	case 'greeting':
		$content = '<h1>Hello World!</h1>';
		include 'app/view/site/layout.php';
		break;


	default:
		F::pageNotFound();


endswitch;