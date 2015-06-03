<?

/* mongoDB simple active record layer */

namespace tryg;

class Model {

  private static $_col = array();
  private static $_db = false;
  private static $_grid = false;
  private $_data = array();

  public $_exists = false;

  public function __construct($id=null) {

    // model matching data passed in
    if (is_array($id) && count($id) > 0) {
      $this->_data = $id;
    } else if(is_object($id) && ($id instanceof \MongoId)) {
    // mongoid has been passed in
      $this->_data = self::col()->findOne(array('_id' => $id));
    } else if (is_string($id) && strlen($id) == 24) {
      // non-mongo mongoid has been passed in, try it out
      $this->_data = self::col()->findOne(array('_id' => new \MongoId($id)));
    } else {
      // custom mongoid string passed in, one more shot
      $this->_data = self::col()->findOne(array('_id' => $id));
    }

    if (is_array($this->_data) && count($this->_data) > 0) {
      $this->_exists = true;
    } elseif ($id != null) {
      $this->_data['_id'] = $id;
    }

  }

  public static function validId($id) {

    if (preg_match('/^[0-9a-z]{24}$/', $id)) {
      return true;
    }

    return false;
  }

  public static function i($data) {

    $class = '\\mdl\\'.self::getcol();
    $that = new $class;
    $that->_data = $data;

    if ($data != null) {
      $that->_exists = true;
    }

    return $that;
  }

  public static function getcol() {
    $class = get_called_class();
    return substr($class, strrpos($class, '\\') + 1);
  }

  public static function getdb() {

    global $cfg;

    if (isset($cfg['mongo']['db'])) {
      return $cfg['mongo'];
    }

    foreach ($cfg['mongo'] as $key=>$db) {

      if (in_array(self::getcol(), $db['cols'])) {
        return $db;
      }
    }

    return false;

  }

  public static function db() {

    if (!self::$_db) {

      $dbinfo = self::getdb();

      if (isset($dbinfo['replicaSet'])) {
        $mongo = new \MongoClient($dbinfo['host'], array('replicaSet' => $dbinfo['replicaSet']));
      } else {
        $mongo = new \MongoClient($dbinfo['host']);
      }

      self::$_db = $mongo->{$dbinfo['db']};


    }

    return self::$_db;
  }

  public static function grid() {
    if (!self::$_grid) {
      self::$_grid = new \MongoGridFS(self::db());
    }
    return self::$_grid;
  }

  public static function col() {

    if (!isset(self::$_col[self::getcol()])) {
      self::$_col[self::getcol()] = self::db()->{self::getcol()};
    }

    return self::$_col[self::getcol()];
  }

  public function __get($name) {

    if (isset($this->_data[$name])) {
      return $this->_data[$name];
    }
  }

  public function __isset($name) {
    return isset($this->_data[$name]);
  }

  public function __unset($name) {

    if (isset($this->_data[$name])) {
      unset($this->_data[$name]);
      return true;
    }

    return false;
  }

  public function __set($name, $value) {

    if (isset($this->_types) && in_array($name, array_keys($this->_types))) {

      switch ($this->_types[$name]) {

        case 'id' :
          if (is_object($value) && ($value instanceof \MongoId)) {
            return $this->_data[$name] = $value;
          } else {
            return $this->_data[$name] = new \MongoID($value);
          }

        case 'date' :
          if (is_object($value) && ($value instanceof \MongoDate)) {
            return $this->_data[$name] = $value;
          } else {
            return $this->_data[$name] = new \MongoDate($value);
          }

        case 'binary' :
          if (is_object($value) && ($value instanceof \MongoDate)) {
            return $this->_data[$name] = new \MongoBinData($value);
          } else {
            return $this->_data[$name] = $value;
          }

      }
    }

    return $this->_data[$name] = $value;
  }

  public static function __callStatic($name, $args) {
    return call_user_func_array(array(self::col(), $name), $args);
  }

  public function save($data=false, $options=array()) {

    if ($data != false) {
      foreach ($data as $key=>$value) {
        $this->$key = $value;
      }
    }

    return self::col()->save($this->_data,$options);

  }

  public function remove($data=false, $options=array()) {

    if ($data != false) {
      if (self::col()->remove($data, $options)) {
        unset($this->_data);
        return true;
      }
    }


    if (isset($this->_data) && self::col()->remove($this->_data, $options)) {
      unset($this->_data);
      return true;
    }

    return self::col()->remove();

  }

  public function data($rawid=false) {

    $data = $this->_data;

    if (isset($this->_ols) && is_array($this->_ols)) {
      foreach ($this->_ols as $ol) {
        $data[$ol] = $this->$ol;
      }
    }

    if ($rawid) {
      $data['_id'] = $data['_id']->{'$id'};
    }

    return $data;

  }

  public function id($raw=false) {

   if (!isset( $this->_data['_id'])) {
     return false;
   }

   if ($raw) {
     return $this->_data['_id']->{'$id'};
   }

   return $this->_data['_id'];

  }

  public function exists() {
    return $this->_exists;
  }

}
