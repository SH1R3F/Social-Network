<?php
class User{
  private $_db = null,
          $_found = null,
          $_data = null,
          $_hash = null,
          $_SessionName = null,
          $_SessionToken = null,
          $_userData = null,
          $_isLogged = false;

  public function __construct(){
    $this->_db = DB::getInstance();
    $this->_hash = new PasswordHash(8, false);
    $this->_SessionName  = Config::get('sessions/user_login');
    $this->_SessionToken = Config::get('sessions/user_token');


    if(Session::exists($this->_SessionName)){
      if($this->find('users', Session::get($this->_SessionName))){
        $this->_data = $this->found();
        $this->_isLogged = true;
      }
    }
    if($this->isLogged()){

      if(!Session::get($this->_SessionToken)){
        Session::put($this->_SessionToken,  md5(uniqid(rand(), true)));
      }

      if($this->_db->get('active_state', 'users', array('id'=>$this->data()->id))->first()->active_state === '0'){//mark me as online
        $this->_db->update('users', array('id' => $this->data()->id), array('active_state' => '1'));
      }
    }
  }



/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~* Dealing With Users. [ Registering Users, Logging Users In,    *~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~* Logging Out, Changing Password, Changing Basic Informations ] *~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
  public function create($table, $values){
    return $this->_db->insert($table, $values);
  }

  public function find($table, $input, $fixedField = null){
    if(!$fixedField){ // So If I Wanted To Find A User By Specific Field Not By Auto Find
      if(strpos($input, '@')){
        $field = 'email';
      }elseif(is_numeric($input)){
        $field = 'id';
      }else{
        $field = 'username';
      }
    }else{
      $field = $fixedField;
    }
    $query = $this->_db->get('*', 'users', array($field => $input));
    if(!$query->error()){
      if($query->count()){
        $this->_found = $query->first();
        return $this;
      }
    }
    return false;
  }

  public function login($table, $credint, $password){
    if($this->find($table, $credint)){
      $hashed = $this->found()->password;
      if($this->_hash->CheckPassword($password, $hashed)){
        $this->_data = $this->found();
        Session::put($this->_SessionName, $this->data()->id);

        //make him active
        $this->_db->update('users', array('id' => $this->data()->id), array('active_state' => '1'));

        //csrf token
        Session::put($this->_SessionToken,  md5(uniqid(rand(), true)));

        return true;
      }
    }
    return false;
  }

  public function logout($allDevices = false){
    if($this->isLogged()){
      $this->_db->update('users', array('id' => $this->data()->id), array('active_state' => '0'));
      Session::delete($this->_SessionName);
      return true;
    }
    return false;
  }

  public function Search($string){
    if($this->isLogged()){
      $string = "%{$string}%";
      $query = $this->_db->query('SELECT name, username, image_url FROM users WHERE name LIKE ? OR username LIKE ?', array($string, $string));
      if(!$query->error()){
        return $query->result();
      }
    }
    return false;
  }

  public function changepassword($oldpass, $newpass){
    if($this->isLogged()){
      $data = $this->data();
      if($this->_hash->CheckPassword($oldpass, $data->password)){
        $newhash = $this->_hash->HashPassword($newpass);
        if($this->_db->update('users', array('id' => $data->id), array('password' => $newhash))){
          return true;
        }
      }
    }
    return false;
  }

  public function changeValue($col, $val){
    if($this->isLogged()){
      if($this->_db->update('users', array('id'=>$this->data()->id), array($col=>$val))){
        return true;
      }
    }
    return false;
  }

  /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
  /*~~~~~~~~~~~~~~~~~~~~* Restoring Passwords *~~~~~~~~~~~~~~~~~~~~~*/
  /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
  public function restorePassword($credint){
    if(!$this->isLogged()){
      $f = $this->find('users', $credint, 'email');
      if($f && $f->found()){
        $cstrong = true;
        $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
        $hashed = hash('sha256', $token);
        $q = $this->_db->get('id', 'passwords_tokens', array('user_id' => $f->found()->id));
        if($q && $q->count()){
          $this->_db->delete('passwords_tokens', array('user_id' => $f->found()->id));
        }
        if($this->_db->insert('passwords_tokens', array(
          'token' => $hashed,
          'user_id' => $f->found()->id
        ))){
          return $token; // send token to mail
        }
      }
    }
    return false;
  }

