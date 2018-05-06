<?php
require_once __DIR__ . '/../core/init.php';



/*============================================
===============* Get The Chat *===============
============================================*/
if(
  isset($_POST['showOurChat'], $_POST['csrf'], $_POST['username'], $_POST['limit'], $_POST['offset']) &&
  $_POST['showOurChat'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $limit = intval($_POST['limit']);
      $offset = intval($_POST['offset']);

      $username = $_POST['username'];
      if(!is_numeric($username)){
        $f = $user->find('users', $username, 'username');
        if($f && $f->found()){
          $id = $f->found()->id;
        }else{
          die('usernotfound');
        }
      }else{
        $f = $user->find('users', $username, 'id');
        if($f && $f->found()){
          $id = $f->found()->id;
        }else{
          die('usernotfound');
        }
      }
      if(is_numeric($id)){
        if($user->getHisChat($id, $limit, $offset) === false){
          echo json_encode(array());
        }else{
          $res = $user->getHisChat($id, $limit, $offset);
          $res = array_reverse($res);
          echo json_encode($res);
        }
      }else{
        die('Error1');
      }
    }else{
      die('Error2');
    }
  }else{
    die('csrf');
  }
}


/*==================================================
===============* Send A New Message *===============
==================================================*/
if(
  isset($_POST['sendAMessage'], $_POST['csrf'], $_POST['to'], $_POST['body']) &&
  $_POST['sendAMessage'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $body = $_POST['body'];
      if(str_replace(' ', '', $body)){
        $user_id = $_POST['to'];
        if(!is_numeric($user_id)){
          $f = $user->find('users', $user_id, 'username');
          if($f && $f->found()){
            $toId = $f->found()->id;
          }
        }else{
          $f = $user->find('users', $user_id, 'id');
          if($f && $f->found()){
            $toId = $f->found()->id;
          }
        }
        if($user->sendMessage($toId, $body)){
          echo 'Success';
        }else{
          die('Error');
        }
      }else{
        die('emptyMsg');
      }
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}
/*============================================
===============* Get The Name *===============
============================================*/
if(
  isset($_POST['GetMsgrName'], $_POST['csrf'], $_POST['username']) &&
  $_POST['GetMsgrName'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $username = $_POST['username'];
      if(!is_numeric($username)){
        $f = $user->find('users', $username, 'username');
        if($f && $f->found()){
          $id = $f->found()->id;
        }
      }else{
        $f = $user->find('users', $username, 'id');
        if($f && $f->found()){
          $id = $f->found()->id;
        }
      }
      if($user->getHisName($id)){
        echo 'Success:'.$user->getHisName($id);
      }else{
        die('NoUsr');
      }
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}

/*=====================================================
===============* Setup Side Messengers *===============
=====================================================*/
if(
  isset($_POST['setupMessengers'], $_POST['csrf']) &&
  $_POST['setupMessengers'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $messengers = $user->getMyMessengers();
      if($messengers === false){
        $messengers = array();
      }
      echo json_encode($messengers);
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}

/*==============================================
===============* Make Chat Read *===============
==============================================*/
if(
  isset($_POST['SeenMessages'], $_POST['csrf'], $_POST['user_id']) &&
  $_POST['SeenMessages'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $user_id = $_POST['user_id'];
      if(!is_numeric($user_id)){
        $f = $user->find('users', $user_id, 'username');
        if($f && $f->found()){
          $id = $f->found()->id;
        }
      }else{
        $f = $user->find('users', $user_id, 'id');
        if($f && $f->found()){
          $id = $f->found()->id;
        }
      }
      if($user->makeSeen($id)){
        echo 'Success';
      }
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}

/*===============================================
===============* Make Me Offline *===============
==============================================*/
if(
  isset($_POST['MakeMeOffline'], $_POST['csrf']) &&
  $_POST['MakeMeOffline'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      if(DB::getInstance()->update('users', array('id' => $user->data()->id), array('active_state' => '0'))){
        echo 'Success';
      }
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}
