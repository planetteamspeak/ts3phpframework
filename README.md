# TeamSpeak 3 PHP Framework

Current Version: **1.1.24**

Current Pre-Release: **1.1.28**

Initially released in January 2010, the TS3 PHP Framework is a powerful, open source, object-oriented framework implemented in PHP 5 and licensed under the GNU General Public License. It’s based on simplicity and a rigorously tested agile codebase. Extend the functionality of your servers with scripts or create powerful web applications to manage all features of your TeamSpeak 3 Server instances.

Tested. Thoroughly. Enterprise-ready and built with agile methods, the TS3 PHP Framework has been unit-tested from the start to ensure that all code remains stable and easy for you to extend, re-test with your extensions, and further maintain.

### Why should I use the TS3 PHP Framework rather than other PHP libraries?

The TS3 PHP Framework is a is a modern use-at-will framework that provides individual components to communicate with the TeamSpeak 3 Server.

There are lots of arguments for the TS3 PHP Framework in comparison with other PHP based libraries. It is the most dynamic and feature-rich piece of software in its class and delivers unprecedented performance when used correctly.

### Requirements

The TS3 PHP Framework currently supports PHP 5.2.1 or later, but we strongly recommend the most current release of PHP for critical security and performance enhancements. If you want to create a web application using the TS3 PHP Framework, you need a PHP 5+ interpreter with a web server configured to handle PHP scripts correctly.

Note that the majority of TS3 PHP Framework development and deployment is done on nginx, so there is more community experience and testing performed on Apache than on other web servers.

### Installation
You can either install the TS3 PHP Framework by manually downloading it or using Composer:
```
composer require planetteamspeak/ts3-php-framework
```
The above command will install the latest available release.

If you want to install the TS3 PHP Framework's `master` branch instead (which may not be released / tagged yet), you need to run:
```
composer require planetteamspeak/ts3-php-framework:dev-master
```

### Getting Started
#### Connection URI (Options + IPv4 vs IPv6)
Before you can run commands like "get version of server instance" or "update some settings", you need to specify to which instance you want to connect to. This is done using the URI in `TeamSpeak3::factory("$uri")`.

The base `$uri` looks always like this:
```
$uri = "serverquery://username:password@127.0.0.1:10011/";
```
You also can add some options behind the last `/`. Tell the URI, that you want to connect to a specific virtual TeamSpeak 3 server using it's `virtualserver_port`:
```
$uri = "serverquery://username:password@127.0.0.1:10011/?server_port=9987";
```
Additional options can be added using a simple `&` like in GET-URLs:
```
$uri = "serverquery://username:password@127.0.0.1:10011/?server_port=9987&blocking=0";
```
The list of available options can be found in the documentation: [TeamSpeak3 > factory](https://docs.planetteamspeak.com/ts3/php/framework/class_team_speak3.html#aa0f699eba7225b3a95a99f80b14e73b3)

The TS3 PHP Framework does also support connections to IPv6 TeamSpeak hosts. An IPv6 address must be written within 
square brackets:
```
$uri = "serverquery://username:password@[fe80::250:56ff:fe16:1447]:10011/";
```

In your PHP code, you can use this simple trick to always get the correct URI based on the type of your provided IP address `$ip`:
```
if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	$uri = "serverquery://username:password@${ip}:10011/";
} elseif(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
	$uri = "serverquery://username:password@[${ip}]:10011/";
} else {
	echo "${ip} is no valid IPv4 or IPv6 address!";
}
```

#### Usual PHP Code (`require` solution)
Usual PHP code means a simple created `file.php`, where you start writing your code like this:
```
<?php
	echo "Hello World!";
```
When you use this solution, you'll probably start using the TS3 PHP  Framework like this:
```
<?php
	// load framework files
	require_once("libraries/TeamSpeak3/TeamSpeak3.php");
	
	try
	{
		// IPv4 connection URI
		$uri = "serverquery://username:password@127.0.0.1:10011/?server_port=9987";

		// connect to above specified server, authenticate and spawn an object for the virtual server on port 9987
		$ts3_VirtualServer = TeamSpeak3::factory("$uri");

		// spawn an object for the channel using a specified name
		$ts3_Channel = $ts3_VirtualServer->channelGetByName("I do not exist");
	}
	catch(TeamSpeak3_Exception $e)
	{
		// print the error message returned by the server
		echo "Error " . $e->getCode() . ": " . $e->getMessage();
	}
```

#### PHP Code in [MVC](https://en.wikipedia.org/wiki/Model–view–controller) (`use` solution)
When you use a MVC based software like Symfony, CakePHP, Laravel or something similar, you'll probably  use something like this:
```
<?php

use TeamSpeak3;
use TeamSpeak3_Exception;

class TeamspeakController extends Controller
{
	public function doSomething()
    {
		try
		{
			// IPv4 connection URI
			$uri = "serverquery://username:password@127.0.0.1:10011/?server_port=9987";
			
			// Create new object of TS3 PHP Framework class
			$TS3PHPFramework = new TeamSpeak3();
			
			// connect to above specified server, authenticate and spawn an object for the virtual server on port 9987
			$ts3_VirtualServer = $TS3PHPFramework->factory("$uri");
			
			// spawn an object for the channel using a specified name
			$ts3_Channel = $ts3_VirtualServer->channelGetByName("I do not exist");
		}
		catch(TeamSpeak3_Exception $e)
		{
			// print the error message returned by the server
			return "Error " . $e->getCode() . ": " . $e->getMessage();
		}
	}
}
```
For further information please visit the documentation (see [Useful Links](#useful-links)).

### Features

Features of the TS3 PHP Framework include:

* Fully object-oriented PHP 5 and E_STRICT compliant components
* Access to all TeamSpeak 3 Server features via ServerQuery
* Integrated full featured and customizable TSViewer interfaces
* Full support for file transfers to up- and /or download custom icons and other stuff
* Powerful error handling capablities using exceptions and customizable error messages
* Query mechanisms for several official services such as the blacklist and auto-update servers
* Dynamic signal slots for event based scripting

### Tests

To run all tests use `php vendor/phpunit/phpunit`.

### Useful Links

Visit the following pages for more information about the TS3 PHP Framework:

* [Online Documentation](https://docs.planetteamspeak.com/ts3/php/framework/index.html)
* [Changelog](https://docs.planetteamspeak.com/ts3/php/framework/changelog.txt)

Speed up new development and reduce maintenance costs by using this nifty piece of software!

