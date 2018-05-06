<?php

class Redirect{

  public static function re(){
    header('Refresh: 0');
    exit();
  }

  public static function to($location){
    if($location){
      header("Location: {$location}");
      exit();
    }
  }

}
