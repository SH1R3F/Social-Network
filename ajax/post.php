<?php
require_once __DIR__ . '/../core/init.php';

/*============================================
==========* Getting Timeline Posts *==========
============================================*/
if(
  isset($_POST['FetchPostsTimeline'], $_POST['csrf'], $_POST['limit'], $_POST['start']) &&
  $_POST['FetchPostsTimeline'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $limit = intval($_POST['limit']);
      $start = intval($_POST['start']);
      $res = $user->getTimeline($limit, $start);
      if($res && count($res)){
        foreach($res as $post){
          $post->isLiked = ($user->isLiked($post->id))? 'liked' : '' ;
        }
      }
      echo json_encode($res);
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}

/*============================================
==========* Getting Someones Posts *==========
============================================*/
if(
  isset($_POST['FetchSomeOneTimeline'], $_POST['user_id'], $_POST['csrf'], $_POST['limit'], $_POST['start']) &&
  $_POST['FetchSomeOneTimeline'] === '1'
){
  $userId = $_POST['user_id'];
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $limit = intval($_POST['limit']);
      $start = intval($_POST['start']);
      $res = $user->getPosts($userId, $limit, $start);
      if($res && count($res)){
        foreach($res as $post){
          $post->isLiked = ($user->isLiked($post->id))? 'liked' : '' ;
        }
      }
      if($res === false){
        $res = array();
      }
      echo json_encode($res);
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}

/*===========================================
============* Publishing a post *============
===========================================*/
if(
  isset($_POST['PublishPost'], $_POST['post'], $_POST['csrf_logout']) &&
  $_POST['PublishPost'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf_logout'])){//csrf checking
    if($user->isLogged()){
      $post = $_POST['post'];
      if(strlen(str_replace(' ', '', $post))){
        if($user->publishPost($post)){
          echo 'Success';
        }else{
          echo 'didnt';
        }
      }else{
        die('emptyPost');
      }
    }else{
      die('not logged');
    }
  }else{
    die('csrf error');
  }
}

/*==========================================
==========* Removing My Own Post *==========
==========================================*/
if(
  isset($_POST['RemoveMyPost'], $_POST['csrf'], $_POST['PostId']) &&
  $_POST['RemoveMyPost'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $id = $_POST['PostId'];
      if($user->removePost($id)){
        echo 'Success';
      }else{
        echo 'NotOwner';
      }
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}

/*=============================================
==========* Liking or Unliking post *==========
=============================================*/
if(
  isset($_POST['Liking'], $_POST['post_id'], $_POST['csrf']) &&
  $_POST['Liking'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $post_id = $_POST['post_id'];
      if(is_numeric($post_id)){
        if($user->actionLike($post_id)){
          echo 'Success';
        }else{
          die('error');
        }
      }else{
        die('Nonumeric');
      }
    }else{
      die('not logged');
    }
  }else{
    die('csrf error');
  }
}

/*======================================
==========* Getting Comments *==========
======================================*/
if(
  isset($_POST['getComments'], $_POST['post_id'], $_POST['csrf'], $_POST['limit'], $_POST['start']) &&
  $_POST['getComments'] === '1'
){
  $postId = $_POST['post_id'];
  $html = '';
  if($user->isLogged() && Token::check('csrf_logged', $_POST['csrf'])){
    $limit = intval($_POST['limit']);
    $start = intval($_POST['start']);
    $res   = $user->getComments($postId, $limit, $start);
    if($res === false){
      $res = array();
    }
    $res = array_reverse($res);
    echo json_encode($res);
  }else{
    die('Error');
  }
}

/*================================================
==========* Getting Number of Comments *==========
================================================*/
if(
  isset($_POST['getCommentsNumber'], $_POST['post_id'], $_POST['csrf']) &&
  $_POST['getCommentsNumber'] === '1'
){
  $postId = $_POST['post_id'];
  $html = '';
  if($user->isLogged() && Token::check('csrf_logged', $_POST['csrf'])){
    echo $user->getCommentsNumber($postId);
  }else{
    die('Error');
  }
}

/*==========================================
==========* Publishing A Comment *==========
==========================================*/
if(
  isset($_POST['PublishComment'], $_POST['comment'], $_POST['post_id'], $_POST['csrf_token']) &&
  $_POST['PublishComment'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf_token'])){//csrf checking
    if($user->isLogged()){
      $comment = $_POST['comment'];
      if($comment){
        $id = $_POST['post_id'];
        if(is_numeric($id)){
          if($user->publishComment($id, $comment)){
            echo 'Success';
          }
        }else{
          die('Nonumeric');
        }
      }else{
        echo 'n';
      }
    }else{
      die('not logged');
    }
  }else{
    die('csrf error');
  }
}

/*=============================================
==========* Removing My Own Comment *==========
=============================================*/
if(
  isset($_POST['RemoveMyComment'], $_POST['csrf'], $_POST['CommentId'], $_POST['PostId']) &&
  $_POST['RemoveMyComment'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $id = $_POST['CommentId'];
      if($user->removeComment($id, $_POST['PostId'])){
        echo 'Success';
      }else{
        echo 'NotOwner';
      }
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}
