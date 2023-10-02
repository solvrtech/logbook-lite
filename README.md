# logbook-lite

#### Version: 1.0.0

You can find the demo version here: [https://logbook.solvrtech.id](https://logbook.solvrtech.id).</br>
You can [contact us](https://solvrtech.id/#contact-support) or register at our [SolvrTech Support](https://support.solvrtech.id/signin) site should you need any support.

LogBook is not your simple [CRUD](https://en.wikipedia.org/wiki/Create,_read,_update_and_delete) app. It is meant to be a back office system that will store logs and monitor multiple apps at the same time.
It is a supporting tool for both development and production purposes. Even though possible,
we don’t recommend deploying LogBook on a shared hosting but rather to install it on top of a VPS or dedicated server environment
so that you will have more control (for ex. Amazon EC2, Google Compute Engine, DigitalOcean Droplets, etc).

Before installing LogBook, you should ensure that the following prerequisites are installed properly.
For a brief documentation reason, installation/setup of most of these softwares are not covered here.

#### Mandatory:

1. PHP 8.1 installed (or greater) along with the following PHP extensions:
   - Imagick
   - PDO MySQL/PostgreSQL
   - Intl
   - GD
   - cURL
   - OpenSSL
   - Mbstring
   - Iconv
   - Redis
2. One of the following database:
   - MySQL 8.0 or greater
   - MariaDB 10.11 or greater
   - PostgreSQL 14.0 or greater
3. [NGINX](https://www.nginx.com) or [Apache](https://httpd.apache.org) web server.
4. [Composer](https://getcomposer.org) 2.3 or greater.
5. [Supervisor](https://github.com/Supervisor/supervisor) or [Systemd](https://systemd.io) for background processes manager.

#### Optional:

The following softwares are not mandatory but we strongly recommend them for a better performance:

1. [Redis](https://redis.io) 6.0 or greater for better cache mechanism.

## Basic Setup

The following setups cover three mandatory things such as web server configs, application installation, and mandatory background processes.

### Web Server Configuration

##### **Notes**

The following snippets assume that LogBook is copied or uploaded into **/var/www/logbook** folder, using domain **logbook.com**, and using PHP 8.1. Please change them according to your needs.

#### NGINX

NGINX is our recommended web server to be used with LogBook. In your NGINX site config, add a new location block to handle request access for the backend and frontend part.

Location blocks handler for backend requests:

```php
location /api {
    root /var/www/logbook/public;
    try_files $uri $uri/ /index.php?$query_string;
}
```

Location blocks handler for frontend requests:

```php
location / {
    root /var/www/logbook/frontend;
    try_files $uri $uri/ /index.html;

    expires 1y;
    add_header Cache-Control "public, no-transform";
}
```

#### Apache

You can also use the Apache web server to power LogBook. Here is an example of the virtual host configuration for the backend part:

```php
<VirtualHost *:80 *:8000>
        ServerName localhost:8000

        DocumentRoot /var/www/logbook/public
        <Directory /var/www/logbook/public>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{REQUEST_FILENAME} !-f
                        RewriteRule ^(.*)$ index.php [QSA,L]
                </IfModule>
        </Directory>

        <FilesMatch \.php$>
                SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost/"
        </FilesMatch>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>
```

Here is an example of the virtual host configuration for the frontend side:

```php
<VirtualHost *:80>
        ServerName logbook.com

        DocumentRoot /var/www/logbook/public/frontend
        <Directory /var/www/logbook/public/frontend>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f [OR]
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -d
                        RewriteRule ^ - [L]
                        RewriteRule ^ /index.html [L]
                </IfModule>
        </Directory>

        <Location /api>
                ProxyPass http://localhost:8000/api
                ProxyPassReverse http://localhost:8000/api
                ProxyPassReverseCookieDomain localhost logbook.com
                ProxyPassReverseCookiePath / /api
                Header always edit Set-Cookie "(.*)" "$1; Path=/;"
        </Location>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>
```

### Installation

Once you copied or upload all the files and folders inside dist/logbook/ into your site or web root directory, for example into **/var/www/logbook/**, run the following command at the web root directory:

```bash
sudo chmod ug+x bin/install.sh && ./bin/install.sh
```

**install.sh** is a bash script that will help you to set the correct files and folders permissions in order to make LogBook to work properly and securely. It will also guide you through all of the necessary steps, including dependencies installation, application configuration, migrations, and creation of an initial administrator user.

### Background Processes

LogBook has three background processes that need to be configured by using either Systemd or Supervisor (choose one). They will do processing, followed by a sleep, then repeat it again forever.

You can choose one of the following options, by using Systemd or Supervisor.

##### **Notes**

Most of the following snippets usewww-data as the default web server user, and also use it as the same background process user. Please change it if you have a different web server user according to your system.

#### Using Systemd

Create a new systemd service file, for example **/etc/systemd/system/health-check.service**, then add the following configurations into the file:

```
[Unit]
Description=LogBook Health Check
After=network.target

[Service]
ExecStart=/usr/bin/php /var/www/logbook/bin/console app:health-status:check
WorkingDirectory=/var/www/logbook
User=www-data
Restart=always

[Install]
WantedBy=multi-user.target
```

Once the service file is saved, run the following command anywhere in order to start the service (also auto start it during a system reboot):

```bash
sudo systemctl start health-check && sudo systemctl enable health-check
```

Then create another service file for database backup service, for example **/etc/systemd/system/logbook-backup.service**:

```
[Unit]
Description=LogBook Database Backup
After=network.target

[Service]
ExecStart=/usr/bin/php /var/www/logbook/bin/console app:backup:start
WorkingDirectory=/var/www/logbook
User=www-data
Restart=always

[Install]
WantedBy=multi-user.target
```

Start the service and enable it during system reboot:

```bash
sudo systemctl start logbook-backup && sudo systemctl enable logbook-backup
```

Finally - create the last service file for purging expired records, for example **/etc/systemd/system/logbook-clear-expired.service**:

```
[Unit]
Description=LogBook Clear Expired Record
After=network.target

[Service]
ExecStart=/usr/bin/php /var/www/logbook/bin/console app:expired-record:clear
WorkingDirectory=/var/www/logbook
User=www-data
Restart=always

[Install]
WantedBy=multi-user.target
```

then start the service and enable it during system reboot:

```bash
sudo systemctl start logbook-clear-expired && sudo systemctl enable logbook-clear-expired
```

#### Using Supervisor

If you choose to use Supervisor,create a new configuration file for the health check service, for example **/etc/supervisor/conf.d/logbook-health-status-check.conf**.

Add the following configuration settings to the file:

```
[program:logbook-health-check]
command=php /var/www/logbook/bin/console app:health-status:check
directory=/var/www/logbook
autostart=true
autorestart=true
stderr_logfile=/var/log/logbook-health-check.err.log
stdout_logfile=/var/log/logbook-health-check.out.log
user=www-data
```

Then create configuration file for the database backup service, for example **/etc/supervisor/conf.d/logbook-backup.conf** with the following configurations:

```
[program:logbook-backup]
command=php /var/www/logbook/bin/console app:backup:start
directory=/var/www/logbook
autostart=true
autorestart=true
stderr_logfile=/var/log/logbook-backup.err.log
stdout_logfile=/var/log/logbook-backup.out.log
user=www-data
```

Finally, create the last configuration file for clearing expired records, for example **/etc/supervisor/conf.d/logbook-expired-clear.conf** with the following configurations:

```
[program:logbook-expired-clear]
command=php /var/www/logbook/bin/console app:expired-record:clear
directory=/var/www/logbook
autostart=true
autorestart=true
stderr_logfile=/var/log/logbook-expired-clear.err.log
stdout_logfile=/var/log/logbook-expired-clear.out.log
user=www-data
```

When all configuration files are saved, run the following commands anywhere to start them:

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start logbook-health-check logbook-backup logbook-expired-clear
```

That’s it! The process will also auto start on a system restart. In order to check the status of the process, run the following command:

```bash
sudo supervisorctl status logbook-health-check logbook-backup logbook-expired-clear
```

### Optional: Disabling CDN Usage

By default, LogBook loads some static assets from CDN (ex. Google Fonts). This approach could be considered as a violation from a data protection perspective, for example in EU GDPR. If you choose to serve these assets from your server instead, you just need to modify the **<head>** part of the **public/index.html** file.

Comment or remove the following section:

```html
<!-- using CDN -->
<link
  href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;700&display=swap"
  rel="stylesheet"
/>
<link
  href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
  rel="stylesheet"
/>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css"
/>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/gh/devicons/devicon@v2.15.1/devicon.min.css"
/>
```

And uncomment the section underneath it:

```html
<!-- <link rel="stylesheet" href="assets/fonts/open-sans/open-sans.min.css">
<link rel="stylesheet" href="assets/fonts/material-symbol/material-symbol.min.css">
<link rel="stylesheet" href="assets/fonts/bootstrap-icons/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/fonts/devicons/devicon.min.css"> -->
```

So that the final **<head>** part of index.html file will look like this:

```html
<!-- using CDN -->
<!-- <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/devicons/devicon@v2.15.1/devicon.min.css" /> -->

<!-- self hosted -->
<link rel="stylesheet" href="assets/fonts/open-sans/open-sans.min.css" />
<link
  rel="stylesheet"
  href="assets/fonts/material-symbol/material-symbol.min.css"
/>
<link
  rel="stylesheet"
  href="assets/fonts/bootstrap-icons/bootstrap-icons.min.css"
/>
<link rel="stylesheet" href="assets/fonts/devicons/devicon.min.css" />
```

### Example: Web Server Configuration (Basic Setup)

The following is an example for NGINX and Apache Virtual Host config where the installation directory is located at **/var/www/logbook**, the domain name is logbook.com, and using PHP 8.1.

#### NGINX basic setup without SSL

```php
server {
    listen 80;
    listen [::]:80;
    server_name logbook.com;
    root /var/www/logbook/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    client_max_body_size 100m;

    # handles backend requests
    location /api {
        root /var/www/logbook/public;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # handles frontend requests
    location / {
        root /var/www/logbook/public/frontend;
        try_files $uri $uri/ /index.html;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### NGINX basic setup with SSL

```php
server {
    # SSL configuration
    listen [::]:443 ssl;
    listen 443 ssl;
    ssl_certificate /path/to/ssl_certificate.crt;
    ssl_certificate_key /path/to/ssl_private_key.key;

    server_name logbook.com;
    root /var/www/logbook/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    client_max_body_size 100m;

    # handles backend requests
    location /api {
        root /var/www/logbook/public;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # handles frontend requests
    location / {
        root /var/www/logbook/public/frontend;
        try_files $uri $uri/ /index.html;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    if ($host = logbook.com) {
        return 301 https://$host$request_uri;
    }

    listen 80;
    listen [::]:80;
    server_name logbook.com;
    return 404;
}
```

#### Basic setup for Apache Virtual Host without SSL

```php
<VirtualHost *:80>
        ServerName logbook.com

        DocumentRoot /var/www/logbook/public/frontend
        <Directory /var/www/logbook/public/frontend>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f [OR]
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -d
                        RewriteRule ^ - [L]
                        RewriteRule ^ /index.html [L]
                </IfModule>
        </Directory>

        <Location /api>
                ProxyPass http://localhost:8000/api
                ProxyPassReverse http://localhost:8000/api
                ProxyPassReverseCookieDomain localhost logbook.com
                ProxyPassReverseCookiePath / /api
                Header always edit Set-Cookie "(.*)" "$1; Path=/;"
        </Location>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>

<VirtualHost *:80 *:8000>
        ServerName localhost:8000

        DocumentRoot /var/www/logbook/public
        <Directory /var/www/logbook/public>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{REQUEST_FILENAME} !-f
                        RewriteRule ^(.*)$ index.php [QSA,L]
                </IfModule>
        </Directory>

        <FilesMatch \.php$>
                SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost/"
        </FilesMatch>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>
```

#### Basic setup for Apache Virtual Host with SSL

```php
<VirtualHost *:80>
        ServerName logbook.com
        Redirect permanent / https://logbook.com/
</VirtualHost>

<VirtualHost *:443>
        ServerName logbook.com

        # SSL configuration
        SSLEngine on
        SSLCertificateFile /path/to/certificate.crt
        SSLCertificateKeyFile /path/to/private.key

        DocumentRoot /var/www/logbook/public/frontend
        <Directory /var/www/logbook/public/frontend>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f [OR]
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -d
                        RewriteRule ^ - [L]
                        RewriteRule ^ /index.html [L]
                </IfModule>
        </Directory>

        <Location /api>
                ProxyPass http://localhost:8000/api
                ProxyPassReverse http://localhost:8000/api
                ProxyPassReverseCookieDomain localhost logbook.com
                ProxyPassReverseCookiePath / /api
                Header always edit Set-Cookie "(.*)" "$1; Path=/; Secure"
        </Location>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>

<VirtualHost *:80 *:8000>
        ServerName localhost:8000

        DocumentRoot /var/www/logbook/public
        <Directory /var/www/logbook/public>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{REQUEST_FILENAME} !-f
                        RewriteRule ^(.*)$ index.php [QSA,L]
                </IfModule>
        </Directory>

        <FilesMatch \.php$>
                SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost/"
        </FilesMatch>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>
```

## Recommended Setup

The basic setup as described in [Chapter I](https://github.com/solvrtech/logbook-lite/new/master?readme=1#basic-setup) is meant for a quick and minimal installation. We strongly recommend the following additional steps and softwares to be used and configured, as they can bring performance enhancement, and to be well prepared to receive many incoming logs data while still being able to send alerts as fast as possible.

Our recommendations are:

1. Using a better cache adapter and queueing mechanism (e.g. [Redis](https://redis.io))

### Cache and Messenger

LogBook can use any supported cache adapter as defined [here](https://symfony.com/doc/current/cache.html). While being optional, we strongly recommend using a non-filesystem type of cache provider, for example by using Redis or Memcached.

#### Cache storage

To use Redis as the cache storage in LogBook, make sure Redis is installed first then add the following configuration in **/config/packages/cache.yaml** file inside your LogBook folder :

```php
framework:
    cache:
        app: cache.adapter.redis
        default_redis_provider: 'redis://127.0.0.1:6379'
```

Assuming your Redis installation is available at **127.0.0.1:6379 (localhost using port 6379)**. After changing the cache configuration, don’t forget to run the following command:

```bash
php bin/console cache:clear
```

in your LogBook root folder (for ex. **/var/www/logbook**). It will refresh the current application's cache, ensuring that any changes made to the code or configuration are reflected correctly.

Messenger
Messenger provides a message bus with the ability to send messages and then handle them immediately in your application or send them through transports (e.g. queues) to be handled later.

If you want to handle a message asynchronously, and use Redis as messenger transport, add this configuration to **/config/packages/messenger.yaml** file inside your LogBook folder.

```php
framework:
    messenger:
        transports:
            async: 'redis://127.0.0.1:6379'
        routing:
            'Symfony\Component\Mailer\Messenger\SendEmailMessage': async
```

[Symfony messenger](https://symfony.com/doc/current/messenger.html) component provides a message bus to send messages such as reset password links, 2FA tokens, and alert messages. The messenger process must be added to the process manager that your server uses (e.g., Systemd or Supervisor).

##### **Notes**

The following snippets use **www-data** as the default background process user, the same user as the web server user. Please change it according to your system.

#### Using systemd

This configuration is provided as a systemd unit file for managing the LogBook Messenger service.

```
[Unit]
Description=LogBook Messenger
After=network.target

[Service]
ExecStart=/usr/bin/php /var/www/logbook/bin/console messenger:consume async --time-limit=3600
WorkingDirectory=/var/www/logbook
User=www-data
Restart=always
RestartSec=3
LimitNOFILE=4096

[Install]
WantedBy=multi-user.target
```

Make sure to save the unit file with a **.service** extension, such as **logbook-messenger.service**, and place it in the appropriate Systemd directory according to your system, for example **/etc/systemd/system/**.

Then, you can use Systemd commands anywhere to start the service:

```bash
sudo systemctl start logbook-messenger
```

To auto start the service on a system reboot, you can run:

```bash
sudo systemctl enable logbook-messenger
```

#### Using Supervisor

Assuming that you put the LogBook installation in the **/var/www/logbook** folder, you will need to create a new configuration file in the **/etc/supervisor/conf.d/** directory as follows:

```
[program:logbook-messenger]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/logbook/bin/console messenger:consume async --time-limit=3600
autostart=true
autorestart=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/tmp/logbook.log
stopwaitsecs=3600
```

As you can see on the snippet above, we set **8** as the number of background processes that will run the given command. The **--time-limit** option is used in order to stop the worker process when the given time limit is reached (in seconds). If a message is being handled at that time, the process will stop after the processing is finished.

Once you’ve saved the file, run the following commands to start the process:

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start logbook-messenger
```

That’s it - the process will run automatically on a system restart. In order to check the status of the process, run the following command:

```bash
sudo supervisorctl status logbook-messenger
```

### Example: NGINX Config (for Recommended Setup)

The following are examples for NGINX and Apache Virtual Host configs with the following scenario:

- LogBook installation directory is located at **/var/www/logbook**
- It uses domain name of **logbook.com**
- using PHP 8.1 and Mercure (running in port 8080) for sending notifications

#### NGINX recommended setup without SSL

```php
server {
    listen 80;
    listen [::]:80;
    server_name logbook.com;
    root /var/www/logbook/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    client_max_body_size 100m;

    # handles backend requests
    location /api {
        root /var/www/logbook/public;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # handles frontend requests
    location / {
        root /var/www/logbook/public/frontend;
        try_files $uri $uri/ /index.html;
    }

    # handling the mercure requests
    location /.well-known/mercure {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### NGINX recommended setup with SSL

```php
server {
    # SSL configuration
    listen [::]:443 ssl;
    listen 443 ssl;
    ssl_certificate /path/to/ssl_certificate.crt;
    ssl_certificate_key /path/to/ssl_private_key.key;

    server_name logbook.com;
    root /var/www/logbook/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    client_max_body_size 100m;

    # handles backend requests
    location /api {
        root /var/www/logbook/public;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # handles frontend requests
    location / {
        root /var/www/logbook/public/frontend;
        try_files $uri $uri/ /index.html;
    }

    # handling the mercure requests
    location /.well-known/mercure {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    if ($host = logbook.com) {
        return 301 https://$host$request_uri;
    }

    listen 80;
    listen [::]:80;

    server_name logbook.com;
    return 404;
}
```

#### Recommended Apache Virtual Host setup without SSL

```php
<VirtualHost *:80>
        ServerName logbook.com

        DocumentRoot /var/www/logbook/public/frontend
        <Directory /var/www/logbook/public/frontend>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f [OR]
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -d
                        RewriteRule ^ - [L]
                        RewriteRule ^ /index.html [L]
                </IfModule>
        </Directory>

        <Location /api>
                ProxyPass http://localhost:8000/api
                ProxyPassReverse http://localhost:8000/api
                ProxyPassReverseCookieDomain localhost logbook.com
                ProxyPassReverseCookiePath / /api
                Header always edit Set-Cookie "(.*)" "$1; Path=/;"
        </Location>

        <Location /.well-known/mercure>
                ProxyPass http://localhost:8080/.well-known/mercure
                ProxyPassReverse http://localhost:8080/.well-known/mercure
        </Location>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>

<VirtualHost *:80 *:8000>
        ServerName localhost:8000

        DocumentRoot /var/www/logbook/public
        <Directory /var/www/logbook/public>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{REQUEST_FILENAME} !-f
                        RewriteRule ^(.*)$ index.php [QSA,L]
                </IfModule>
        </Directory>

        <FilesMatch \.php$>
                SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost/"
        </FilesMatch>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>
```

#### Recommended Apache Virtual Host setup with SSL

```php
<VirtualHost *:80>
        ServerName logbook.com
        Redirect permanent / https://logbook.com/
</VirtualHost>

<VirtualHost *:443>
        ServerName logbook.com

        # SSL configuration
        SSLEngine on
        SSLCertificateFile /path/to/certificate.crt
        SSLCertificateKeyFile /path/to/private.key

        DocumentRoot /var/www/logbook/public/frontend
        <Directory /var/www/logbook/public/frontend>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f [OR]
                        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -d
                        RewriteRule ^ - [L]
                        RewriteRule ^ /index.html [L]
                </IfModule>
        </Directory>

        <Location /api>
                ProxyPass http://localhost:8000/api
                ProxyPassReverse http://localhost:8000/api
                ProxyPassReverseCookieDomain localhost logbook.com
                ProxyPassReverseCookiePath / /api
                Header always edit Set-Cookie "(.*)" "$1; Path=/; Secure"
        </Location>

        <Location /.well-known/mercure>
                ProxyPass http://localhost:8080/.well-known/mercure
                ProxyPassReverse http://localhost:8080/.well-known/mercure
        </Location>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>

<VirtualHost *:80 *:8000>
        ServerName localhost:8000

        DocumentRoot /var/www/logbook/public
        <Directory /var/www/logbook/public>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted

                <IfModule mod_rewrite.c>
                        RewriteEngine On
                        RewriteCond %{REQUEST_FILENAME} !-f
                        RewriteRule ^(.*)$ index.php [QSA,L]
                </IfModule>
        </Directory>

        <FilesMatch \.php$>
                SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost/"
        </FilesMatch>

        ErrorLog /var/log/apache2/logbook_error.log
</VirtualHost>
```

## Connecting Your Apps to LogBook

- [Laravel logbook](https://github.com/solvrtech/laravel-logbook)
- [Symfony logbook](https://github.com/solvrtech/symfony-logbook)
