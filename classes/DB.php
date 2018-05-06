<?php

class DB{
  private static $_instance = null;
  private $_pdo = null,
          $_query = null,
          $_count = 0,
          $_error = false,
          $_result = null;

  public function __construct(){
    $host = Config::get("database/host");
    $name = Config::get("database/name");
    $user = Config::get("database/user");
    $pass = Config::get("database/pass");
    try{
      $this->_pdo = new PDO("mysql:host={$host};dbname={$name}", $user, $pass);
    }catch(PDOException $e){
      die($e->getMessage());
    }
  }

  public static function getInstance(){
    if(!self::$_instance){
      self::$_instance = new DB();
    }
    return self::$_instance;
  }

  public function query($sql, $params = array()){
    if($this->_query = $this->_pdo->prepare($sql)){
      if(count($params)){
        $x = 1;
        foreach($params as $param){
          $this->_query->bindValue($x, $param);
          $x++;
        }
      }
      if($this->_query->execute()){
        $this->_count = $this->_query->rowCount();
        $this->_result = $this->_query->fetchAll(PDO::FETCH_OBJ);
      }else{
        $this->_error = true;
      }
      return $this;
    }
    return false;
  }

  public function get($get, $table, $where = array(), $extra = '', $operator = '='){
    if(count($where)){
      $str = "";
      foreach($where as $col=>$val){
        $str .= "{$col} {$operator} ? AND ";
      }
      $str = rtrim($str, "AND ");
      $sql = "SELECT {$get} FROM {$table} WHERE {$str}";
    }else{
      $sql = "SELECT {$get} FROM {$table}";
    }
    if($extra === 'desc_id'){
      $sql .= " ORDER BY id DESC";
    }elseif($extra === 'desc_id limit_30'){
      $sql .= " ORDER BY id DESC LIMIT 30";
    }
    return $this->query($sql, $where);
  }

  public function insert($table, $values = array()){
    if(count($values)){
      $fields = "";
      $qs = "";
      foreach($values as $col=>$val){
        $fields .= "{$col}, ";
        $qs .= "?, ";
      }
      $fields = rtrim($fields, ", ");
      $qs = rtrim($qs, ", ");
      $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$qs})";
      return $this->query($sql, $values);
    }
    return false;
  }

  public function update($table, $where = array(), $values = array(), $operator = '='){
    if(count($where)){
      $str = "";
      foreach($where as $col=>$val){
        $str .= "{$col} {$operator} {$val} AND ";
      }
      if(count($values)){
        $str2 = "";
        foreach($values as $col=>$val){
          $str2 .= "{$col} = ?, ";
        }
        $str2 = rtrim($str2, ', ');
        $str = rtrim($str, 'AND ');
        $sql = "UPDATE {$table} SET {$str2} WHERE {$str}";
        if($this->query($sql, $values)){
          return true;
        }
      }
    }
    return false;
  }

  public function delete($table, $where = array(), $operator = '='){
    if(count($where)){
      $str = "";
      foreach($where as $col=>$val){
        $str .= "{$col} {$operator} ? AND ";
      }
      $str = rtrim($str, ' AND ');
      $sql = "DELETE FROM {$table} WHERE {$str}";
      if($this->query($sql, $where)){
        return true;
      }
    }
    return false;
  }

  public function error(){
    return $this->_error;
  }

  public function count(){
    return $this->_count;
  }

  public function result(){
    return $this->_result;
  }

  public function first(){
    return $this->result()[0];
  }

}
