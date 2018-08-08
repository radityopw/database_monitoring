# Dependency Tool

Welcome to the official repository of dependency tool. This repo contains **two application**, stored procedure dependency tool and user dependency tool. This repo also contains guide to set the configuration and run the application.

## Table of Contents
---
- [Dependency Tool](#dependency-tool)
    - [Table of Contents](#table-of-contents)
    - [Prerequisites](#prerequisites)
    - [Connection](#connection)
        - [SQL Server Connection](#sql-server-connection)
        - [Neo4j Connection](#neo4j-connection)
    - [Image Building](#image-building)
        - [PHP-FPM Image](#php-fpm-image)
        - [Supervisor-Cron Image](#supervisor-cron-image)
    - [Compose Configuration](#compose-configuration)
        - [PHP-FPM Compose Configuration](#php-fpm-compose-configuration)
        - [Supervisor-Cron Compose Configuration](#supervisor-cron-compose-configuration)
    - [Container Supervisor-Cron Configuration](#container-supervisor-cron-configuration)
        - [Crontab](#crontab)
        - [Supervisor](#supervisor)
    - [Production](#production)
    - [Routing](#routing)
    - [Finishing](#finishing)
    - [Contributor's Corner](#contributors-corner)

## Prerequisites
---

Requirements for using this repo is as below:
1. Visual Studio Code (Editing files in personal's devices) 
2. Vim (Editing files in server)
3. Docker
4. Nginx
5. Crontab

>Note: Using Visual Studio Code to edit configuration file in source code preserve syntax and linter, so the source code is still beautiful as it was.
 
## Connection
---

Before staging source code from development to production, first, we have to set the connection variable in the application config. The connection variable is located at *database.php* file. The file is located at **./config/** folder relative to **project directory**. If the *database.php* file is not there, so we make it manually and copied the syntax from *database_config_syntax.php*.

```
<?php

return [
    'connections' => [
        'sqlsrv' => [
            'host' => '',
            'port' => 0,
            'database' =>   '',
            'username' => '',
            'password' => '',
            'charset' => 'utf-8',
            'prefix' => 'sqlsrv', 
        ],
        'neo4j' => [
            'sp' => [
                'host' => '',
                'port' => 0,
                'username' => '',
                'password' => '',
            ],
            'user' => [
                'host' => '',
                'port' => 0,
                'username' => '',
                'password' => '',
            ],
            'username_read' => '',
            'password_read' => '',
        ]
    ]
];
```

### SQL Server Connection
We set the SQL Server Connection variable. We fill the *host, port, database, username,* and *password* based on the SQL Server that we have installed. We can choose to leave the database variable empty or not. The example of configuration can be found below.

```
'sqlsrv' => [
    // Fill in the ip address, host, or domain
    'host' => '10.199.199.199',
    //Fill with the port, here is the default port
    'port' => 1433, 
    //We can leave it empty or specified it based on the selected database
    'database' =>   'hello',
    //Fill the username
    'username' => 'there', 
    //Fill the password
    'password' => 'hellothere', 
    'charset' => 'utf-8',
    'prefix' => 'sqlsrv', 
],
```

### Neo4j Connection

We set the neo4j connection based on the configured instance that we have. We fill the *host, port, username,* and *password* variable in each sp and user array based on the configuration. We can set the *username_read* and *password_read* same as the *username* and *password* set in the user array. The example of configuration can be seen below.

```
'neo4j' => [
    'sp' => [
        'host' => '10.199.199.199',
        'port' => 2687,
        'username' => 'neo4j',
        'password' => 'neo4j',
    ],
    'user' => [
        'host' => '10.199.199.199',
        'port' => 1687,
        'username' => 'neo4j',
        'password' => 'neo4j',
    ],
    'username_read' => 'neo4j',
    'password_read' => 'neo4j',
]
```

## Image Building
---

Currently, this application requiring 4 services to run. They are 2 neo4j services, php-fpm service, and supervisor-cron service. Image for neo4j has been acquired through [the official repo](https://hub.docker.com/_/neo4j/). Image for php-fpm and supervisor-cron are custom made. They are modified from the official image. So, we need to build the image first. The Dockerfiles have been provided in the application.

### PHP-FPM Image

PHP-FPM official image can be found on [this repo](https://hub.docker.com/_/php/). We need to build the custom made one, because the original image doesn't contain *sqlsrv* and *pdo_sqlsrv* extension for php. To build the image, we just run the code below relative to **project directory**.

```
docker build -t shadow/php-fpm-debian:tugas-akhir ./etc/php-fpm-debian 
```

###  Supervisor-Cron Image

Supervisor-Cron image is made from the php image of official repo with *cli* type. The job of this image is to run background processing of application with cron. We want that background processing of application runs automatically. We need to run this code below relative to **project directory**.

```
docker build -t shadow/supervisord-cron-debian:tugas-akhir ./etc/supervisord-cron-debian
```

## Compose Configuration
---

To run the service from image that taken from official repo and made by manual build, we need to run compose file from docker. In this section, the neo4j service configuration is not explained, because neo4j image is taken from the original one. Further explanation of neo4j configuration could be looked in [the official repo](https://hub.docker.com/_/neo4j/). 

### PHP-FPM Compose Configuration
```
phpfpmserver:
    image: shadow/php-fpm-debian:tugas-akhir
    deploy:
        restart_policy:
            condition: on-failure
            delay: 5s
    networks:
        - hello
    ports:
        - "9000:9000"
    # extra_hosts:
        # - "host.machine:192.168.100.13"
        # - "host.machine:192.168.2.70"
    volumes:
        - "./etc/xdebug/xdebug.ini:/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini" #/usr/local/etc/php/docker-php-ext-xdebug.ini alpine image
        - "./:/var/www/html"
```
Things that we should look at is the *ports* and *volumes* part. This compose configuration runs php-fpm service with exposed port of 9000 (default port of php-fpm) integrated with host port 9000. This means that php-fpm can be accessed locally at port 9000 through the device's ip. We need to mount the application so php-fpm can process the php file at container's storage. The location of *mounted* application is at **/var/www/html**. 

>Note: Xdebug here is not explained because it's not a fundamental necessity to run this application. Xdebug configuration is for advanced users only. 

### Supervisor-Cron Compose Configuration

```
supervisorserver:
    image: shadow/supervisord-cron-debian:tugas-akhir
    volumes:
        - "./etc/supervisor-config:/etc/supervisord.d"
        - "./etc/supervisord-cron-debian/dependency-tool.txt:/config/dependency-tool.txt"
        - "./:/var/www/html"
    networks:
        - hello
```
In here, we need to look at the *volumes* part. We need to mount the application so the service can run background processing with cron in local storage of container. Mounted application is located at **/var/www/html**. We need to mount the supervisor configuration from **./etc/supervisor-config** relative to **project directory** at **/etc/supervisord.d**. We also need to mount cron configuration from **./etc/supervisord-cron-debian/dependency-tool.txt** relative to **project directory** at **/config/dependency-tool.txt**. Details of supervisor and cron are explained later.

>Note: Further configuration of docker compose can be found at [the official documentation](https://docs.docker.com/compose/compose-file/).

##  Container Supervisor-Cron Configuration
---

In this section, further explanation of cron and supervisor is detailed below. We need to set the cron and supervisor configuration so the background processing can be run automatically without fail.

>Note: Supervisor in here is used to ensure that cron process always run, so background processing can be restarted if it is fail.

### Crontab
```
#Crontab configuration
@reboot /usr/local/bin/php -f /var/www/html/app/Components/user_process.php > /tmp/user-php.log
@reboot /usr/local/bin/php -f /var/www/html/app/Components/SP_syscat.php > /tmp/sp-syscat-php.log; /usr/local/bin/php -f /var/www/html/app/Components/SP_parsing.php > /tmp/sp-parsing-php.log
* */3 * * * /usr/local/bin/php -f /var/www/html/app/Components/user_process.php > /tmp/user-php.log
* */3 * * * /usr/local/bin/php -f /var/www/html/app/Components/SP_syscat.php > /tmp/sp-syscat-php.log; /usr/local/bin/php -f /var/www/html/app/Components/SP_parsing.php > /tmp/sp-parsing-php.log
* * * * * echo "HELLO WORLD" > /tmp/test.log

```
Based on the code above, background processing of user and stored procedure application runs at reboot (startup) and every 3 hours. We also add some of the testing with *test.log* file contains "HELLO WORLD" text with the purpose of debugging the cron. Cron syntax can be looked at [the official documentation here](https://en.wikipedia.org/wiki/Cron). User application need to run only one file that is *user_process.php* and stored procedure application need to run two files, they are *SP_syscat.php* and *SP_parsing.php* consecutively.

### Supervisor

```
# supervisord.conf
[unix_http_server]
file = /tmp/supervisor.sock
chmod = 0777
chown= nobody:nogroup

[supervisord]
logfile = /tmp/supervisord.log
logfile_maxbytes = 50MB
logfile_backups=10
loglevel = info
pidfile = /tmp/supervisord.pid
nodaemon = true
umask = 022
identifier = supervisor

[supervisorctl]
serverurl = unix:///tmp/supervisor.sock

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[include]
files = /etc/supervisord.d/*.conf
```

```
#example.conf
[program:cron]
command = cron -f -L 15
autostart=true
autorestart=true
```
All the code above is the configuration to run supervisor. We just need to look at only *program:cron* part. That part shows us that we run cron in foreground mode with log level 15. That part also runs cron with autostart and autorestart mode. Further syntax explanation of cron can be found in [the official documentation](http://supervisord.org/configuration.html).

## Production
---
In this section, now we need to run the application in production mode. Before we run the compose file,  we need to make two volumes in the *machine* to persist the storage of neo4j database. To do that, we just need to execute the code below.

```
docker volume create neo4j_user_data
docker volume create neo4j_sp_data
```

To run the compose file, we just need to execute the code below relative to **project directory**.

```
docker-compose up -d
```

## Routing
---

In here, we set the access route to the application. In production mode, the application still can't be accessed from outside and filter feature from application can't be used. Thus, setting the route is necessary. Requirement to setting the route is installing nginx in server. This can be done by the code below. 

```
sudo apt-get install nginx -y --no-install-recommends
```

After installing nginx, we change the default route for nginx with the configuration below. The nginx configuration for this application is located at **/etc/nginx/sites-enabled/dependency-tool.conf** in the server.

```
server {
        listen 80;
        listen [::]:80;
        #server_name localhost;
        charset utf-8;
        error_log  /var/log/nginx/error.log;
        access_log /var/log/nginx/access.log;
        #access_log /var/log/nginx/scripts.log scripts;
        #root /home/user/apps/gitlab.com/dependency-tool;
        #location / {
        #       autoindex on;
        #}
        location /dependency-tool {
                alias /home/user/apps/gitlab.com/dependency-tool/views;
                location ~ \.php$ {
                    rewrite ^/.+/(.+\.php)$ /$1 break;
                    #try_files $uri =404;
                    fastcgi_split_path_info ^(.+\.php)(\/.+)$;
                    fastcgi_pass 127.0.0.1:9000;
                    fastcgi_index index.php;
                    include fastcgi_params;
                    fastcgi_param SCRIPT_FILENAME /var/www/html/views$fastcgi_script_name;
                    fastcgi_param PATH_INFO $fastcgi_path_info;
                }
                #autoindex on;
        }
}
```

Since we use the php-fpm service with docker, so we need to set the *fastcgi_pass* to remote. We also need to rewrite the uri, so we can use the correct *$fastcgi_script_name* to set *SCRIPT_FILENAME* same as location in container's storage. The remote *fastcgi_pass* is equal to port forwarded by docker compose settings. Further explanation of nginx syntax can be found in [the documentation here](https://nginx.org/en/docs/). 

We need to restart nginx by using systemctl after setting the configuration with the code below.

```
sudo systemctl restart nginx
```
## Finishing
---

In this section, we just do some finishing touch to the application. We want to ensure when the server reboots or dies and starts again, docker compose runs automatically. We add additional configuration in server's cron to run a script at startup. 

First, we open the current configuration to edit it with the code below.

```
crontab -e
```

Second, we add this code below at the end of file (EOF).

```
@reboot /usr/local/bin/docker-compose -f <project directory>/docker-compose.yml up -d
```

Project directory in the code above means the application's folder that contains *docker-compose.yml* file.

## Contributor's Corner
---

* Project Leader: Radityo Prasetianto Wibowo
* Developer : 
    * Aprilia Rizki Rahmawati (Stored Procedure Dependency Application)
    * Hafiz Putra Ludyanto (User Dependency Application)

---
License: ©Dependency-Tool






