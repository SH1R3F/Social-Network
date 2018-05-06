<?php

class Cookie{

  public static function get($name){
    return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : false ;
  }

  public static function put($name, $value, $expiry){
    return (setcookie($name, $value, $expiry, '/')) ? true : false ;
  }

  public static function delete($name){
    return self::put($name, '', time()-1);
  }

}
