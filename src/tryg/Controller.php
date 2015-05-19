<?

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
      trigger_error('controller not found: ['.$this->_controller.']');
      return false;
    }
    if (!method_exists($this->_controller, $this->_action) and !method_exists($this->_controller, '__call')) {
      trigger_error('method not found: ['.$this->_controller.'->'.$this->_action.'()]');
      return false;
    }

    call_user_func_array(array(new $this->_controller(),$this->_action), $this->_args);

  }

}
