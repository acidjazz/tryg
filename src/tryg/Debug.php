<?

namespace tryg;

class Debug {

  public static $errors = [];
  public static $html = false;
  public static $window = 8;

  const MAX_ERRORS = 10;

	private static $_etypes = [
		E_ERROR							=> 'Error',
		E_WARNING						=> 'Warning',
		E_PARSE							=> 'Parsing Error',
		E_NOTICE        	  => 'Notice',
		E_CORE_ERROR				=> 'Core Error',
		E_CORE_WARNING			=> 'Core Warning',
		E_COMPILE_ERROR			=> 'Compile Error',
		E_COMPILE_WARNING		=> 'Compile Warning',
		E_USER_ERROR				=> 'User Error',
		E_USER_WARNING			=> 'User Warning',
		E_USER_NOTICE  		  => 'User Notice',
		E_STRICT        	  => 'Runtime Notice',
		E_RECOVERABLE_ERROR => 'Recoverable Error',
		E_DEPRECATED				=> 'Deprecated',
		E_USER_DEPRECATED		=> 'User Deprecated',
		420									=> 'KDB'
	];

  public static function shutdown() {

    if (is_null($error = error_get_last()) === false) {
      self::handler($error['type'], $error['message'], $error['file'], $error['line']);
    }

    global $cfg;

    if (count(self::$errors) > 0) {

      /*if(
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      */

      if (defined('KDB_JSON')) {

        Header('Content-type: application/json');
        http_response_code(400);

        $return = [
          'status' => 400,
          'result' => 'Internal',
          'errors' => self::$errors
        ];

        echo json_encode($return, JSON_PRETTY_PRINT);

      } else {

        self::$html = Jade::c('Debug', ['errors' => self::$errors], true);
        echo self::$html;

      }

    }

  }

  public static function handler($eno, $string, $file, $line) {


    if (count(self::$errors) > self::MAX_ERRORS) {

      self::$errors[] = [
        'type' => self::$_etypes[420],
        'error' => 'Maximum error(s) hit of '.self::MAX_ERRORS,
        'file' => false,
        'line' => false,
        'code' => false
      ];

      exit;
      return false;

    }

    $lines = explode('<br />', highlight_file($file, true));

    $code = [];

	  for ($i = (($line-self::$window < 1) ? 1 : $line-self::$window); $i != $line+self::$window; $i++) {

      if (isset($lines[$i])) {
        $code[$i+1] = $lines[$i];
      }

    }

    $error = [
      'type' => self::$_etypes[$eno],
      'error' => $string,
      'file' => $file,
      'line' => $line,
      'code' => $code
    ];

    //hpr($error);

    self::$errors[] =  $error;

  }

  public static function errors() {

    $errors = [];

    foreach (self::$errors as $error) {
      unset($error['code']);
      $errors[] = $error;
    }

    return $errors;

  }

  public static function def($data) {
    $GLOBALS['data'] = $data;
  }

  public static function rootPath() {
    $path = substr($_SERVER['DOCUMENT_ROOT'], 0, strrpos($_SERVER['DOCUMENT_ROOT'], '/'));
    return $path;
  }

  // slurp a bunch of json and yaml from a directory
  //
  public static function slurp($path, $content=[]) {

    foreach(scandir($path) as $file) {

      if (in_array($file, ['.','..'])) {
        continue;
      }

      if (is_dir($path.$file)) {
        $newpath = $path.$file.'/';
        $content[$file] = self::slurp($newpath, []);
        continue;
      }

      list($name, $ext) = explode('.', $file);

      if (in_array($ext, ['json','jsn'])) {

        $parsed = json_decode(file_get_contents($path.$file), true);

        if ($parsed == null) {
          self::handler(E_USER_NOTICE, 'Error parsing JSON', $path.$file, 4);
          return false;
        } else {
          $content[$name] = $parsed;
        }

      }

      if (in_array($ext, ['yaml','yml'])) {
        $parsed = yaml_parse_file($path.$file);
        $content[$name] = $parsed;
      }

    }

    return $content;
  }

  public static function hpr($obj, $return=false) {

    if (defined('KDB_JSON')) {
      echo json_encode($obj, true);
      exit();
      return true;
    }

    $output = '';

    if (PHP_SAPI != 'cli') {

    $output = <<<HTML
    <pre style="
      font-size: 13px;
      font-family: 'lucida grande', tahoma, verdana, arial, sans-serif;
      color: #333;
      border: 1px solid #d0d0d0; 
      background-color: #efefef;
      border-radius: 5px;
      margin: 5px; 
      padding: 5px;
    ">
HTML;

  }

    ob_start();
    var_dump($obj);
    $output .= ob_get_contents();
    ob_end_clean();
    
    if (PHP_SAPI != 'cli') {
      $output .= '</pre>';
    }

    if ($return == true) {
      return $output;
    }

    echo $output;

  }


}
