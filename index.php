<?
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
//NODE Includes
include "php-o/O.php";
include "config.php";
//NODE Functions
function html () {
  header('Content-type: text/html; charset=UTF-8');
}
function plain_text () {
  header('Content-type: text/plain; charset=UTF-8');
}
//NODE * class Template
class Template {
  private $template;
  private $keys;
  //NODE function __construct ($template)
  function __construct ($template) {
    $this->template = O\s($template);
    $this->keys = [];
  }
  //NODE public function with ($key, $value)
  public function with ($key, $value) {
    $this->keys[$key] = $value;
  }
  //NODE public function read ()
  public function read () {
    $r = $this->template;
    foreach ($this->keys as $key => $value) { $r = O\s($r->replace($key,$value)); }
    return $r;
  }
}
//NODE * class FileTemplate
class FileTemplate {
  private $file;
  private $keys;
  //NODE function __construct ($filename)
  function __construct ($filename) {
    $this->file = O\f($filename);
    $this->keys = [];
  }
  //NODE public function with ($key, $value)
  public function with ($key, $value) {
    $this->keys[$key] = $value;
  }
  //NODE public function read ()
  public function read () {
    $this->file->open("rb");
    $r = O\s($this->file->read_all());
    $this->file->close();
    foreach ($this->keys as $key => $value) { $r = O\s($r->replace($key, $value)); }
    return $r;
  }
}
//NODE * class StuffedStringArray
class StuffedStringArray {
  private $array = null;
  private $prologue = '';
  private $epilogue = '';
  private $before = '';
  private $after = '';
  //NODE function __construct (&$array)
  function __construct (&$array) {
    $this->array = O\a($array);
  }
  //NODE public function prologue ($str)
  public function prologue ($str) {
    $this->prologue = $str;
  }
  //NODE public function epilogue ($str)
  public function epilogue ($str) {
    $this->epilogue = $str;
  }
  //NODE public function before ($str)
  public function before ($str) {
    $this->before = $str;
  }
  //NODE public function after ($str)
  public function after ($str) {
    $this->after = $str;
  }
  //NODE public function read ()
  public function read () {
    $r = O\s($this->prologue);
    foreach($this->array as $value) { $r .= $this->before . $value . $this->after; }
    $r .= $this->epilogue;
    return $r;
  }
}
//NODE * class UploadedFile
class UploadedFile {
  private $url;
  private $file;
  //NODE function __construct (&$url,&$file)
  function __construct (&$url,&$file) {
    $this->url = $url;
    $this->file = $file;
  }
  //NODE static function format_bytes (int $size)
  static function format_bytes (int $size) {
    if ($size == 0) { return '0 B'; }
    $base = log($size, 1024);
    $suffixes = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
    return round(pow(1024, $base-floor($base)), 2).' '.$suffixes[floor($base)];
  }
  //NODE public function url ()
  public function url () {
    return $this->url . $this->file->relative_name();
  }
  //NODE public function size ()
  public function size () {
    return UploadedFile::format_bytes($this->file->length());
  }
  //NODE public function creation ()
  public function creation () {
    global $settings;
    return $this->file->creation_time()->format($settings['date_format']);
  }
  //NODE public function name ()
  public function name () {
    return $this->file->name();
  }
  //NODE public function html_link ()
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
  //NODE public function html_button ($action,$description)
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
  //NODE public function read ()
  public function read () {
    return $this->html_link() . $this->html_button('delete','delete');
  }
}
//NODE * class UploadedFilesList
class UploadedFilesList {
  private $files;
  private $url;
  //NODE function __construct (&$url, &$files_array)
  function __construct (&$url, &$files_array) {
    $this->url = $url;
    $this->files = $files_array;
  }
  //NODE public function read ()
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
}
//NODE * class RandomName
class RandomName {
  private $name = '';
  //NODE function __construct ($alphabet, $namesize)
  function __construct ($alphabet, $namesize) {
    $name = '';
    while (strlen($name) < $namesize) {
      $name .= $alphabet[mt_rand(0, strlen($alphabet)-1)]; }
    $this->name = O\s($name);
  }
  //NODE public function name ()
  public function name () {
    return $this->name;
  }
}
//NODE * More functions
//NODE function diverse_array ($vector)
// Rotate a two-dimensional array. Used for file uploads
function diverse_array ($vector) {
  $result = [];
  foreach ($vector as $key1 => $value1) {
    foreach ($value1 as $key2 => $value2) {
      $result[$key2][$key1] = $value2; }}
  return $result;
}
//NODE function file_upload_error ($err)
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
//NODE function new_random_filename ($extension)
function new_random_filename ($extension) {
  global $settings;
  $ext = O\s($extension)->trim()
    ->substr(0,$settings['random_name_len']-1)
    ->preg_replace('/[^a-zA-Z0-9_-]/u','');
  if ($ext == '') { $ext = 'txt'; }
  again:
  $newname = new RandomName($settings['random_name_alphabet'], $settings['random_name_len']);
  $nfilename = 'uploads/' . $newname->name() . '.' . $ext;
  if (file_exists($nfilename)) { goto again; }
  return $nfilename;
}
//NODE function redirect_after ($seconds,$url)
function redirect_after ($seconds,$url) {
  $t = new Template('Refresh: {SECS};url={URL}');
  $t->with('{SECS}',$seconds);
  $t->with('{URL}',$url);
  header($t->read(), true, 303);
}
//NODE function upload_file ($file_data)
function upload_file ($file_data) {
  global $settings;
  redirect_after(5,$settings['url']);
  if ($file_data['error'] != UPLOAD_ERR_OK) { file_upload_error($file_data['error']); return; }
  $file = O\f($file_data['tmp_name']);
  $orig = O\f($file_data['name']);
  $nfilename = new_random_filename($orig->extension());
  if ($file->rename_to($nfilename)) {
    echo($settings['url'] . $nfilename . "\n");
  } else {
    echo('Something gone wrong'); }
}
//NODE function delete_file ($filename)
function delete_file ($filename) {
  global $settings;
  redirect_after(2,$settings['url']);
  $file = O\f('uploads/' . $filename);
  if ($file->exists() && O\s($file->path())->pos(O\FileClass::current_dir().'/uploads/') == 0) {
    if ($file->remove()) {
      echo('deleted: '.$file->name());
    } else {
      echo('Something gone wrong'); }
  } else {
    echo('Something gone wrong'); }
}
//NODE * Main code
//NODE ** Delete file case:
if (isset($_POST) && isset($_POST['target']) && isset($_POST['action'])) {
  plain_text();
  switch ($_POST['action']) {
    case 'delete': delete_file($_POST['target']); break;
    default: echo 'Error';
  }
  exit;
}
//NODE ** Upload case:
if (isset($_FILES['file'])) {
  plain_text();
  if (is_array($_FILES['file'])) {
    $file_array = diverse_array($_FILES['file']);
    foreach ($file_array as $file_data) { upload_file($file_data); }
  } else {
    upload_file($_FILES['file']); }
  exit;
}
//NODE ** Default case:
html();
$template = new FileTemplate('upload.html');
$template->with('{LANG}',$settings['lang']);
$template->with('{LANG_DIR}',$settings['lang_dir']);
$template->with('{TITLE}',$settings['title']);
$template->with('{URL}',$settings['url']);
$template->with('{MAX_UPLOAD}',ini_get('upload_max_filesize'));
$template->with('{MAX_FILES}',ini_get('max_file_uploads'));
$uploads = O\f('uploads');
$uploads_file_list = $uploads->list();
$uploaded_files = O\a($uploads_file_list)
  ->map(function ($i) { $f = O\f($i); if (!$f->is_directory()) {return $f;} })
  ->filter(function ($f) { return !is_null($f); });
$ufiles_list = new UploadedFilesList($settings['url'], $uploaded_files);
$template->with('{FILES_LIST}', $ufiles_list->read());
echo($template->read());
