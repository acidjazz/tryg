<?

namespace tryg;

class Pug {

  public static $templatedir = '/tpl/';

  public static function c($template, $array=array(), $return=false) {

    global $data;

    $path = \tryg\Debug::rootPath().self::$templatedir;

    $tryg = __DIR__.self::$templatedir;

    $array['d'] = $data;
    $array['path'] = \tryg\Debug::rootPath();
    $array['pretty'] = true;
    $array['self'] = true;

    foreach (['s' => isset($_SESSION) ? $_SESSION : [], 'g' => $_GET, 'p' => $_POST, 'r' => $_REQUEST] as $k=>$v) {
      $array['_'.$k] = $v;
    }

    if (!is_file($path.$template) && !is_file($tryg.$template)) {
      $template = $template.'.pug';
    }

    $file = false;

    if (is_file($path.$template)) {
      $file = $path.$template;
    } elseif (is_file($tryg.$template)) {
      $file = $tryg.$template;
    }

    if (!$file) {
      trigger_error('Template not found: "'.$path.$template.'" or "'.$tryg.$template."'");
      return false;
    }

    $result = Node::post('pug', 'http://localhost:4200/', ['file' => $file], $array);

    if ($result['status'] == 500) {

      if (preg_match('/on line ([0-9]+)/i', $result['data'], $matches)) {
        Debug::handler(E_ERROR, '[Pug] '.$result['data'], $path.$template, $matches[1]);
      } elseif (preg_match('/^(.*?):([0-9]+)/i', $result['data'], $matches)) {
        $lines = explode("\n", trim($result['data']));
        Debug::handler(E_ERROR, '[Pug] '.end($lines), $matches[1], $matches[2]);
      } else {
        trigger_error("<b>[Pug]</b> compilation error: <pre>".$result['data']."</pre>");
      }

      return false;

    }

    if ($return) {
      return $result['data'];
    }

    echo $result['data'];

  }

}
