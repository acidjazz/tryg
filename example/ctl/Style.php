<?

namespace ctl;

class Style {

  public function __call($function, $arguments) {
    $this->index(substr($function, 0, -4));
  }

  public function index($file='main') {

    global $cfg;

    if (preg_match('/^[a-z]+$/', $file) && is_file($cfg['path'].'sty/'.$file.'.styl')) {

      if (!$css = \tryg\Stylus::c($file, true)) {
        Header('Content-type: text/html');
        echo $css;
      } else {
        Header('Content-type: text/css', true);
        Header('X-Content-Type-Options: nosniff');
        echo $css;
      }

    }

  }

}
