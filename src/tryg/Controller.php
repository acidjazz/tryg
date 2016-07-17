<?php

namespace tryg;

class Controller {

  private $_controller;
  private $_action;
  private $_uri;
  private $_args;
  protected $_browser;
  public static $_space = '\\ctl\\';

  public function __construct($uri) {

    $this->_uri = $uri;
    $params = explode('/', ($pos = strpos($uri, '?')) ?  substr($uri, 0, $pos) : $uri);
    $this->_controller = isset($params[1]) && !empty($params[1]) ? self::$_space.ucfirst($params[1]) : self::$_space.'Index';
    $this->_action = isset($params[2]) && !empty($params[2]) ? $params[2] : 'index';
    $this->_args = isset($params[3]) && !empty($params[3]) ? array_slice($params, 3) : [];

  }

  public function start() {

    if (!class_exists($this->_controller)) {
      if (!class_exists(self::$_space.'Error')) {
        trigger_error('controller class not found: "'.$this->_controller.'" nor  "'.self::$_space.'Error" (404 route placeholder)');
        return false;
      } else {
        //(new \ctl\Error())->index();
        $errorController = self::$_space.'Error';
        (new $errorController())->index();
        return false;
      }
    }
    if (!method_exists($this->_controller, $this->_action) and !method_exists($this->_controller, '__call')) {
      trigger_error('method not found: ['.$this->_controller.'->'.$this->_action.'()]');
      return false;
    }

    call_user_func_array([new $this->_controller(),$this->_action], $this->_args);

  }

}
