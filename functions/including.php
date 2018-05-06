<?php

function get_header(){
  include_once 'header.php';
}

function get_footer(){
  include_once 'footer.php';
}
function includeIn($included, $where){ // array('index.php', 'login.php');
  foreach($where as $place){
    $page = basename($_SERVER['PHP_SELF']);
    if($place[0] === '!'){
      $place = str_replace('!', '', $place);
      if($page !== $place){
        if(strpos($included, '.js')){
          echo "<script src='js/{$included}'></script>";
        }elseif(strpos($included, '.css')){
          echo "<link href='css/{$included}' rel='stylesheet' />";
        }
      }
    }elseif($page === $place){
      if(strpos($included, '.js')){
        echo "<script src='js/{$included}'></script>";
      }elseif(strpos($included, '.css')){
        echo "<link href='css/{$included}' rel='stylesheet' />";
      }
    }
  }
}
