# Simple PHP upload
Simple PHP file upload (anonymous file share hosting) script.

> :warning: **Security warning**: There is no limit on file size or file type. Please make sure that file permissions are set right so nobody can execute uploaded code. See [server configuration](#server-configuration) for examples.

## TODO
- [x] Delete files
- [x] AJAX Uploader
- [x] PHP-O integration
- [x] A better README.md

## Installation
Just unpack all repo's files in a directory and edit config.php. It will work straight away

## Configuration
There are few options that you should change by editing config.php:

- Website title:

  `'title' => 'File Upload Service'`

- Website URL:

  `'url' => 'http://localhost:8080'`

- Website language:
  ```
  'lang' => 'en',
  'lang_dir' => 'ltr'
  ```
- Random name generation configs:
  ```
  'random_name_alphabet' => 'abcdefghijklmnopqrstuvwxyz0123456789',
  'random_name_len' => 20,
  ```

- Display file dates format

  `'date_format' => 'Y/m/d H:i'`

## Usage options
- Through an interface:
  - Choose files via dialogue
  - Drop files, via HTML5 drag'and'drop
  - Basic HTML Form (if no JavaScript is suported)
- Upload using any compatible tool (like cURL)

  This example will upload a file and copy URL to clipboard:

  ```bash
  curl -F "file[]=@file.jpg" localhost:8080 | xclip -sel clip
  ```

## Server configuration
Do not allow uploaded scripts' execution!

### NGINX configuration example

```
server {
  listen 80 default_server;
  listen [::]:80 default_server ipv6only=on;

  root /usr/share/nginx;
  index index.php;

  server_name localhost;

  location / {
    try_files $uri $uri/ =404;
  }

  error_page 404 /index.php;

  location /index.php {
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
    fastcgi_pass unix:/var/run/php5-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
  }
}
```

### Lighttpd configuration example

```
server.document-root = "/home/user/Public/www/"
server.port = 8080

fastcgi.server = (
  ".php" => ((
    "socket" => "/home/user/Public/meta/php/php.socket",
    "bin-path" => "/home/user/Public/src/dist/bin/php-cgi",
    "idle-timeout" => 20,
    "max-procs" => 500,
    "bin-environment" => (
      "PHP_FCGI_CHILDREN" => "15",
      "PHP_FCGI_MAX_REQUESTS" => "500",
      "PHPRC" => "/home/user/Public/config/",
      "REAL_SCRIPT_NAME" => ""
    ),
    "bin-copy-environment" => (
      "PATH", "SHELL", "USER", "BASE"
    ),
    "broken-scriptfilename" => "enable"
  ))
)

$HTTP["url"] =~ "/uploads/" {
  cgi.assign = ()
  scgi.server = ()
  fastcgi.server = ()
}
```