  public function checkToken($token){
    $hash = hash('sha256', $token);
    $q = $this->_db->get('*', 'passwords_tokens', array('token' => $hash));
    if($q && $q->count()){
      return $q->first()->user_id;
    }
    return false;
  }

  public function resetPassword($userId, $newpass){
    $hashed = $this->_hash->HashPassword($newpass);
    if($this->_db->update('users', array('id' => $userId), array('password' => $hashed))){
      $sql = "DELETE FROM passwords_tokens WHERE user_id = ?";
      if($this->_db->query($sql, array($userId))){
        return true;
      }
    }
    return false;
  }

/*------------------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------------------*/

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~* Some Following Stuff. [ Following Users & Unfollowing, Check Following Existence ] *~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
  public function following($id){
    if($this->isLogged()){
      $query = $this->_db->get('id', 'followers', array('follower_id' => $id));
      if(!$query->error()){
        return $query->count();
      }
    }
    return false;
  }

  public function followers($id){
    if($this->isLogged()){
      $query = $this->_db->get('id', 'followers', array('user_id' => $id));
      if(!$query->error()){
        return $query->count();
      }
    }
    return false;
  }

  public function isFollowed($id){
    if($this->isLogged()){
      $query = $this->_db->get('id', 'followers', array('user_id' => $id, 'follower_id' => $this->data()->id));
      if(!$query->error()){
        return $query->count();
      }
    }
    return false;
  }

  public function follow($username){
    $id = $this->find('users', $username)->found()->id;
    if($this->isLogged() && $this->data()->id !== $id){
      if(!$this->isFollowed($id)){
        if($this->_db->insert('followers', array(
          'user_id' => $id,
          'follower_id' => $this->data()->id
        ))){
          //send Notification
          $user_idd = $id;
          $post_idd = $this->data()->id; // post id will be the follower id
          $type = 3; // Type three for Follows
          if($this->getNotification($user_idd, $post_idd, $type)){ //if true then it a record exists by another guy
            $body = $this->getNotification($user_idd, $post_idd, $type)->body;
            $json = JSON_decode($body, true);
            array_unshift($json['Followerz'], $this->data()->name);
            $json = JSON_encode($json);
            $this->_db->update('notifications', array(
              'user_id' => $user_idd,
              'post_id' => $post_idd,
              'type' => $type,
            ), array('body' => $json, 'state' => 0));
          }else{// no notification found. You are a new Follower
            $body = array(
              'Followerz' => array(
                $this->data()->name
              )
            );
            $json = JSON_encode($body);
            $this->sendNotification($user_idd, $post_idd, $type, $json);
          }
          return true;
        }
      }else{
        if($this->_db->delete('followers', array(
          'user_id' => $id,
          'follower_id' => $this->data()->id
        ))){
          //remove notify
          $user_idd = $id;
          $post_idd = $this->data()->id; // post id will be the follower id
          $type = 3;
          // We now need to remove my name from notifications or remove the whole notification if I'm the only liker
          // If the liker is the owner of the post don't send him notification back
          if($this->getNotification($user_idd, $post_idd, $type)){ // This must be true always. but I put to avoid errors anyway while debugging
            $notify = $this->getNotification($user_idd, $post_idd, $type);
            $body = JSON_decode($notify->body, true);
            if(in_array($this->data()->name, $body['Followerz'])){ // because I safe three names only
              //but if he is the only user~ then delete the whole record.
              if(count($body['Followerz']) === 1){
                $this->_db->delete('notifications', array(
                  'user_id' => $user_idd,
                  'post_id' => $post_idd,
                  'type' => $type
                ));
              }else{ // else if Follower name is stored then remove him
                $key = array_search($this->data()->name, $body['Followerz']);
                unset($body['Followerz'][$key]);
                $json = JSON_encode($body);
                $this->_db->update('notifications', array(
                  'user_id' => $user_idd,
                  'post_id' => $post_idd,
                  'type' => $type
                ), array('body' => $json));
              }
            }
          }
          return true;
        }
      }
    }
    return false;
  }
/*------------------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------------------*/


/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~* Some Filtering Stuff. [ HTML codes, Hashtags & Mentions Enabling ] *~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

