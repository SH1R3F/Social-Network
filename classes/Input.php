<?php


class Input{

  public static function exists($type = 'POST'){
    switch($type){
      case 'POST':
        return (!empty($_POST))? true : false ;
      break;
      case 'GET':
        return (!empty($_GET))? true : false ;
      break;
      default:
        return false;
      break;
    }
  }

  public static function get($name, $type = 'POST'){
    switch($type){
      case 'POST':
        return (isset($_POST[$name]))? htmlspecialchars($_POST[$name]) : false ;
      break;
      case 'GET':
        return (isset($_GET[$name]))? htmlspecialchars($_GET[$name]) : false ;
      break;
      default:
        return false;
      break;
    }
  }

}
