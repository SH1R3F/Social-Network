<?php
require_once __DIR__ . '/../core/init.php';

/*===========================================
============* Requesting Follow *============
===========================================*/
if(
  isset($_POST['following'], $_POST['username'], $_POST['csrf']) &&
  $_POST['following'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      if($user->find('users', $_POST['username'])){
        if($user->follow($_POST['username'])){
          echo 'Success';
        }else{
          echo 'allready followed';
        }
      }else{
        die('no real followed account');
      }
    }else{
      die('not logged');
    }
  }else{
    die('csrf error');
  }
}
