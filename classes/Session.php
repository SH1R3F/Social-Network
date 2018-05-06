<?php
class Session{

  public static function exists($name){
    return (isset($_SESSION[$name])) ? true : false ;
  }

  public static function get($name){
    return (isset($_SESSION[$name])) ? $_SESSION[$name] : false ;
  }

  public static function delete($name){
    if(isset($_SESSION[$name])){
      unset($_SESSION[$name]);
    }
  }

  public static function put($name, $value){
    return $_SESSION[$name] = $value;
  }

  public static function flash($name, $message = ''){
    if($message){
      self::put($name, $message);
    }else{
      $msg = self::get($name);
      self::delete($name);
      return $msg;
    }
  }

}
?>
