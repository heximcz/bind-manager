# Bind Manager


## Overview

Periodical update of the root zone for Bind DNS resolver.

**NEW:** Support for Bind 9 Statistics and Zabbix monitoring.

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

* Configure Zabbix agent

```
cp /opt/bind-manager/zabbix/bind-resolver.conf /etc/zabbix/zabbix_agentd.d/
/etc/init.d/zabbix-agent restart
```

* Download the Bind xml template from github [bind template](https://github.com/heximcz/bind-manager/blob/master/zabbix/zabbix_bind_template.xml)
* Import template to your Zabbix monitoring

## Example Usage

print help:

```php ./bind-manager.php```

* ~~php ./bind-manager.php bind -h~~

```php ./bind-manager.php bind:sys -h```

## Using via crontab

add this lines to your /etc/crontab:

* ~~0 0  * * *   root /usr/bin/php /opt/bind-manager/bind-manager.php bind --restart >> /var/log/bind-manager/bind-manager.log~~

* ~~10 0  * * 6   root /usr/bin/php /opt/bind-manager/bind-manager.php bind --update >> /var/log/bind-manager/bind-manager.log~~

```10 0  * * 6   root /usr/bin/php /opt/bind-manager/bind-manager.php bind:sys update >> /var/log/bind-manager/bind-manager.log```

```*  *  * * *   root /usr/bin/php /opt/bind-manager/bind-manager.php bind:sys statistics >> /var/log/bind-manager/bind-manager.log```
