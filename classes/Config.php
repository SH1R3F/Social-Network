<?php

class Config{

  public static function get($path){
    if($path){
      $bits = explode('/', $path);
      $config = $GLOBALS['config'];
      foreach($bits as $bit){
        if(isset($config[$bit])){
          $config = $config[$bit];
        }
      }
      return $config;
    }
    return false;
  }


}
