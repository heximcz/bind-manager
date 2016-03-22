# Manager for Bind 9 DNS Resolver 


## Overview

Periodical update of the root zone for Bind DNS resolver.

**NEW:** Support for Bind 9 Statistics and Zabbix monitoring.

**NEW:** Support for Bind 9.10.x JSON Statistics.

## Prerequisites

bind, wget, php > 5.6.x (with enable shell_exec and exec function in php.ini)

## How to install Bind Manager

 - Connet via SSH to your web server
 - ```cd /opt/```
 - ```git clone https://github.com/heximcz/bind-manager.git```
 - ```cd /opt/bind-manager/```
 - ```git tag -l```
 - ```git checkout tags/<last tag name of stable version>```
 - ```cp ./config.default.yml ./config.yml```
 - ```mkdir -p /var/log/bind-manager/```
 -  **change your preferences in the config.yml file**

## How to update Bind Manager

 - ```cd /opt/bind-manager/```
 - ```git pull```
 - ```git tag -l```
 - ```git checkout tags/<last tag name of stable version>```
 - How to finding the tag is that checked out? Simply.
 - ```git describe --tags```

## Zabbix statistics

* Allow statistics in named.conf [Bind manual](https://ftp.isc.org/isc/bind9/9.10.4b2/doc/arm/Bv9ARM.ch06.html#statschannels)

```
# Allow statistics
statistics-channels {
   inet 127.0.0.1 port 8053;
};
/etc/init.d/bind9 reload
```

* Configure Zabbix Agent

```
cp /opt/bind-manager/zabbix/bind-resolver.conf /etc/zabbix/zabbix_agentd.d/
/etc/init.d/zabbix-agent restart
```

* Download the Bind 9 template for Zabbix 3.x: [Zabbix templates](https://github.com/heximcz/bind-manager/tree/master/zabbix)

```

Template-Bind-9-Statistics-JX3.xml - Support for XML v3 an JSON
Template-Bind-9-Statistics-X2.xml - Support for XML v2

```

* Import the template to your Zabbix monitoring.
* Configure section 'system' in the config.yml 

```
...
    # bind statistics url - howto: https://ftp.isc.org/isc/bind9/9.10.4b2/doc/arm/Bv9ARM.ch06.html#statschannels
    # (allowed path after port number is: /xml/v2, /xml/v3, /json, default is no path )
    statsurl: "http://127.0.0.1:8053"
    # directory for store files with a statistics values
    statsdir: "/var/cache/bind/named-stats/"
...
```

## Example Usage

print help:

```php ./bind-manager.php```

or

``` shell
user@server:/opt/bind-manager# php ./bind-manager.php bind:sys -h

Usage:
  bind:sys [<action>]

Arguments:
  action                update | restart | statistics [default: "update"]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 ...

Help:
 Update db.root, checks and reload actions.

```

```php ./bind-manager.php bind:sys update```

```php ./bind-manager.php bind:sys restart```

```php ./bind-manager.php bind:sys statistics```


## Example using via crontab

add this lines to your /etc/crontab:

```10 0  * * 6   root /usr/bin/php /opt/bind-manager/bind-manager.php bind:sys update >> /var/log/bind-manager/bind-manager.log```

```*  *  * * *   root /usr/bin/php /opt/bind-manager/bind-manager.php bind:sys statistics >> /var/log/bind-manager/bind-manager.log```
