<?php
require_once __DIR__ . '/../core/init.php';


/*===========================================
============* Changing Password *============
===========================================*/
if(
  isset($_POST['change_password'], $_POST['oldpass'], $_POST['newpass'], $_POST['confpass'], $_POST['csrf_pass']) &&
  $_POST['change_password'] === '1'
){
  if(Token::check('csrf_pass', $_POST['csrf_pass'])){
    if($user->isLogged()){
      $oldpass  = $_POST['oldpass'];
      $newpass  = $_POST['newpass'];
      $confpass = $_POST['confpass'];

      if($newpass !== $confpass){
        die('notMatches');
      }elseif(//validate passwords
        strlen($confpass) < 4 || strlen($newpass) < 4 || //Min
        strlen($confpass) > 40 || strlen($newpass) > 40 //Max
      ){
        die('notValid');
      }elseif($newpass === $oldpass){
        die('newEqold');
      }
      if($user->changepassword($oldpass, $newpass)){
        echo 'Success';
      }else{
        echo 'wrongOld';
      }
  }else{
    echo 'token err';
  }
  }else{
    echo 'Error';
  }
}


/*==========================================
==========* Changing Information *==========
==========================================*/
if(
  isset($_POST['changeInfo'], $_POST['name'], $_POST['csrf_name']) &&
  $_POST['changeInfo'] === '1' &&
  Token::check('csrf_name', $_POST['csrf_name'])
){
  if($user->isLogged()){
    $name = $_POST['name'];
    $bio = (strlen(str_replace(' ', '', $_POST['bio'])))? $_POST['bio'] : '' ;
    $town = (strlen(str_replace(' ', '', $_POST['town'])))? preg_replace('/\s+/', ' ', $_POST['town']) : '' ;
    $website = (strlen(str_replace(' ', '', $_POST['website'])))? preg_replace('/\s+/', ' ', $_POST['website']) : '' ;
    if(strlen($name) < 2 || strlen($name) > 20 || strlen(str_replace(' ', '', $name)) === 0){
      die('notValid');
    }
    if($website !== ''){
      if(!filter_var($website, FILTER_VALIDATE_URL)){
        die('urlnotvalid');
      }
    }
    if(
      $user->changeValue('name', htmlspecialchars($name)) &&
      $user->changeValue('bio', $user->FilteringText($bio)[0]) &&
      $user->changeValue('town', htmlspecialchars($town)) &&
      $user->changeValue('website', htmlspecialchars($website))
    ){
      echo 'Success';
    }else {
      die('test');
    }
  }else{
    die('Error');
  }
}
