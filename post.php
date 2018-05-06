<?php require_once 'core/init.php'; ?>
<?php

if(Input::get('username', 'GET')){
  if($user->find('users', Input::get('username', 'GET'))){
    $data = $user->find('users', Input::get('username', 'GET'))->found();
  }else{
    require_once 'includes/no-user.php';
    exit();
  }
}
if(is_numeric(Input::get('id', 'GET'))){
  //if post exists in database for this user
  $post  = $user->getPost(Input::get('id', 'GET'));
  $truth = false;
  if($post){
    if($post->user_id === $data->id){
      $truth = true;
    }
  }
  if(!$truth){
    require_once 'includes/no-user.php';
    exit();
  }
}else{
  require_once 'includes/no-user.php';
  exit();
}
?>
<?php get_header(); ?>
<div class='post-page'>
   <div class='container'>
      <div class='row'>
         <div class='col-xs-12 col-sm-10 col-sm-offset-1'>
            <div class="post" id="<?php echo $post->id; ?>">
               <div class="row">

                  <div class="publisher col-xs-10 text-left">
                     <div class="poster-thumb" style="background-image: url(<?php echo $post->image_url; ?>)"></div>
                     <a href="profile.php?username=<?php echo $post->username; ?>"><?php echo $post->name; ?></a><a href="post.php?username=<?php echo $post->username; ?>&amp;id=<?php echo $post->id; ?>"><?php echo $post->posted_at; ?></a>
                  </div>
                  <?php if($post->user_id === $user->data()->id): ?>
                    <div class="options dropdown col-xs-2 text-right">
                       <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></a>
                       <ul class="dropdown-menu">
                          <li><a href="#" onclick="alert('not working yet')">Edit</a></li>
                          <li><a href="#" onclick="RemovePost(<?php echo $post->id; ?>, event)">Delete</a></li>
                       </ul>
                   </div>
                  <?php endif; ?>

               </div>
               <p class="article" dir="auto"><?php echo $post->body; ?></p>
               <span class="stats"><i class="fa fa-thumbs-up like"> <?php echo $post->likes; ?></i> <i class="fa fa-comment comment"> <?php echo $post->comments; ?></i></span>
               <div class="buttons">
                  <div class="row">
                    <?php
                    $liked = ($user->isLiked($post->id))? ' liked' : '' ;
                    ?>
                    <a href="#" onclick="SetULike(<?php echo $post->id; ?>, this, event);" class="col-xs-6 like<?php echo $liked; ?>"> <i class="fa fa-thumbs-up"></i> Like</a>
                    <a href="post.php?username=<?php echo $post->username; ?>&amp;id=<?php echo $post->id; ?>" class="col-xs-6 comment"><i class="fa fa-comment"></i> Comment</a>
                  </div>
               </div>
               <div class='comments'>
                 <a href='#' onclick='return false;' id='showmore'>showmore</a>
                 <div id='comments-area'></div>
                  <div class='comment'>
                     <div class='row'>
                        <div class='publisher-img'>
                          <div class="poster-thumb" style="background-image: url(<?php echo $user->data()->image_url; ?>)"></div>
                        </div>
                        <div class='written-comment'>
                           <textarea placeholder="Write a comment.. (Press enter to post)" id="writeComment"></textarea>
                           <input type='submit' value='comment' class='pull-right' id="publishComment" />
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
$(document).ready(function(){
  FetchComments(<?php echo $post->id; ?>, 4, 0, 'UPDATE');
  setInterval(function(){
    FetchComments(<?php echo $post->id; ?>, 4, 0, 'UPDATE');
  }, 2500);

  var commentsOffset = 4;
  $('#showmore').click(function(){
    FetchComments(<?php echo $post->id; ?>, 4, commentsOffset, 'GETandSCROLL'); // SCROLL RETREIVING
    commentsOffset = commentsOffset + 7;
  });

});
</script>
<?php get_footer(); ?>
