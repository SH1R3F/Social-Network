<?php
session_start();

$GLOBALS['config'] = array(
  'site' => array(
    'name' => 'FaceAbok'
  ),
  'database' => array(
    'host' => 'localhost', // your database informations
    'name' => 'SocialNetwork1',
    'user' => 'root',
    'pass' => 'toor',
  ),
  'cookies' => array(
    'login_token' => 'SocialNetwork',
    'second_token' => 'SocialNetwork_',
    'logged' => 'user_id'
  ),
  'sessions' => array(
    'user_login' => 'login_user_id',
    'user_token' => 'forms_token'
  )
);


spl_autoload_register(function($class){
  require_once __DIR__ . "/../classes/{$class}.php";
});



$user = new User();
$page = basename($_SERVER['PHP_SELF']);
if($user->isLogged()){
  if(
    $page === 'login.php'            ||
    $page === 'register.php'         ||
    $page === 'forget-password.php'  ||
    $page === 'restore-password.php'
  ){
    Redirect::to('index.php');
  }
}elseif(
    $page === 'index.php'    ||
    $page === 'profile.php'  ||
    $page === 'post.php'     ||
    $page === 'settings.php' ||
    $page === 'notifications.php' ||
    $page === 'search.php' ||
    $page === 'logout.php' ||
    $page === 'topic.php'
  ){
  Redirect::to('login.php');
}

foreach(glob("functions/*.php") as $function){
  require_once $function;
}
