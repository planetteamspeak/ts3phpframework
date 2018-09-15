# TeamSpeak 3 PHP Framework

Initially released in January 2010, the TS3 PHP Framework is a powerful, open source, object-oriented framework implemented in PHP 5 and licensed under the GNU General Public License. It’s based on simplicity and a rigorously tested agile codebase. Extend the functionality of your servers with scripts or create powerful web applications to manage all features of your TeamSpeak 3 Server instances.

Tested. Thoroughly. Enterprise-ready and built with agile methods, the TS3 PHP Framework has been unit-tested from the start to ensure that all code remains stable and easy for you to extend, re-test with your extensions, and further maintain.

### Why is TS3 PHP Framework better than other libraries?

The TS3 PHP Framework is a is a modern use-at-will framework that provides individual components to communicate with the TeamSpeak 3 Server.

There are lots of arguments for the TS3 PHP Framework in comparison with other PHP based libraries. It is the most dynamic and feature-rich piece of software in its class and delivers unprecedented performance when used correctly.

### Features

Features of the TS3 PHP Framework include:

* Fully object-oriented PHP 5 and E_STRICT compliant components
* Access to all TeamSpeak 3 Server features via ServerQuery
* Integrated full featured and customizable TSViewer interfaces
* Full support for file transfers to up- and /or download custom icons and other stuff
* Powerful error handling capablities using exceptions and customizable error messages
* Query mechanisms for several official services such as the blacklist and auto-update servers
* Dynamic signal slots for event based scripting

Speed up new development and reduce maintenance costs by using this nifty piece of software!

### Installation

**Requirements**

* PHP - Developed on PHP 7.x, with 7.2.x targeted for testing.
* TeamSpeak Server - v3.4.0 (build >= 1536564584) or higher.

**Often used with...**
* Server - Apache, nginx, php-fpm, CLI
* Database - Standalone (sqlite), Maria DB, PostgreSQL
* Dev - Git, composer, docker, PHPUnit 

Note that the majority of TS3 PHP Framework development and deployment is done on nginx, so there is more community experience and testing performed on Apache than on other web servers.

