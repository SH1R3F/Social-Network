<?php
require_once 'core/init.php';
$user->logout(false);
Redirect::to('index.php');
