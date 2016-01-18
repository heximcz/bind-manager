# Bind Manager


## Overview

Auto update root zones, check if all ok, bind restart.

## Prerequisites

bind, php > 5.6.x (with enable shell_exec and exec function in php.ini)

## How to install Bind Manager

 - Connet via SSH to your web server
 - ```cd /opt/```
 - ```git clone https://github.com/heximcz/bind-manager.git```
 - ```cd /opt/bind-manager/```
 - ```git tag -l```
 - ```git checkout tags/<last tag name of stable version>```
 - ```cp ./config.default.yml ./config.yml```
 - ```mkdir -p /var/log/bind-manager/```
 -  if need it, change your preferences in the config.yml file

## How to update Bind Manager

 - ```git pull```
 - ```git tag -l```
 - ```git checkout tags/<last tag name of stable version>```

## Example Usage

print help:

```php ./bind-manager.php```

```php ./bind-manager.php bind -h```

### Using via crontab

add this lines to your /etc/crontab:

```0 0  * * *   root /usr/local/sbin/php /opt/bind-manager/bind-manager.php bind --restart >> /var/log/bind-manager/bind-manager.log```

```10 0  * * 6   root /usr/local/sbin/php /opt/bind-manager/bind-manager.php bind --update >> /var/log/bind-manager/bind-manager.log```