You can install the TS3 PHP Framework by [manually downloading](https://github.com/ronindesign/ts3phpframework/archive/master.zip) it or using Composer:

```
composer require planetteamspeak/ts3-php-framework
```

The above command will install the latest available release.

If you want to install the TS3 PHP Framework's `master` branch instead (which may not be released / tagged yet), you need to run:

```
composer require planetteamspeak/ts3-php-framework:dev-master
```

### Tests

To run all tests use `php vendor/bin/phpunit`.

### Useful Links

Visit the following pages for more information about the TS3 PHP Framework:

* [Online Documentation](https://docs.planetteamspeak.com/ts3/php/framework/index.html)
* [Changelog](https://docs.planetteamspeak.com/ts3/php/framework/changelog.txt)
* [Changelog (dev)](https://github.com/planetteamspeak/ts3phpframework/blob/master/CHANGELOG)

### Getting Started

#### Connection URI (Options + IPv4 vs IPv6)

Before you can run commands like "get version of server instance" or "update some settings", you need to specify to which instance you want to connect to. This is done using the URI in `TeamSpeak3::factory($uri)`.

The base `$uri` looks always like this:

```php
$uri = "serverquery://username:password@127.0.0.1:10011/";
```

_**Note:** If a piece of your URI contains [special characters](https://github.com/planetteamspeak/ts3phpframework#encoding-uri-special-characters), you will need to encode that piece using [rawurlencode](http://us2.php.net/manual/en/function.rawurlencode.php):_

```php
$uri = "serverquery://" . rawurlencode("test!@#$%^&*()_+") . ":" . rawurlencode('sd5kjKJ2') . "@127.0.0.1:10011/";
```

You also can add some options behind the last `/` in the URI.

To connect to a specific virtual TeamSpeak 3 server using it's `virtualserver_port`:

```php
$uri = "serverquery://username:password@127.0.0.1:10011/?server_port=9987";
```

Additional options can be added using a simple `&` like in HTTP GET URLs:

```php
$uri = "serverquery://username:password@127.0.0.1:10011/?server_port=9987&blocking=0";
```

The list of available options can be found in [TeamSpeak3 > factory](https://docs.planetteamspeak.com/ts3/php/framework/class_team_speak3.html#aa0f699eba7225b3a95a99f80b14e73b3)

The TS3 PHP Framework supports connecting to IPv6 TeamSpeak hosts. An IPv6 address must be written within 
square brackets:

```php
$uri = "serverquery://username:password@[fe80::250:56ff:fe16:1447]:10011/";
```

You can use this simple trick to always get the correct URI based on type of provided IP address `$ip`:

```php
if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
  $uri = "serverquery://username:password@${ip}:10011/";
} elseif(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
  $uri = "serverquery://username:password@[${ip}]:10011/";
} else {
  echo "${ip} is no valid IPv4 or IPv6 address!";
}
```

#### SSH Connections ([TeamSpeak Server](https://www.teamspeak.com) only)

SSH connections can be established using the optional `ssh` parameter:

```php
$uri = "serverquery://username:password@[fe80::250:56ff:fe16:1447]:10022/?ssh=1";
```

#### SSL/TLS Connections ([TeaSpeak Server](https://www.teaspeak.de) only)

Secure ServerQuery connections can be established using the optional `tls` parameter:

```php
$uri = "serverquery://username:password@[fe80::250:56ff:fe16:1447]:10011/?tls=1";
```

#### Custom Protocol Welcome Message and/or MOTD ([TeaSpeak Server](https://www.teaspeak.de) only)

If you're running a [TeaSpeak Server](https://www.teaspeak.de) with a custom MOTD, simply define `CUSTOM_PROTO_IDENT` and/or `CUSTOM_MOTD_PREFIX` before starting the ServerQuery connection:

```php
define("CUSTOM_PROTO_IDENT", "MyTS3");
define("CUSTOM_MOTD_PREFIX", "Hello");
```

#### Encoding URI Special Characters
When passing URI as argument or parameter, some parts may need to contain special characters.
You should use [rawurlencode](http://us2.php.net/manual/en/function.rawurlencode.php) on these parts:

```php
// Bad:
$uri = "serverquery://test!@#$%^&*()_+:sd5kjKJ2@127.0.0.1:10011/";
// Good:
$uri = "serverquery://" . rawurlencode("test!@#$%^&*()_+") . ":sd5kjKJ2@127.0.0.1:10011/";
```

_**Note:** Encode URI components rather than entire URI string. Valid, special characters need to remain unencoded!

Special characters are defined in the newer [RFC 3986](https://tools.ietf.org/html/rfc3986#section-2.3) as any character not in the (ascii, latin) set:

```
ALPHA / DIGIT / "-" / "." / "_" / "~"
```

Additional:
* Most common situation will be encoding only 'username', 'password' pieces.
* Do not encode if not using special characters.
* Path and query components need additional parsing, e.g. we don't want to encode valid '/' and '&'.
* Fragment component may need to be encoded using older `urlencode`.
* [RFC 1738](https://tools.ietf.org/html/rfc1738) - Old RFC used for URI encoding
* [RFC 3986](https://tools.ietf.org/html/rfc3986) - Current URI standard
* [RFC 2396 - Section 3](https://tools.ietf.org/html/rfc2396#section-3.4) - Valid URI syntax (specifically, components of)
* [PHP.net - rawurlencode](http://us2.php.net/manual/en/function.rawurlencode.php)

#### Usual PHP Code (`require` solution)
Usual PHP code means a simple created `file.php`, where you start writing your code like this:

```php
<?php

echo "Hello World!";
```

When you use this solution, you'll probably start using the TS3 PHP  Framework like this:

```php
<?php
// load framework files
require_once("libraries/TeamSpeak3/TeamSpeak3.php");
  
try
{
  // IPv4 connection URI
  $uri = "serverquery://username:password@127.0.0.1:10011/?server_port=9987";

  // connect to above specified server, authenticate and spawn an object for the virtual server on port 9987
  $ts3_VirtualServer = TeamSpeak3::factory($uri);

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

```php
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
      $ts3_VirtualServer = $TS3PHPFramework->factory($uri);
      
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
For further information please visit the documentation (see [Useful Links](#useful-links) above).

### Docker

Setup a local test instance of TeamSpeak3 (amd64, Alpine Linux):
```
docker run --name teamspeak_server -p 9987:9987/udp -p 10011:10011 -p 30033:30033 -e TS3SERVER_LICENSE=accept teamspeak:latest
```
_Add `-d` flag to run in background. Options / Examples: [Docs @ Docker](https://docs.docker.com/samples/library/teamspeak/) | [Hub @ Docker](https://hub.docker.com/_/teamspeak/)_

Use full docker stack to deploy TeamSpeak 3 with Maria DB:
```
// If fresh Docker install, you might need to:
docker swarm init
// Deploy stack with latest TS3, PHP 7.x, Maria DB:
docker stack deploy -c docker-compose.yml teamspeak
```

Additional useful commands:
```
docker logs teamspeak_server # View container logs
docker exec -it teamspeak_server sh # Open shell in container
docker stop teamspeak_server # Stop container
docker rm teamspeak_server # Remove container
docker ps # Show all processes
docker stack ps # Show stack processes
docker stack ls # List stacks
```

_Note: When deploying docker stack, containers are named uniquely:_
```
$ docker stack ps teamspeak
CONTAINER ID        IMAGE               ...   NAMES
f97fe9827b08        mariadb:latest      ...   teamspeak_db.1.xbwjm6jcu5qow44u5i9da2hcv
f9c1538b9875        teamspeak:latest    ...   teamspeak_teamspeak.1.rr3sipmw6dhod92wuhgs3s1rn
```