  /*======================================================
  ============* Enable Hashtags and Mentions *============
  ======================================================*/
  // What does it to? - It Checks for every word if it contains @ or # then convert it into a link
  public function HashtagsAndMentions($string){
    $words = explode(" ", $string);
    $mentionArr = array();
    foreach($words as $key => $word){ // For each word check if it contains @ or #
      if(substr($word, 0, 1) === '@'){
        $username = str_replace('@', '', $word);
        if($this->find('users', $username, 'username')){ // Check if the tagged username exists or not
          $mentioned = $this->find('users', $username, 'username')->found();
          $username = $mentioned->username;
          $words[$key] = "<a href='profile.php?username={$username}'>{$word}</a>";
          $mentioning = $this->find('users', $username, 'username')->found()->id;
          array_push($mentionArr, $mentioning);
        }
      }elseif(substr($word, 0, 1) === '#'){
        $hashtag = str_replace('#', '', $word);
        $words[$key] = "<a href='topic.php?topic={$hashtag}'>{$word}</a>";
      }
    }
    //after foreach implode the text again
    $string = implode($words, ' ');
    $string = str_replace("\r", "", $string);
    $string = str_replace("\n", "", $string);

    $returned = array($string, $mentionArr);
    return $returned;
  }

  /*===========================================================
  ============* Filter Text For Comments or Posts *============
  ===========================================================*/
  // What does it to? - It removes extra new lines, and replace new lines with HTML breaklines
  // Also it Calls a function to check Hashtags or mentions.
  public function FilteringText($string, $noMT = false){ //$noMT refers for no mention or tag.. to use the same function to filter msgs
    $string = htmlspecialchars($string); // To freeze any HTML tags.
    $lines = explode("\r", $string . ' ');
    foreach($lines as $key => $line){
      if(strlen($line) === 1){
        unset($lines[$key]);
      }
    }
    $string = implode($lines, " <br/> "); // Convert it To a text again to go two next step.
    if(!$noMT){
      return $this->HashtagsAndMentions($string);
    }else{
      return $string;
    }
  }
/*------------------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------------------*/


/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~* Some Posts Stuff. [ Publishing, Retreiving, Checking Existence ] *~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
  /*=========================================================
  ============* Inserting New Posts To DataBase *============
  =========================================================*/
  public function publishPost($post){
    if($this->isLogged()){
      $clearPost = $this->FilteringText($post); // Remove extra new lines, change nl 2 br and enable hashtags & mentions.
      $date = Date('Y-m-d h:i:s');
      if($this->_db->insert('posts', array( // It's safe now. insert it into database
          'user_id' => $this->data()->id,
          'body' => $clearPost[0],
          'posted_at' => $date
        ))){
          //Send Notification, If it contains a tag.
          if($clearPost[1]){
            // If the Tagger is the owner of the post don't send him notification back
            foreach($clearPost[1] as $tag){
              if($this->data()->id !== $tag){
                // get the post id
                $post_idd = $this->_db->get('id', 'posts', array(
                  'user_id' => $this->data()->id,
                  'body' => $clearPost[0],
                  'posted_at' => $date
                ));
                $post_idd = $post_idd->first()->id;
                $body = array(
                  'Tagger' => $this->data()->name,
                  'username' => $this->data()->username
                );
                $json = JSON_encode($body);
                $this->sendNotification($tag, $post_idd, 4, $json);
              }
            }
          }
        return true;
      }

    }
    return false;
  }

  public function removePost($id){
    if($this->isLogged()){
      if(!$this->_db->get('*', 'posts', array('id' => $id))->error()){
        $post = $this->_db->get('*', 'posts', array('id' => $id))->first();
        if($post->user_id === $this->data()->id){
          // Before deleting the Post check If This post was carrying a tag and delete the notification of it.
          if(strpos(' ' . $post->body, '@')){ // if true then check if it has html tags <a></a> or not, // the space because it may take index zero
            $string = preg_replace("/<[^>]*>/", " ", $post->body); // This will replace html we have put when we stored it with space so we can explode more accurate
            $words = explode(' ', $string);
            foreach($words as $word){
              $word = str_replace(' ', '', $word);
              $username = str_replace('@', '', $word); // to get the pure username
              $q = $this->find('users', $username, 'username');
              if($q && $q->found()){// Then the user exists, then delete the notify
                $this->_db->delete('notifications', array(
                  'user_id' => $q->found()->id,
                  'post_id' => $id,
                  'type' => 4
                ));
              }
            }
          }

          //if the post has comments delete the comments
          if($this->hasComments($id)){
            $cmnts = $this->_db->get('id', 'comments', array('post_id' => $id))->result();
            foreach($cmnts as $cmnt){
              $this->removeComment($cmnt->id, $id, true); // true refers for sudo
            }
          }

          if($this->_db->delete('posts', array('id' => $id))){
            return true;
          }
        }
      }
    }
    return false;
  }

