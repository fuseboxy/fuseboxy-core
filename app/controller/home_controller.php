<?php
switch ( $fusebox->action ) :


	case 'index':
		echo 'Hello World!';
		break;


	default:
		F::pageNotFound();


endswitch;