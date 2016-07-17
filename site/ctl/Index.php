<?php

namespace ctl;

class Index {

  public function __construct() {
  }

  public function index() {
    \tryg\Pug::c('index');
  }

}

