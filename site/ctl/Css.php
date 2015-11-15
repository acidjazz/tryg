<?php

namespace ctl;

class Css {

  public function __call($function, $arguments) {
    $this->index(substr($function, 0, -4));
  }

  public function index($file='main') {

    if (preg_match('/^[a-z]+$/', $file) && is_file(__DIR__.'/../sty/'.$file.'.styl')) {

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
