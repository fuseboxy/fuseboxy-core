FUSERBOXY (1.x)
===============

# Simple is Beautiful - Easy MVC framework for PHP
(inspired by ColdFusion Fusebox Framework)




## What is Fusebox?

Fusebox is an application framework (or methodology) invented by Steve Nelson in the 90's for ColdFusion scripting language (CFML).

Please note that this framework is ***NOT*** a direct port of the original Fusebox framework.

This framework only applies the concept of Fusebox methodology.




## Basic Concept
The main concept of Fusebox is to:
> Centralize all request to *index.php*, and use *command* to determine which files to include




## URL Convention
When **urlRewrite=false**, the URL format of the application looks like:

```
http://{HOST}/index.php?{command}={controller}.{action}
```

When **urlRewrite=true**, the URL format of the application looks like:
```
http://{HOST}/{controller}/{action}
```

If **action** was not specified in command, the framework will resolve it into `index`. So, the followings are the same:
```
http://{HOST}/index.php?fuseaction=news
http://{HOST}/index.php?fuseaction=news.index
```

If both **controller** and **action** are not defined, the framework will use `defaultCommand` which defined in `app/config/fusebox_config.php`. So, the followings are the same:
```
http://{HOST}/index.php
http://{HOST}/index.php?fuseaction={defaultCommand}
```




## Example

To make a thank you page at `http://{HOST}/index.php?fuseaction=site.thank`

1. Create or edit **app/controller/site_controller.php**
2. Under the **switch**, add a new **case** for the action
3. Display content by **include** the view file there
5. Done!

```
switch ( $fusebox->action ) :
	...
	case 'thank':
    	include F::appPath('app/view/site/thank.php');
        break;
	...
endswitch;
```




## formUrl2arguments

To offer a single-point-of-access to all your variables in previous page, the framework automatically creates a variable called `$arguments` which contains both data of **$_POST** and **$_GET** scopes.

Of course, you could still use **$_POST** and **$_GET** to access your variables, because the framework was meant to be unobtrusive.

You could turn-off this option by `formUrl2arguments` at the framework config file.