  /*=====================================================
  ============* Check If The User Has Posts *============
  =====================================================*/
  public function hasPosts($id){
    if($this->isLogged()){
      if($this->_db->get('*', 'posts', array('user_id' => $id))->count()){
        return true;
      }
    }
    return false;
  }

  /*====================================================
  ============* Get All The User Has Posts *============
  ====================================================*/
  public function getPosts($id, $limit, $start){
    if($this->hasPosts($id)){
      $sql = "SELECT posts.*,
                     users.name,
                     users.username,
                     users.image_url
              FROM   users,
                     posts
              WHERE  users.id = ?
                     AND posts.user_id = ?
              ORDER  BY id DESC
              LIMIT  {$limit} offset {$start}";
      return $this->_db->query($sql, array($id, $id))->result();
    }
    return false;
  }

  public function getPost($id){
    if($this->isLogged()){
      $sql = "SELECT posts.*,
                     users.name,
                     users.username,
                     users.image_url
              FROM   users,
                     posts
              WHERE  users.id = posts.user_id
                     AND posts.id = ?";
      $query = $this->_db->query($sql, array($id));
      if($query->count()){
        return $query->first();
      }
    }
    return false;
  }

  /*====================================================
  ==============* Get All Timeline Posts *==============
  ====================================================*/
  public function getTimeline($limit, $start){
    if($this->isLogged()){
      $id = $this->data()->id;
      $sql = "SELECT posts.*
              FROM
                (SELECT posts.id,
                        posts.user_id,
                        users.name,
                        users.image_url,
                        users.username,
                        posts.body,
                        posts.likes,
                        posts.comments,
                        posts.posted_at
                 FROM users,
                      posts,
                      followers
                 WHERE users.id = followers.user_id
                   AND posts.user_id = users.id
                   AND followers.follower_id = {$id}
                 UNION
                   (SELECT posts.id,
                           posts.user_id,
                           users.name,
                           users.image_url,
                           users.username,
                           posts.body,
                           posts.likes,
                           posts.comments,
                           posts.posted_at
                    FROM posts,
                         users
                    WHERE posts.user_id = {$id}
                      AND users.id = {$id} ))posts
              ORDER BY id DESC LIMIT {$limit} OFFSET {$start}";

      return $this->_db->query($sql)->result();
    }
    return false;
  }

  public function sendNotification($user_id, $post_id, $type, $body){//About types: 1 Will be for likes, Type 2 for comments, 3 for follow and 4 for tags
    if($this->isLogged()){
      if(is_numeric($user_id) && is_numeric($type)){
        if($this->_db->insert('notifications', array(
          'user_id' => $user_id,
          'post_id' => $post_id,
          'type' => $type,
          'body' => $body,
          'creation_time' => Date('Y-m-d h:i:s')
        ))){
          return true;
        }
      }
    }
    return false;
  }

  public function getMyNotifications($user_id){
    if($this->isLogged()){
      if(is_numeric($user_id)){
        $query = $this->_db->get('*', 'notifications', array('user_id' => $user_id ), 'desc_id limit_30');
        if(!$query->error() && $query->count()){
          return $query->result();
        }
      }
    }
    return false;
  }

  public function getNotification($user_id, $post_id, $type){
    if($this->isLogged()){
      if(is_numeric($user_id) && is_numeric($type)){
        $query = $this->_db->get('*', 'notifications', array(
          'user_id' => $user_id,
          'post_id' => $post_id,
          'type' => $type
        ));
        if(!$query->error() && $query->count()){
          return $query->first();
        }
      }
    }
    return false;
  }

