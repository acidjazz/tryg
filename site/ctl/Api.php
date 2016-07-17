<?php

namespace ctl;

Class Api extends \tryg\Aphpi {

  public $user = false;

  public function __construct() {
    // placeholder to verify logged-in users and store info in $this->user
    parent::__construct();
  }

  public function __call($method, $args) {

    if (isset($args[0]) && !empty($args[0])) {
      $method = $method.ucfirst($args[0]);
    }

    switch ($method) {

      // public methods
      case 'index' :
      case 'login' :
        call_user_func_array([$this, '_'.$method], []);
        break;

      // login required methods
      case 'logout' :
        if ($this->user == false) {
          $this->error('role', 'Requires the session token of a currently logged in user');
          return $this->result(false);
        }

        call_user_func_array([$this, '_'.$method], []);
        break;

      default :
        $this->error('endpoint', 'Endpoint not found or unavailable: ' . $method);
        $this->result(false);
        break;

    }

  }

  private function _index() {
    return $this->result(true, 'Index Endpoint Successful', ['Welcome to Tryg']);
  }

  private function _login() {

    if (!$data = $this->required(['email', 'password'])) {
      return false;
    }

  }

}
