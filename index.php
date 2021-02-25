<?php
/*
  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace O; include "php-o/O.php";
include "config.php";
function html () {
  header('Content-type: text/html; charset=UTF-8');
}
function plain_text () {
  header('Content-type: text/plain; charset=UTF-8');
}
class File {
  private const link = 0120000;
  private const file = 0100000;
  private const block = 0060000;
  private const directory = 0040000;
  private const fifo = 0010000;
  private $filename = '';
  private $pathinfo = '';
  private $filehandle = false;
  private $openmode = '';
  // private $stats;
  function __construct ($filename) {
    $this->filename = s($filename);
    $this->pathinfo = pathinfo($filename);
    // $this->stats = stats($filename);
  }
  function __destruct () {
  }
  // Directory of the current script
  static function current_dir () {
    return dirname(__FILE__);
  }
  static function separator () {
    return DIRECTORY_SEPARATOR;
  }
  static function get_straight_path ($path) {
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
      if ('.' == $part) continue;
      if ('..' == $part) {
        array_pop($absolutes);
      } else {
        $absolutes[] = $part; }}
    return implode(DIRECTORY_SEPARATOR, $absolutes);
  }
  public function is_directory () {
    return is_dir($this->filename);
    // return ($this->stats['mode'] & directory) === directory;
  }
  public function is_file () {
    return is_file($this->filename);
    // return ($this->stats['mode'] & file) === file;
  }
  // The directory containing this file/directory
  public function parent () {
    // return dirname($this->filename);
    return $this->pathinfo['dirname'];
  }
  // Canonical path to file/directory
  public function path () {
    if ($this->exists()) { return realpath($this->filename); }
    return realpath($this->parent()) . File::separator() . $this->name();
  }
  public function name () {
    return $this->pathinfo['basename'];
  }
  public function relative_name () {
    return $this->filename;
  }
  public function extension () {
    return $this->pathinfo['extension'];
  }
  // Length of bytes on file or number of entries on a directory
  public function length () {
    if ($this->is_directory()) {
      return a(scandir($this->filename,SCANDIR_SORT_NONE))->count() - 2; }
    if ($this->is_file()) {
      return filesize($this->filename); }
    return -1;
    // return $this->stats['size'];
  }
  public function exists () {
    return file_exists($this->filename);
  }
  public function is_open () {
    return $filehandle != false;
  }
  public function is_uploaded () {
    return is_uploaded_file($this->filename);
  }
  public function can_read () {
    return is_readable($this->filename);
  }
  public function can_write () {
    return is_writable($this->filename);
  }
  public function creation_time () {
    return filectime($this->filename);
  }
  public function last_modified () {
    return filemtime($this->filename);
  }
  public function last_accessed () {
    return fileatime($this->filename);
  }
  // The file pointer position
  public function position () {
    if (!$this->filehandle) { return -1; }
    return ftell($this->filehandle);
  }
  // A string describing the mode used to open the file
  public function mode () {
    return $this->openmode;
  }
  public function open ($mode) {
    if ($this->filehandle) { return false; }
    if ($this->is_directory()) {
      $this->openmode = s("d");
      $this->filehandle = opendir($this->filename);
    } else {
      $this->openmode = s($mode);
      $this->filehandle = fopen($this->filename,$mode); }
    if (!$this->filehandle) { $this->openmode = ''; }
    return ($this->filehandle != false);
  }
  public function open_rw () {
    return $this->open("c+b");
  }
  public function close () {
    if (!$this->filehandle) { return false; }
    if ($this->is_directory()) {
      closedir($this->filehandle);
      $r = true; }
    if ($this->is_file()) {
      $r = fclose($this->filehandle); }
    $this->filehandle = false;
    $this->openmode = '';
    return $r;
  }
  public function remove () {
    if ($this->filehandle) { $this->close(); }
    if ($this->is_file()) { return unlink($this->filename); }
    if ($this->is_directory()) { return rmdir($this->filename); }
    return false;
  }
  public function copy_to ($target) {
    $this->flush();
    return copy($this->filename,$target);
  }
  public function rename_to ($target) {
    if ($this->filehandle) { $this->close(); }
    $r = rename($this->filename,$target);
    if ($r) {
      $this->filename = s($target);
      $this->pathinfo = pathinfo($target); }
    return $r;
  }
  public function seek ($pos = 0, $whence = SEEK_CUR) {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fseek($this->filehandle, $pos, $whence); }
    return false;
  }
  public function rewind () {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return rewind($this->filehandle); }
    if ($this->is_directory()) { rewinddir($this->filehandle); return true; }
    return false;
  }
  public function read ($bytes = 1) {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fread($this->filehandle, $bytes); }
    if ($this->is_directory()) { return readdir($this->filehandle); }
    return false;
  }
  public function readln () {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fgets($this->filehandle); }
    if ($this->is_directory()) { return readdir($this->filehandle); }
    return false;
  }
  public function read_all () {
    if (!$this->filehandle) { return false; }
    $this->rewind();
    if ($this->is_file()) { return $this->read($this->length()); }
    if ($this->is_directory()) {
      $r = '';
      for ($i = 1; $i <= $this->length(); $i++) { $r .= readdir($this->filehandle) . "\n"; }
      return $r; }
    return false;
  }
  public function write ($str) {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fwrite($this->filehandle, $str); }
    return false;
  }
  public function write_all ($lines) {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { 
      ftruncate($this->filehandle, 0);
      rewind($this->filehandle);
      foreach ($lines as $line) {
        $r = $this->write($line);
        if ($r != count($line)) { return false; }}
      return true; }
    return false;
  }
  public function flush () {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fflush($this->filehandle); }
    return false;
  }
  public function list ($pattern = '*') {
    $dir = $this->parent();
    if ($this->is_directory() && $this->exists()) {
      $dir = $this->name(); }
    return glob($dir . File::separator() . $pattern, GLOB_MARK & GLOB_NOSORT & GLOB_BRACE);
  }
  public function mkdir ($name = false) {
    if (!$name && !$this->exists()) {
      return mkdir($this->filename, 0777, true);
    } else {
      return false; }
    $dir = $this->parent();
    if ($this->is_directory() && $this->exists()) {
      $dir = $this->name(); }
    return mkdir($dir . File::separator() . $name, 0777, true);
  }
  public function mkfile ($name) {
    $dir = $this->parent();
    if ($this->is_directory() && $this->exists()) {
      $dir = $this->name(); }
    return touch($dir . File::separator() . $name);
  }
  public function lock_exclusive () {
    if ($this->is_directory()) { return false; }
    if (!$this->filehandle) { $this->open_rw(); }
    return ($this->filehandle &&
      flock($this->filehandle, LOCK_EX|LOCK_NB, $eWouldBlock) == true &&
      $eWouldBlock == 0);
  }
  public function lock_shared () {
    if ($this->is_directory()) { return false; }
    if (!$this->filehandle) { $this->open_rw(); }
    return ($this->filehandle &&
      flock($this->filehandle, LOCK_SH|LOCK_NB, $eWouldBlock) &&
      $eWouldBlock == 0);
  }
  public function unlock () {
    if ($this->filehandle) { return $this->close(); }
    return false;
  }
}
class Template {
  private $keys;
  private $template;
  function __construct ($template) {
    $this->template = s($template);
    $this->keys = [];
  }
  public function with ($key, $value) {
    $this->keys[$key] = $value;
  }
  public function read () {
    $r = $this->template;
    foreach ($this->keys as $key => $value) { $r = s($r->replace($key,$value)); }
    return $r;
  }
}
class FileTemplate {
  private $file;
  private $keys;
  function __construct ($filename) {
    $this->file = new File($filename);
    $this->keys = [];
  }
  public function with ($key, $value) {
    $this->keys[$key] = $value;
  }
  public function read () {
    $this->file->open("rb");
    $r = s($this->file->read_all());
    $this->file->close();
    foreach ($this->keys as $key => $value) { $r = s($r->replace($key,$value)); }
    return $r;
  }
}
class StuffedStringArray {
  private $array = null;
  private $prologue = '';
  private $epilogue = '';
  private $before = '';
  private $after = '';
  function __construct (&$array) {
    $this->array = a($array);
  }
  public function prologue ($str) {
    $this->prologue = $str;
  }
  public function epilogue ($str) {
    $this->epilogue = $str;
  }
  public function before ($str) {
    $this->before = $str;
  }
  public function after ($str) {
    $this->after = $str;
  }
  public function read () {
    $r = s($this->prologue);
    foreach($this->array as $value) { $r .= $this->before . $value . $this->after; }
    $r .= $this->epilogue;
    return $r;
  }
}
class UploadedFile {
  private $url;
  private $file;
  function __construct (&$url,&$file) {
    $this->url = $url;
    $this->file = $file;
  }
  static function format_bytes (int $size) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');  
    return round(pow(1024, $base-floor($base)), 2).''.$suffixes[floor($base)];
  }
  public function url () {
    return $this->url . $this->file->relative_name();
  }
  public function size () {
    return UploadedFile::format_bytes($this->file->length());
  }
  public function creation () {
    global $settings;
    return date($settings['date_format'], $this->file->creation_time());
  }
  public function name () {
    return $this->file->name();
  }
  public function html_link () {
    $link = new Template(
      '<a class="uploaded_file" href="{FILE_URL}" target="_blank">'."\n".
        '{FILE_NAME}<span>({FILE_SIZE}, {FILE_CREATION})</span>'."\n".
      '</a>'."\n");
    $link->with('{FILE_URL}',$this->url());
    $link->with('{FILE_NAME}',$this->name());
    $link->with('{FILE_SIZE}', $this->size());
    $link->with('{FILE_CREATION}', $this->creation());
    return $link->read();
  }
  public function html_button ($action,$description) {
    $button = new Template(
      '<form action="{SERVER_URL}" method="post">'."\n".
        '<input type="hidden" name="target" value="{FILE_NAME}"/>'."\n".
        '<input type="hidden" name="action" value="{BUTTON_ACTION}"/>'."\n".
        '<button type="submit">{BUTTON_DESC}</button>'."\n".
      '</form>'."\n");
    $button->with('{SERVER_URL}',$this->url);
    $button->with('{FILE_NAME}',$this->name());
    $button->with('{BUTTON_ACTION}',$action);
    $button->with('{BUTTON_DESC}',$description);
    return $button->read();
  }
  public function read () {
    return $this->html_link() . $this->html_button('delete','delete');
  }
}
class UploadedFilesList {
  private $files;
  private $url;
  function __construct (&$url, &$files_array) {
    $this->url = $url;
    $this->files = $files_array;
  }
  public function read () {
    $url = $this->url;
    $fileitems = a($this->files) ->map(function ($f) {
        $a = new UploadedFile($url, $f);
        return $a->read(); });
    $items = new StuffedStringArray($fileitems);
    $items->before('<li class="owned">'."\n");
    $items->after('</li>'."\n");
    return $items->read();
  }
}
class RandomName {
  private $name = '';
  function __construct ($alphabet, $namesize) {
    $name = '';
    while (strlen($name) < $namesize) {
      $name .= $alphabet[mt_rand(0, strlen($alphabet)-1)]; }
    $this->name = s($name);
  }
  public function name () {
    return $this->name;
  }
}
// Rotate a two-dimensional array. Used for file uploads
function diverse_array ($vector) {
  $result = [];
  foreach ($vector as $key1 => $value1) {
    foreach ($value1 as $key2 => $value2) {
      $result[$key2][$key1] = $value2; }}
  return $result;
}
function file_upload_error ($err) {
  switch ($err) {
  case UPLOAD_ERR_NO_FILE:
    echo('No file sent.' . "\n");
  case UPLOAD_ERR_INI_SIZE:
  case UPLOAD_ERR_FORM_SIZE:
    echo('Exceeded filesize limit.' . "\n");
  default:
    echo('Unknown error.' . "\n");
  }
}
function new_random_filename ($extension) {
  a:
  global $settings;
  $newname = new RandomName($settings['random_name_alphabet'], $settings['random_name_len']);
  $ext = s(s(s($extension)
    ->trim())
    ->substr(0,$settings['random_name_len']-1))
    ->preg_replace('/[^a-zA-Z0-9_-]/u','');
  if ($ext == '') { $ext = 'txt'; }
  $nfilename = 'uploads/' . $newname->name() . '.' . $ext;
  if (file_exists($nfilename)) { goto a; }
  return $nfilename;
}
function redirect_after ($seconds,$url) {
  $t = new Template('Refresh: {SECS};url={URL}');
  $t->with('{SECS}',$seconds);
  $t->with('{URL}',$url);
  header($t->read());
}
function upload_file ($file_data) {
  global $settings;
  redirect_after(10,$settings['url']);
  if ($file_data['error'] != UPLOAD_ERR_OK) { file_upload_error($file_data['error']); return; }
  $file = new File($file_data['tmp_name']);
  $orig = new File($file_data['name']);
  $nfilename = new_random_filename($orig->extension());
  if ($file->rename_to($nfilename)) {
    echo($settings['url'] . '/' . $nfilename . "\n");
  } else {
    echo('Something gone wrong'); }
}
function delete_file ($filename) {
  global $settings;
  redirect_after(2,$settings['url']);
  $file = new File('uploads/' . $filename);
  if ($file->exists() && s($file->path())->pos(File::current_dir().'/uploads/') == 0) {
    if ($file->remove()) {
      echo('deleted: '.$file->name());
    } else {
      echo('Something gone wrong'); }
  } else {
    echo('Something gone wrong'); }
}
if (isset($_POST) && isset($_POST['target']) && isset($_POST['action'])) {
  plain_text();
  switch ($_POST['action']) {
  case 'delete':
    delete_file($_POST['target']);
    break;
  default:
    echo 'Error'; }
  exit;
}
if (isset($_FILES['file'])) {
  plain_text();
  if (is_array($_FILES['file'])) {
    $file_array = diverse_array($_FILES['file']);
    foreach ($file_array as $file_data) { upload_file($file_data); }
  } else {
    upload_file($_FILES['file']); }
  exit;
}
html();
$template = new FileTemplate('upload.html');
$template->with('{LANG}',$settings['lang']);
$template->with('{LANG_DIR}',$settings['lang_dir']);
$template->with('{TITLE}',$settings['title']);
$template->with('{URL}',$settings['url']);
$template->with('{MAX_UPLOAD}',ini_get('upload_max_filesize'));
$template->with('{MAX_FILES}',ini_get('max_file_uploads'));
$uploads = new File('uploads');
$uploaded_files = a($uploads->list()) ->map(function ($i) { return new File($i); });
$ufiles_list = new UploadedFilesList($url, $uploaded_files);
$template->with('{FILES_LIST}', $ufiles_list->read());
echo($template->read());