  public function sawNotification($id){
    if($this->isLogged()){
      if(is_numeric($id)){
        if($this->_db->update('notifications', array('id' => $id), array('state' => 1))){
          return true;
        }
      }
    }
    return false;
  }

  public function getNotifsNumber(){
    if($this->isLogged()){
      $query = $this->_db->get('id', 'notifications', array(
        'user_id' => $this->data()->id,
        'state' => 0
      ));
      if(!$query->error()){
        return $query->count();
      }
    }
    return false;
  }

/*------------------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------------------*/

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~* Some Liking Stuff. [ Setting Like, Removing like ] *~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
  public function isLiked($id){
    if($this->isLogged()){
      if($this->_db->get('*', 'likes', array('post_id' => $id, 'user_id' => $this->data()->id))->count()){
        return true;
      }
    }
    return false;
  }

  public function actionLike($id){
    $likes = $this->_db->get('likes, user_id, body', 'posts', array('id' => $id))->first();
    //let's check if a record exists or not
    $user_idd = $likes->user_id; // This is ( the owner of the post )'s Id
    $post_idd = $id; // This is The Post's Id
    $type     = 1; // This is Type: 1 for likes
    if($this->isLiked($id)){ // If liked then set unlike
      if($this->_db->delete('likes', array('user_id' => $this->data()->id, 'post_id' => $id)) && $this->_db->update('posts', array('id' => $id), array('likes' => $likes->likes - 1)) ){
        // We now need to remove my name from notifications or remove the whole notification if I'm the only liker
        // If the liker is the owner of the post don't send him notification back
        if($this->data()->id !== $likes->user_id){
          if($this->getNotification($user_idd, $post_idd, $type)){ // This must be true always. but I put to avoid errors anyway while debugging
            $notify = $this->getNotification($user_idd, $post_idd, $type);
            $body = JSON_decode($notify->body, true);
            if(in_array($this->data()->name, $body['Likerz'])){
              //but if he is the only user~ then delete the whole record.
              if(count($body['Likerz']) === 1){
                $this->_db->delete('notifications', array(
                  'user_id' => $user_idd,
                  'post_id' => $post_idd,
                  'type' => $type
                ));
              }else{ // else if liker name is stored then remove him
                $key = array_search($this->data()->name, $body['Likerz']);
                unset($body['Likerz'][$key]);
                $json = JSON_encode($body);
                $this->_db->update('notifications', array(
                  'user_id' => $user_idd,
                  'post_id' => $post_idd,
                  'type' => $type
                ), array('body' => $json));
              }
            }
          }
        }
        return true;
      }
    }elseif($this->_db->insert('likes', array( 'user_id' => $this->data()->id, 'post_id' => $id )) && $this->_db->update('posts', array('id' => $id), array('likes' => $likes->likes + 1)) ){ // If Unliked then Set a like.
      // If the liker is the owner of the post don't send him notification back
      if($this->data()->id !== $likes->user_id){
        if($this->getNotification($user_idd, $post_idd, $type)){//if true then it exists
          $body = $this->getNotification($user_idd, $post_idd, $type)->body;
          $json = JSON_decode($body, true);
          array_unshift($json['Likerz'], $this->data()->name);
          $json = JSON_encode($json);
          $this->_db->update('notifications', array(
            'user_id' => $user_idd,
            'post_id' => $post_idd,
            'type' => $type,
          ), array('body' => $json, 'state' => 0));
        }else{// no notification found
          $bodiy = preg_replace('/<[^>]*>/', '', $likes->body);
          $postWords = explode(' ', $bodiy);
          if(count($postWords) > 3){
            $postBreif = $postWords[0] . ' ' . $postWords[1] . ' ' . $postWords[2] . ' ';
          }else{
            $postBreif = $likes->body;
          }
          $body = array(
            'breif' => $postBreif,
            'Likerz' => array(
              $this->data()->name
            )
          );
          $json = JSON_encode($body);
          $this->sendNotification($user_idd, $post_idd, $type, $json);
        }
      }
      return true;
    }
    return false;
  }
/*------------------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------------------*/

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~* Some Commenting Stuff. [ Checking Comments Existence, Put Comment ] *~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
  public function hasComments($id){
    if($this->isLogged()){
      if($this->_db->get('*', 'comments', array('post_id' => $id))->count()){
        return true;
      }
    }
    return false;
  }

