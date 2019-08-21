<?php
switch ( $fusebox->action ) :


	case 'index':
		$xfa['greeting'] = 'home.greeting';
		ob_start();
		include F::config('appPath').'view/home/index.php';
		$layout['content'] = ob_get_clean();
		include F::config('appPath').'view/home/layout.php';
		break;


	case 'greeting':
		$layout['content'] = '<h1>Hello World!</h1>';
		include F::config('appPath').'view/home/layout.php';
		break;


	default:
		F::pageNotFound();


endswitch;