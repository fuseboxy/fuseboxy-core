<?php
switch( $fusebox->action ) :


	case 'index':
		echo "This is unit test controller\n";
		break;


	case 'anotherPage':
		echo "This is another page\n";
		break;


	case 'simpleInvoke':
		echo "This is simple invoke (invokeQueueLength=".sizeof($fusebox->invokeQueue).")\n";
		break;


	case 'nestedInvoke':
		echo "This is nested invoke (invokeQueueLength=".sizeof($fusebox->invokeQueue).")\n";
		F::invoke('unitTest.simpleInvoke');
		break;


	default:
		F::pageNotFound();


endswitch;