  public function getComments($id, $limit, $start){
    if($this->hasComments($id)){
      $sql = "SELECT comments.*,
                     users.name,
                     users.username,
                     users.image_url
              FROM   comments,
                     users
              WHERE  comments.post_id = ?
              And    users.id = comments.user_id
              ORDER BY id DESC LIMIT {$limit} OFFSET {$start}";
      $query = $this->_db->query($sql, array($id));
      if(!$query->error()){
        return $query->result();
      }
    }
    return false;
  }

  public function publishComment($id, $comment){
    if($this->isLogged()){
      if($this->_db->get('comments', 'posts', array('id' => $id))->count()){
        $comments = $this->_db->get('comments', 'posts', array('id' => $id))->first()->comments;
        $clearred = $this->FilteringText($comment);
        $clearComment = $clearred[0]; //Freeze HTML Codes And Clear Extra Lines, & Replace \n with <br /> And Enable Hashtags & Mentions.
        $date = Date('Y-m-d h:i:s');
        if(
          $this->_db->insert('comments', array(
            'user_id' => $this->data()->id,
            'post_id' => $id,
            'posted_at' => $date,
            'body' => $clearComment
          )) &&
          $this->_db->update('posts', array('id' => $id), array('comments' => $comments + 1))
        ){
          // if there is a mention send a notify
          if($clearred[1]){
            foreach($clearred[1] as $mention){
              //get the id of the comment posted
              $commentId = $this->_db->get('id', 'comments', array(
                'user_id' => $this->data()->id,
                'post_id' => $id,
                'posted_at' => $date,
                'body' => $clearComment
              ))->first()->id;

              //get the username of the post owner
              $posterId = $this->_db->get('user_id', 'posts', array('id' => $id))->first()->user_id;
              $posterUsr = $this->find('users', $posterId, 'id')->found()->username;
              $body = array(
                'Mentioner' => $this->data()->name,
                'post_link' => 'username=' . $posterUsr . '&amp;id=' . $id
              );
              $json = JSON_encode($body);
              $this->sendNotification($mention, $commentId, 5, $json); // notice that here I will put the comment id in post id column that's because we will use it to delete notify
            }
          }
          // Send notification That someone commented on your post
          $postt = $this->_db->get('user_id, body', 'posts', array('id' => $id))->first();
          // If the Commenter is the owner of the post don't send him notification back
          if($this->data()->id !== $postt->user_id){
            $user_idd = $postt->user_id;
            $post_idd = $id;
            $type = 2; // Type two for comments
            if($this->getNotification($user_idd, $post_idd, $type)){ //if true then it a record exists by another guy or maybe by me
              $body = $this->getNotification($user_idd, $post_idd, $type)->body;
              $json = JSON_decode($body, true);
              //if my name isset delete it
              if(in_array($this->data()->name, $json['Commenterz'])){
                $key = array_search($this->data()->name, $json['Commenterz']);
                unset($json['Commenterz'][$key]);
              }
              array_unshift($json['Commenterz'], $this->data()->name);
              $json = JSON_encode($json);
              $this->_db->update('notifications', array(
                'user_id' => $user_idd,
                'post_id' => $post_idd,
                'type' => $type,
              ), array('body' => $json, 'state' => 0));
            }else{// no notification found. You are a new commenter
              $clearPost = preg_replace("/<[^>]*>/", " ", $postt->body);
              $postWords = explode(' ', $clearPost);
              if(count($postWords) > 3){
                $postBreif = $postWords[0] . ' ' . $postWords[1] . ' ' . $postWords[2] . ' ';
              }else{
                $postBreif = $clearPost;
              }
              $body = array(
                'breif' => $postBreif,
                'Commenterz' => array(
                  $this->data()->name
                )
              );
              $json = JSON_encode($body);
              $this->sendNotification($user_idd, $post_idd, $type, $json);
            }
          }
          return true;
        }
      }
    }
    return false;
  }

