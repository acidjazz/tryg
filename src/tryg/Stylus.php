<?

namespace tryg;

class Stylus {

  public static $stylusdir = '/sty/';

  public static function c($stylus, $return=false) {


    $path = \tryg\Debug::rootPath().self::$stylusdir;

    if (!is_file($path.$stylus)) {
      $stylus = $stylus.'.styl';
    }

    if (!is_file($path.$stylus)) {
      trigger_error('Stylus not found: "'.$path.$stylus.'"');
      return false;
    }

    global $data;

    $options = [];
    $options['paths'] = [$path];
    $options['filename'] = $path.$stylus;
    $options['data'] = $data;

    $data = file_get_contents($path.$stylus);

    $result = Node::post('stylus', 'http://localhost:3000/', $options, $data);

    if ($result['status'] == 500) {

      if (preg_match('/on line ([0-9]+)/i', $result['data'], $matches)) {
        Debug::handler(E_ERROR, '<b>[Stylus]</b> '.$result['data'], $path.$template, $matches[1]);
      } elseif (preg_match('/^(.*?):([0-9]+)/i', $result['data'], $matches)) {
        $lines = explode("\n", trim($result['data']));
        Debug::handler(E_ERROR, '<b>[Stylus]</b> '.end($lines), $matches[1], $matches[2]);
      } else {
        trigger_error("<b>[Stylus]</b> compilation error: <pre>".$result['data']."</pre>");
      }

      return false;

    }

    if ($return) {
      return $result['data'];
    }

    echo $result['data'];

  }

}
