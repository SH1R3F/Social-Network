<?php
require_once 'core/init.php';
if(Input::get('username', 'GET')){
  if($user->find('users', Input::get('username', 'GET'))){
    $data = $user->find('users', Input::get('username', 'GET'))->found();
  }else{
    require_once 'includes/no-user.php';
    exit();
  }
}else{
  $data = $user->data();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && $_FILES['profilepicture']['tmp_name']){
  if(Token::check('csrf_photo', Input::get('csrf_photo'), 'Delete')){
    if($_FILES['profilepicture']['size'] < 10240000){
      $image = base64_encode(file_get_contents($_FILES['profilepicture']['tmp_name']));
      $url = 'https://api.imgur.com/3/image';
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch,CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . Config::get('imgur/bearer'),
        'Content-Type: application/x-www-form-urlencoded'
      ));
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $image);
      $data = curl_exec($ch);
      curl_close($ch);
      $json = JSON_decode($data);
      if($json->success){
        $link = $json->data->link;
        $user->changeValue('image_url', $link);
        Redirect::re();
      }else{
        $state = "<script>alerify.error('An error happened while modifing your picture :( . Refresh the page and try again')</script>";
      }
    }else{
      $state = "<script>alertify.error('Image size is very big. You can only upload less than 10MB.');</script>";
    }
  }else{
    Redirect::re();
  }
}

get_header();?>
<div class='container-fluid'>
  <header class='profile'>
    <div class='user'>
      <div class='container'>
        <div class='mid'>
          <div class='col-sm-2 hidden-xs follow'>
            <h3>Following</h3>
            <h5 id='following'><?php echo $user->following($data->id); ?></h5>
          </div>
          <div class='col-xs-12 col-sm-8 userdata'>
            <div class='user-image' style="background: url(<?php echo $data->image_url; ?>)">
              <?php if($data->id === $user->data()->id): ?>
                <form action='<?php echo sanitize($_SERVER['PHP_SELF']); ?>' method='POST' enctype='multipart/form-data' id="changeImg" />
                  <label for='profilepicture'>
                    <div class='camera' title='Change your profile picture'>
                      <i class='fa fa-camera'></i>
                    </div>
                  </label>
                  <input type='file' id='profilepicture' name='profilepicture' />
                  <input type='hidden' name='csrf_photo' value='<?php echo Token::generate('csrf_photo'); ?>' />
                </form>
              <?php endif; ?>
            </div>
            <h3><?php echo sanitize($data->name); ?></h3>
            <h5><a href='profile.php?username=<?php echo sanitize($data->username); ?>'>@<?php echo sanitize($data->username); ?></a></h5>
            <?php
            if($user->data()->id !== $data->id):
              $class  = ($user->isFollowed($data->id))? ' followed' : ' follow' ;
              $iclass = ($user->isFollowed($data->id))? ' fa-users' : ' fa-user' ;
              $title  = ($user->isFollowed($data->id))? 'You are following ' . explode(' ', $data->name)[0] . '. click to unfollow him.' : 'Do you know ' . explode(' ', $data->name)[0] . '? click to follow him.' ;
              ?>
              <div class='action<?php echo $class; ?>' onclick="FollowAction('<?php echo $data->username; ?>', '<?php echo explode(' ', $data->name)[0]; ?>', this, event)" title="<?php echo $title; ?>">
                <i class='fa <?php echo $iclass; ?>'></i>
              </div>
              <div class='action message' onclick="PopChatUp('<?php echo $data->username; ?>', 20, 0)" title="Send a message to <?php echo explode(' ', $data->name)[0]; ?>.">
                <i class='fa fa-envelope'></i>
              </div>
            <?php endif; ?>
          </div>
          <div class='col-sm-2 hidden-xs follow'>
            <h3>Followers</h3>
            <h5 id='followers'><?php echo $user->followers($data->id); ?></h5>
          </div>
        </div>
      </div>
    </div>
  </header>
</div>

<div class='profile-content'>
  <div class='container'>
    <div class='row'>
      <?php if($data->bio || $data->town || $data->website): ?>
        <div class='col-md-4 hidden-xs hidden-sm sidebar'>
          <div class='widget'>
            <div class='widget-title'>
              <h3>About Me</h3>
            </div>
            <div class='widget-content'>

              <?php
                if($data->bio){
                  echo '<label>Who am I?</label><p>'.$data->bio.'</p>';
                }
                if($data->town){
                  echo "<div class='row'><label class='col-xs-3'><a>Town:</a></label><span>".$data->town."</span></div>";
                }
                if($data->website){
                  echo "<div class='row'> <label class='col-xs-3'> <a>Website:</a> </label> <span><a href='".$data->website."' target='_blank'>".$data->website."</a></span> </div>";
                }
              ?>
            </div>
          </div>
        </div>
      <?php endif;
      if($data->bio || $data->town || $data->website){
        $class = 'col-md-8 col-xs-12';
      }else{
        $class = 'col-md-10 col-md-offset-1 col-xs-12';
      }
      ?>
      <div class='<?php echo $class; ?>'>
        <div class='posts-area' id='postarea'>
        </div>
        <div id="loadData"></div>
      </div>
    </div>
  </div>
</div>
<script>
getUserPosts(<?php echo $data->id; ?>, 7, 0, 'UPDATE')
setInterval(function(){
  getUserPosts(<?php echo $data->id; ?>, 7, 0, 'UPDATE')
}, 1500)
var offset = 7;
$(window).scroll(function(){
  if($(window).scrollTop() + $(window).height() > $("#postarea").height()){
    getUserPosts(<?php echo $data->id; ?>, 7, offset, 'GETandSCROLL'); // SCROLL RETREIVING
    offset = offset + 7;
  }
})
</script>
<?php echo @$state; // The State of changing profile picture ?>
<?php get_footer(); ?>