  public function removeComment($id, $postId, $sudo = false){ // Sudo refers to fuck your rules up and do what i want. I use it with deleting comments by deleting posts
    if($this->isLogged()){
      if(!$this->_db->get('*', 'comments', array('id' => $id))->error()){
        $comment = $this->_db->get('*', 'comments', array('id' => $id))->first();
        if($comment->user_id === $this->data()->id || $sudo){ // Sudo refers to fuck your rules up and do what i want. I use it with deleting comments by deleting posts
          // Before deleting the comment check If This comment was carrying a mention and delete the notification of it.
          if(strpos(' ' . $comment->body, '@')){ // if true then check if it has html tags <a></a> or not, // the space because it may take index zero
            $string = preg_replace("/<[^>]*>/", " ", $comment->body); // This will replace html we have put when we stored it with space so we can explode more accurate
            $words = explode(' ', $string);
            foreach($words as $word){
              $word = str_replace(' ', '', $word);
              $username = str_replace('@', '', $word); // to get the pure username
              $q = $this->find('users', $username, 'username');
              if($q && $q->found()){// Then the user exists, then delete the notify
                $this->_db->delete('notifications', array(
                  'user_id' => $q->found()->id,
                  'post_id' => $id,
                  'type' => 5
                ));
              }
            }
          }
          if($this->_db->delete('comments', array('id' => $id))){
            // decrease number of comments of the post
            $comments = $this->_db->get('comments', 'posts', array('id' => $postId))->first()->comments;

            if($this->_db->update('posts', array('id' => $postId), array('comments' => $comments - 1)) || $sudo){
              //notification part
              $postt = $this->_db->get('user_id, body', 'posts', array('id' => $postId))->first();
              $user_idd = $postt->user_id;
              $post_idd = $postId; // You can see this as a stupid thing but I did it because I copied the coming part from another code of mine. :"D
              $type = 2; // Type two for comments

              // We now need to remove my name from notifications or remove the whole notification if I'm the only commenter
              // If the commenter is the owner of the post don't send him notification back
              if($this->data()->id !== $postt->user_id && !$sudo){
                if($this->getNotification($user_idd, $post_idd, $type)){ // This must be true always. but I put to avoid errors anyway while debugging

                  $notify = $this->getNotification($user_idd, $post_idd, $type);

                  $body = JSON_decode($notify->body, true);
                  if(in_array($this->data()->name, $body['Commenterz'])){
                    //but if he is the only user~ then delete the whole record.
                    if(count($body['Commenterz']) === 1){
                      $this->_db->delete('notifications', array(
                        'user_id' => $user_idd,
                        'post_id' => $post_idd,
                        'type' => $type
                      ));
                    }else{ // else if commenter name is stored then remove him
                      $key = array_search($this->data()->name, $body['Commenterz']);
                      unset($body['Commenterz'][$key]);
                      $json = JSON_encode($body);
                      $this->_db->update('notifications', array(
                        'user_id' => $user_idd,
                        'post_id' => $post_idd,
                        'type' => $type
                      ), array('body' => $json));
                    }
                  }
                }
              }elseif($sudo){
                // Remove notification
                if($this->getNotification($user_idd, $post_idd, $type)){ // This must be true always. but I put to avoid errors anyway while debugging
                  $notify = $this->getNotification($user_idd, $post_idd, $type);
                  $body = JSON_decode($notify->body, true);
                  if(in_array($this->data()->name, $body['Commenterz']) || $sudo){
                    //but if he is the only user~ then delete the whole record.
                    if(count($body['Commenterz']) === 1){
                      $this->_db->delete('notifications', array(
                        'user_id' => $user_idd,
                        'post_id' => $post_idd,
                        'type' => $type
                      ));
                    }else{ // else if commenter name is stored then remove him
                      $key = array_search($this->data()->name, $body['Commenterz']);
                      unset($body['Commenterz'][$key]);
                      $json = JSON_encode($body);
                      $this->_db->update('notifications', array(
                        'user_id' => $user_idd,
                        'post_id' => $post_idd,
                        'type' => $type
                      ), array('body' => $json));
                    }
                  }
                }


              }

              return true;
            }
          }
        }
      }
    }
    return false;
  }

