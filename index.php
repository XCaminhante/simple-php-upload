<?
//@+leo-ver=5-thin
//@+node:caminhante.20210224221051.3: * @file index.php
//@@first
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
//@+others
//@+node:caminhante.20210224221501.1: ** includes
include "php-o/O.php";
include "config.php";
//@+node:caminhante.20210224221513.1: ** function html ()
function html () {
  header('Content-type: text/html; charset=UTF-8');
}
//@+node:caminhante.20210224221523.1: ** function plain_text ()
function plain_text () {
  header('Content-type: text/plain; charset=UTF-8');
}
//@+node:caminhante.20210224221717.1: ** class File
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
  //@+others
  //@+node:caminhante.20210224221830.1: *3* function __construct ($filename)
  function __construct ($filename) {
    $this->filename = O\s($filename);
    $this->pathinfo = pathinfo($filename);
  }
  //@+node:caminhante.20210224222001.1: *3* static function get_straight_path ($path)
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
  //@+node:caminhante.20210224221847.1: *3* static function current_dir ()
  // Directory of the current script
  static function current_dir () {
    return dirname(__FILE__);
  }
  //@+node:caminhante.20210224224153.1: *3* Informations
  //@+node:caminhante.20210224221939.1: *4* static function separator ()
  static function separator () {
    return DIRECTORY_SEPARATOR;
  }
  //@+node:caminhante.20210224222038.1: *4* public function is_directory ()
  public function is_directory () {
    return is_dir($this->filename);
  }
  //@+node:caminhante.20210224222106.1: *4* public function is_file ()
  public function is_file () {
    return is_file($this->filename);
  }
  //@+node:caminhante.20210224222150.1: *4* public function parent ()
  // The directory containing this file/directory
  public function parent () {
    return $this->pathinfo['dirname'];
  }
  //@+node:caminhante.20210224222201.1: *4* public function path ()
  // Canonical path to file/directory
  public function path () {
    if ($this->exists()) { return realpath($this->filename); }
    return realpath($this->parent()) . File::separator() . $this->name();
  }
  //@+node:caminhante.20210224222209.1: *4* public function name ()
  public function name () {
    return $this->pathinfo['basename'];
  }
  //@+node:caminhante.20210224222217.1: *4* public function relative_name ()
  public function relative_name () {
    return $this->filename;
  }
  //@+node:caminhante.20210224222229.1: *4* public function extension ()
  public function extension () {
    return $this->pathinfo['extension'];
  }
  //@+node:caminhante.20210224222238.1: *4* public function length ()
  // Length of bytes on file or number of entries on a directory
  public function length () {
    if ($this->is_directory()) {
      $dirlist = scandir($this->filename, SCANDIR_SORT_NONE);
      return O\a($dirlist)->count() - 2; }
    if ($this->is_file()) {
      return filesize($this->filename); }
    return -1;
  }
  //@+node:caminhante.20210224222250.1: *4* public function exists ()
  public function exists () {
    return file_exists($this->filename);
  }
  //@+node:caminhante.20210224222258.1: *4* public function is_open ()
  public function is_open () {
    return $filehandle != false;
  }
  //@+node:caminhante.20210224222310.1: *4* public function is_uploaded ()
  public function is_uploaded () {
    return is_uploaded_file($this->filename);
  }
  //@+node:caminhante.20210224222319.1: *4* public function can_read ()
  public function can_read () {
    return is_readable($this->filename);
  }
  //@+node:caminhante.20210224222330.1: *4* public function can_write ()
  public function can_write () {
    return is_writable($this->filename);
  }
  //@+node:caminhante.20210224223819.1: *4* public function creation_time ()
  public function creation_time () {
    return filectime($this->filename);
  }
  //@+node:caminhante.20210224223827.1: *4* public function last_modified ()
  public function last_modified () {
    return filemtime($this->filename);
  }
  //@+node:caminhante.20210224223833.1: *4* public function last_accessed ()
  public function last_accessed () {
    return fileatime($this->filename);
  }
  //@+node:caminhante.20210224223841.1: *4* public function position ()
  // The file pointer position
  public function position () {
    if (!$this->filehandle) { return -1; }
    return ftell($this->filehandle);
  }
  //@+node:caminhante.20210224223849.1: *4* public function mode ()
  // A string describing the mode used to open the file
  public function mode () {
    return $this->openmode;
  }
  //@+node:caminhante.20210224224415.1: *3* Manipulations
  //@+node:caminhante.20210224223856.1: *4* public function open ($mode)
  public function open ($mode) {
    if ($this->filehandle) { return false; }
    if ($this->is_directory()) {
      $this->openmode = O\s("d");
      $this->filehandle = opendir($this->filename);
    } else {
      $this->openmode = O\s($mode);
      $this->filehandle = fopen($this->filename,$mode); }
    if (!$this->filehandle) { $this->openmode = ''; }
    return ($this->filehandle != false);
  }
  //@+node:caminhante.20210224223904.1: *4* public function open_rw ()
  public function open_rw () {
    return $this->open("c+b");
  }
  //@+node:caminhante.20210224223919.1: *4* public function close ()
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
  //@+node:caminhante.20210224223929.1: *4* public function remove ()
  public function remove () {
    if ($this->filehandle) { $this->close(); }
    if ($this->is_file()) { return unlink($this->filename); }
    if ($this->is_directory()) { return rmdir($this->filename); }
    return false;
  }
  //@+node:caminhante.20210224223937.1: *4* public function copy_to ($target)
  public function copy_to ($target) {
    $this->flush();
    return copy($this->filename,$target);
  }
  //@+node:caminhante.20210224223944.1: *4* public function rename_to ($target)
  public function rename_to ($target) {
    if ($this->filehandle) { $this->close(); }
    $r = rename($this->filename,$target);
    if ($r) {
      $this->filename = O\s($target);
      $this->pathinfo = pathinfo($target); }
    return $r;
  }
  //@+node:caminhante.20210224223951.1: *4* public function seek ($pos = 0, $whence = SEEK_CUR)
  public function seek ($pos = 0, $whence = SEEK_CUR) {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fseek($this->filehandle, $pos, $whence); }
    return false;
  }
  //@+node:caminhante.20210224224000.1: *4* public function rewind ()
  public function rewind () {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return rewind($this->filehandle); }
    if ($this->is_directory()) { rewinddir($this->filehandle); return true; }
    return false;
  }
  //@+node:caminhante.20210224224005.1: *4* public function read ($bytes = 1)
  public function read ($bytes = 1) {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fread($this->filehandle, $bytes); }
    if ($this->is_directory()) { return readdir($this->filehandle); }
    return false;
  }
  //@+node:caminhante.20210224224017.1: *4* public function readln ()
  public function readln () {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fgets($this->filehandle); }
    if ($this->is_directory()) { return readdir($this->filehandle); }
    return false;
  }
  //@+node:caminhante.20210224224024.1: *4* public function read_all ()
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
  //@+node:caminhante.20210224224041.1: *4* public function write ($str)
  public function write ($str) {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fwrite($this->filehandle, $str); }
    return false;
  }
  //@+node:caminhante.20210224224047.1: *4* public function write_all ($lines)
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
  //@+node:caminhante.20210224224052.1: *4* public function flush ()
  public function flush () {
    if (!$this->filehandle) { return false; }
    if ($this->is_file()) { return fflush($this->filehandle); }
    return false;
  }
  //@+node:caminhante.20210224224102.1: *4* public function list ($pattern = '*')
  public function list ($pattern = '*') {
    $dir = $this->parent();
    if ($this->is_directory() && $this->exists()) {
      $dir = $this->name(); }
    return glob($dir . File::separator() . $pattern, GLOB_MARK & GLOB_NOSORT & GLOB_BRACE);
  }
  //@+node:caminhante.20210224224109.1: *4* public function mkdir ($name = false)
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
  //@+node:caminhante.20210224224118.1: *4* public function mkfile ($name)
  public function mkfile ($name) {
    $dir = $this->parent();
    if ($this->is_directory() && $this->exists()) {
      $dir = $this->name(); }
    return touch($dir . File::separator() . $name);
  }
  //@+node:caminhante.20210224224450.1: *3* Locks
  //@+node:caminhante.20210224224124.1: *4* public function lock_exclusive ()
  public function lock_exclusive () {
    if ($this->is_directory()) { return false; }
    if (!$this->filehandle) { $this->open_rw(); }
    return ($this->filehandle &&
      flock($this->filehandle, LOCK_EX|LOCK_NB, $eWouldBlock) &&
      $eWouldBlock == 0);
  }
  //@+node:caminhante.20210224224133.1: *4* public function lock_shared ()
  public function lock_shared () {
    if ($this->is_directory()) { return false; }
    if (!$this->filehandle) { $this->open_rw(); }
    return ($this->filehandle &&
      flock($this->filehandle, LOCK_SH|LOCK_NB, $eWouldBlock) &&
      $eWouldBlock == 0);
  }
  //@+node:caminhante.20210224224140.1: *4* public function unlock ()
  public function unlock () {
    if ($this->filehandle) { return $this->close(); }
    return false;
  }
  //@-others
}
//@+node:caminhante.20210224221744.1: ** class Template
class Template {
  private $template;
  private $keys;
  //@+others
  //@+node:caminhante.20210224223435.1: *3* function __construct ($template)
  function __construct ($template) {
    $this->template = O\s($template);
    $this->keys = [];
  }
  //@+node:caminhante.20210224223443.1: *3* public function with ($key, $value)
  public function with ($key, $value) {
    $this->keys[$key] = $value;
  }
  //@+node:caminhante.20210224223449.1: *3* public function read ()
  public function read () {
    $r = $this->template;
    foreach ($this->keys as $key => $value) { $r = O\s($r->replace($key,$value)); }
    return $r;
  }
  //@-others
}
//@+node:caminhante.20210224222405.1: ** class FileTemplate
class FileTemplate {
  private $file;
  private $keys;
  //@+others
  //@+node:caminhante.20210224223500.1: *3* function __construct ($filename)
  function __construct ($filename) {
    $this->file = new File($filename);
    $this->keys = [];
  }
  //@+node:caminhante.20210224223510.1: *3* public function with ($key, $value)
  public function with ($key, $value) {
    $this->keys[$key] = $value;
  }
  //@+node:caminhante.20210224223520.1: *3* public function read ()
  public function read () {
    $this->file->open("rb");
    $r = O\s($this->file->read_all());
    $this->file->close();
    foreach ($this->keys as $key => $value) { $r = O\s($r->replace($key, $value)); }
    return $r;
  }
  //@-others
}
//@+node:caminhante.20210224222522.1: ** class StuffedStringArray
class StuffedStringArray {
  private $array = null;
  private $prologue = '';
  private $epilogue = '';
  private $before = '';
  private $after = '';
  //@+others
  //@+node:caminhante.20210224223319.1: *3* function __construct (&$array)
  function __construct (&$array) {
    $this->array = O\a($array);
  }
  //@+node:caminhante.20210224223340.1: *3* public function prologue ($str)
  public function prologue ($str) {
    $this->prologue = $str;
  }
  //@+node:caminhante.20210224223350.1: *3* public function epilogue ($str)
  public function epilogue ($str) {
    $this->epilogue = $str;
  }
  //@+node:caminhante.20210224223358.1: *3* public function before ($str)
  public function before ($str) {
    $this->before = $str;
  }
  //@+node:caminhante.20210224223403.1: *3* public function after ($str)
  public function after ($str) {
    $this->after = $str;
  }
  //@+node:caminhante.20210224223410.1: *3* public function read ()
  public function read () {
    $r = O\s($this->prologue);
    foreach($this->array as $value) { $r .= $this->before . $value . $this->after; }
    $r .= $this->epilogue;
    return $r;
  }
  //@-others
}
//@+node:caminhante.20210224222606.1: ** class UploadedFile
class UploadedFile {
  private $url;
  private $file;
  //@+others
  //@+node:caminhante.20210224222938.1: *3* function __construct (&$url,&$file)
  function __construct (&$url,&$file) {
    $this->url = $url;
    $this->file = $file;
  }
  //@+node:caminhante.20210224222956.1: *3* static function format_bytes (int $size)
  static function format_bytes (int $size) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');  
    return round(pow(1024, $base-floor($base)), 2).' '.$suffixes[floor($base)];
  }
  //@+node:caminhante.20210224223057.1: *3* public function url ()
  public function url () {
    return $this->url . $this->file->relative_name();
  }
  //@+node:caminhante.20210224223105.1: *3* public function size ()
  public function size () {
    return UploadedFile::format_bytes($this->file->length());
  }
  //@+node:caminhante.20210224223111.1: *3* public function creation ()
  public function creation () {
    global $settings;
    return date($settings['date_format'], $this->file->creation_time());
  }
  //@+node:caminhante.20210224223123.1: *3* public function name ()
  public function name () {
    return $this->file->name();
  }
  //@+node:caminhante.20210224223132.1: *3* public function html_link ()
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
  //@+node:caminhante.20210224223140.1: *3* public function html_button ($action,$description)
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
  //@+node:caminhante.20210224223150.1: *3* public function read ()
  public function read () {
    return $this->html_link() . $this->html_button('delete','delete');
  }
  //@-others
}
//@+node:caminhante.20210224222609.1: ** class UploadedFilesList
class UploadedFilesList {
  private $files;
  private $url;
  //@+others
  //@+node:caminhante.20210224222852.1: *3* function __construct (&$url, &$files_array)
  function __construct (&$url, &$files_array) {
    $this->url = $url;
    $this->files = $files_array;
  }
  //@+node:caminhante.20210224222908.1: *3* public function read ()
  public function read () {
    $url = $this->url;
    $fileitems = O\a($this->files) ->map(function ($f) use (&$url) {
        $a = new UploadedFile($url, $f);
        return $a->read(); });
    $items = new StuffedStringArray($fileitems);
    $items->before('<li class="owned">'."\n");
    $items->after('</li>'."\n");
    return $items->read();
  }
  //@-others
}
//@+node:caminhante.20210224222612.1: ** class RandomName
class RandomName {
  private $name = '';
  //@+others
  //@+node:caminhante.20210224222807.1: *3* function __construct ($alphabet, $namesize)
  function __construct ($alphabet, $namesize) {
    $name = '';
    while (strlen($name) < $namesize) {
      $name .= $alphabet[mt_rand(0, strlen($alphabet)-1)]; }
    $this->name = O\s($name);
  }
  //@+node:caminhante.20210224222813.1: *3* public function name ()
  public function name () {
    return $this->name;
  }
  //@-others
}
//@+node:caminhante.20210224222617.1: ** function diverse_array ($vector)
// Rotate a two-dimensional array. Used for file uploads
function diverse_array ($vector) {
  $result = [];
  foreach ($vector as $key1 => $value1) {
    foreach ($value1 as $key2 => $value2) {
      $result[$key2][$key1] = $value2; }}
  return $result;
}
//@+node:caminhante.20210224222623.1: ** function file_upload_error ($err)
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
//@+node:caminhante.20210224222629.1: ** function new_random_filename ($extension)
function new_random_filename ($extension) {
  a:
  global $settings;
  $newname = new RandomName($settings['random_name_alphabet'], $settings['random_name_len']);
  $ext = O\s($extension)
    ->trim()
    ->substr(0,$settings['random_name_len']-1)
    ->preg_replace('/[^a-zA-Z0-9_-]/u','');
  if ($ext == '') { $ext = 'txt'; }
  $nfilename = './uploads/' . $newname->name() . '.' . $ext;
  if (file_exists($nfilename)) { goto a; }
  return $nfilename;
}
//@+node:caminhante.20210224222632.1: ** function redirect_after ($seconds,$url)
function redirect_after ($seconds,$url) {
  $t = new Template('Refresh: {SECS};url={URL}');
  $t->with('{SECS}',$seconds);
  $t->with('{URL}',$url);
  header($t->read());
}
//@+node:caminhante.20210224222634.1: ** function upload_file ($file_data)
function upload_file ($file_data) {
  global $settings;
  redirect_after(10,$settings['url']);
  if ($file_data['error'] != UPLOAD_ERR_OK) { file_upload_error($file_data['error']); return; }
  $file = new File($file_data['tmp_name']);
  $orig = new File($file_data['name']);
  $nfilename = new_random_filename($orig->extension());
  if ($file->rename_to($nfilename)) {
    echo($settings['url'] . $nfilename . "\n");
  } else {
    echo('Something gone wrong'); }
}
//@+node:caminhante.20210224222638.1: ** function delete_file ($filename)
function delete_file ($filename) {
  global $settings;
  redirect_after(2,$settings['url']);
  $file = new File('uploads/' . $filename);
  if ($file->exists() && O\s($file->path())->pos(File::current_dir().'/uploads/') == 0) {
    if ($file->remove()) {
      echo('deleted: '.$file->name());
    } else {
      echo('Something gone wrong'); }
  } else {
    echo('Something gone wrong'); }
}
//@+node:caminhante.20210224223643.1: ** main code
//@+node:caminhante.20210224223659.1: *3* delete file case
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
//@+node:caminhante.20210224223708.1: *3* upload case
if (isset($_FILES['file'])) {
  plain_text();
  if (is_array($_FILES['file'])) {
    $file_array = diverse_array($_FILES['file']);
    foreach ($file_array as $file_data) { upload_file($file_data); }
  } else {
    upload_file($_FILES['file']); }
  exit;
}
//@+node:caminhante.20210224223716.1: *3* default case
html();
$template = new FileTemplate('upload.html');
$template->with('{LANG}',$settings['lang']);
$template->with('{LANG_DIR}',$settings['lang_dir']);
$template->with('{TITLE}',$settings['title']);
$template->with('{URL}',$settings['url']);
$template->with('{MAX_UPLOAD}',ini_get('upload_max_filesize'));
$template->with('{MAX_FILES}',ini_get('max_file_uploads'));
$uploads = new File('uploads');
$uploads_file_list = $uploads->list();
$uploaded_files = O\a($uploads_file_list) ->map(function ($i) { return new File($i); });
$ufiles_list = new UploadedFilesList($settings['url'], $uploaded_files);
$template->with('{FILES_LIST}', $ufiles_list->read());
echo($template->read());
//@-others
//@-leo
