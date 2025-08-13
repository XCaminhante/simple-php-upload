<?
$settings = array(
  'title' => 'File Upload Service',
  'url' => O\s("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}")->replace("index.php",""),
  'lang' => 'en',
  'lang_dir' => 'ltr',
  'random_name_alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-',
  'random_name_len' => 25,
  'date_format' => 'Y/m/d H:i',
);