  public function getCommentsNumber($id){
    if($this->isLogged()){
      if(!$this->_db->get('id', 'comments', array('post_id' => $id))->error()){
        return $this->_db->get('id', 'comments', array('post_id' => $id))->count();
      }
    }
    return false;
  }

/*------------------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------------------*/




/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~* [ MESSENGER ] *~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
public function getMyMessengers(){
  if($this->isLogged()){
    $myId = $this->data()->id;
$sql = "SELECT DISTINCT IF(msgs.sender_id != {$myId}, msgs.sender_id, msgs.receiver_id) AS id,
                        msgs.username,
                        msgs.user_id,
                        msgs.image_url,
                        msgs.name,
                        msgs.active_state,
                        1 AS seen_state
        FROM
          (SELECT DISTINCT messages.sender_id,
                           messages.receiver_id,
                           users.username,
                           users.id AS user_id,
                           users.name,
                           users.active_state,
                           users.image_url
           FROM messages,
                users
           WHERE (messages.sender_id = {$myId}
                  AND users.id = messages.receiver_id)
             OR (messages.receiver_id = {$myId}
                 AND users.id = messages.sender_id))msgs
        UNION
        SELECT followers.user_id AS id,
               users.username,
               users.id AS user_id,
               users.image_url,
               users.name,
               users.active_state,
               1 AS seen_state
        FROM users,
             followers
        WHERE followers.follower_id = {$myId}
          AND users.id = followers.user_id"; // This Returns a combination of people I messages them before and the people I followed them. and a fixed value of seen state which I will edit it next
    $query = $this->_db->query($sql);
    if(!$query->error() && $query->count()){
      $rows = $query->result();
      foreach($rows as $row){
        $sndr = $row->id;
        $sql = "SELECT seen FROM messages WHERE seen = 0 AND receiver_id = {$myId} AND sender_id = {$sndr}";
        $query = $this->_db->query($sql);
        if(!$query->error() && $query->count()){
          if($query->count() > 0){
            $row->seen_state = '0';
          }
        }
      }
      shuffle($rows);
      return $rows;
    }
  }
  return false;
}

public function getHisChat($id, $limit, $offset){
  $me = $this->data()->id;
  $him = $id;
  if($this->isLogged()){
  $sql = "SELECT messages.*,
                 users.name,
                 users.username
          FROM   messages,
                 users
          WHERE  ( messages.sender_id = ? AND messages.receiver_id = ? AND users.id = ? )
          OR     ( messages.sender_id = ? AND messages.receiver_id = ? AND users.id = ? )
          ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
  $query = $this->_db->query($sql, array(
    $me, $him, $him,
    $him, $me, $him
  ));
    if(!$query->error() && $query->count()){
      return $query->result();
    }
  }
  return false;
}

public function getHisName($id){
  if($this->isLogged()){
    $id = $this->find('users', $id, 'id');
    if($id && $id->found()){
      return $id->found()->name;
    }
  }
  return false;
}


public function makeSeen($id){
  if($this->isLogged()){
    $id = $this->find('users', $id, 'id');
    if($id && $id->found()){
      if(
        $this->_db->update('messages', array('sender_id' => $id->found()->id,'receiver_id' => $this->data()->id), array('seen' => 1))
      ){
        return true;
      }
    }
  }
  return false;
}


/*==================================================
===============* get new msgs number *===============
==================================================*/
public function getNumMsgs(){
  if($this->isLogged()){
    $query = $this->_db->get('id', 'messages', array(
      'receiver_id' => $this->data()->id,
      'seen' => 0
    ));
    if(!$query->error()){
      return $query->count();
    }
  }
  return false;
}


/*==================================================
===============* Send A New Message *===============
==================================================*/
public function sendMessage($toId, $body){
  //filter the msg before
  if($this->isLogged()){
    $id = $this->find('users', $toId, 'id');
    if($id && $id->found()){
      $body = $this->FilteringText($body, true);

      if($this->_db->insert('messages', array(
        'sender_id' => $this->data()->id,
        'receiver_id' => $id->found()->id,
        'body' => $body,
        'sending_time' => Date('Y-m-d h:i:s')
      ))){
        return true;
      }

    }
  }
  return false;
}


/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~* Some Stuff *~~~~~~~~~~~~~~~~~~~~*/
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
  public function isLogged(){
    return $this->_isLogged;
  }

  public function found(){
    return $this->_found;
  }

  public function data(){
    return $this->_data;
  }
  /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
  /*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
}
