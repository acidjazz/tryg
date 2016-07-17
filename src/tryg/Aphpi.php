<?php

namespace tryg;

class Aphpi {

  public $microtime = false;

  public $errors = [];

  public function __construct() {

    $this->microtime = microtime(true);

    Header('Content-type: application/json');
    define('KDB_JSON', true);

  }

  protected function required($fields) {

    $data = [];

    foreach ($fields as $field) {

      if (!isset($_REQUEST[$field])) {
        $this->error('Required', "Missing Field \"$field\"");
      } else {
        $data[$field] = $_REQUEST[$field];
      }

    }

    if (count($this->errors) > 0) {
      $this->result(false);
      return false;
    }

    return $data;

  }

  protected function error($type, $error) {

    $this->errors[] = [
      'type' => $type,
      'error' => $error
    ];

    return false;

  }

  protected function result($success, $result=null, $data=[], $errors=[]) {

    // 1st catch and report any internal errors
    if (isset(Debug::$errors) && count(Debug::$errors) > 0) {
      $success = false;
      return false;
    }

    $status = 200;
    if (!$success) {
      $status = 400;
      $result = 'Error(s)';
    }

    http_response_code($status);

    $return = [
      'status' => $status,
      'result' => $result,
      'benchmark' => microtime(true)-$this->microtime,
      'data' => $data,
      'errors' => $this->errors
    ]; 
    
    echo json_encode($return, JSON_PRETTY_PRINT);

    return true;

  }

}
