<?php
require_once __DIR__ . '/../core/init.php';


/*===========================================
==========* Get Num Notifications *==========
===========================================*/
if(
  isset($_POST['GetUnseenNotifications'], $_POST['csrf']) &&
  $_POST['GetUnseenNotifications'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      if($user->getNotifsNumber()){
        echo $user->getNotifsNumber();
      }
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}

/*============================================
==========* Mark Notification Seen *==========
============================================*/
if(
  isset($_POST['SeeMyNotify'], $_POST['csrf'], $_POST['NotifyId']) &&
  $_POST['SeeMyNotify'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $id = $_POST['NotifyId'];
      if($user->sawNotification($id)){
        echo 'Success';
      }
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}

/*===========================================
==========* Get All Notifications *==========
===========================================*/
if(
  isset($_POST['GetMyNotifications'], $_POST['csrf']) &&
  $_POST['GetMyNotifications'] === '1'
){
  if(Token::check('csrf_logged', $_POST['csrf'])){//csrf checking
    if($user->isLogged()){
      $notifications = $user->getMyNotifications($user->data()->id);
      $html = "";
      if($notifications && count($notifications)){
        foreach($notifications as $notif){
          $body = $notif->body;

          if($notif->type === '1'){ // Someone Liked
            $breif = JSON_decode($body, true)['breif'];
            $likers = JSON_decode($body, true)['Likerz'];
            $likers = implode(', ', $likers);
            $body = "<b>{$likers}</b> liked your post: '{$breif}..'";
            $link = "post.php?username=" . $user->data()->username . '&amp;id=' . $notif->post_id;

          }elseif($notif->type === '2'){
            $breif = JSON_decode($body, true)['breif'];
            $commenters = JSON_decode($body, true)['Commenterz'];
            $commenters = implode(', ', $commenters);
            $body = "<b>{$commenters}</b> Commenter on your post: '{$breif}..'";
            $link = "post.php?username=" . $user->data()->username . '&amp;id=' . $notif->post_id;

          }elseif($notif->type === '3'){
            $followers = JSON_decode($body, true)['Followerz'];
            $followers = implode(', ', $followers);
            $body = "<b>{$followers}</b> Followed you.'";
            $link = "profile.php?username=" . $notif->post_id;

          }elseif($notif->type === '4'){
            $json = JSON_decode($body, true);
            $tagger = $json['Tagger'];
            $body = "<b>{$tagger}</b> tagged you in his post.";
            $link = "post.php?username=" . $json['username'] . "&amp;id=" . $notif->post_id;

          }elseif($notif->type === '5'){
            $json = JSON_decode($body, true);
            $tagger = $json['Mentioner'];
            $body = "<b>{$tagger}</b> Mentioned you in a comment.";
            $link = "post.php?" . $json['post_link'];

          }
          $isSeen = ( $notif->state ) ? '' : " class='unseen'" ;
          $html .= "<li><a href='{$link}'{$isSeen} onmouseenter='SeenNotify(".$notif->id.");' style='border-bottom: 1px solid #EEE; padding: 8px 20px;' >{$body}</a></li>";
        }
      }else{
        $html = "<li><a href='#'>You don't have any notifications yet.</a></li>";
      }
      echo $html;
    }else{
      die('Error');
    }
  }else{
    die('Error');
  }
}
