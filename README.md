# Simple PHP upload
Simple PHP file upload (file share hosting) script.

> :warning: **Security warning**: There is no limit on file size or file type. Please make sure that file permissions are set right so nobody can execute uploaded code. See [server configuration](#server-configuration) for examples.

## TODO
- [x] Delete files
- [x] AJAX Uploader
- [x] PHP-O integration
- [ ] A better README.md

## Installation
Just drop a PHP file in any directory. It will work straight away

## Configuration
There are few options that you can change by editing the file itself:

- Display file dates format

  `listfiles_date_format` => `'F d Y H:i:s'`

- Randomize file names (number of 'false')

  `random_name_len` => `4`

- Random file name letters

  `random_name_alphabet` => `'qwertyuiopasdfghjklzxcvbnm'`

## Usage options
- Through an interface:
  - Choose files via dialogue
  - Drop files, via HTML5 drag'and'drop (using [dropzone.js](http://www.dropzonejs.com/))
  - Basic HTML Form (if no JavaScript is suported)
- Upload using any compatible tool (like cURL)

  This example will upload a file and copy URL to clipboard:

  ```bash
  curl -F "file[]=@file.jpg" strace.club | xclip -sel clip
  ```

## Server configuration
Do not allow uploaded code execution!

### NGINX configuration example
Edit the NGINX configuration file (`/etc/nginx/sites-enabled/fileuploader`):

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


