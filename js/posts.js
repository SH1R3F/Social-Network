$(document).ready(function(){
  /*==========================================
  =============* Publish a Post *=============
  ==========================================*/
  if($("#publish")){
    $("#publish").click(function(){
      var post = $("#post").val(),
      csrf = $("#csrf_logged").attr("content");
      //send a request
      $.ajax({
        url: "ajax/post.php",
        type: "POST",
        data: {
          'PublishPost': '1',
          'post': post,
          'csrf_logout': csrf
        },
        success: function(response){
          if(response === 'Success'){
            $("#post").val('');
            TimelineGetandScroll(7, 0, 'UPDATE');
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.log(textStatus, errorThrown);
        }
      })
    });
  }

  /*==============================================
  ============* Publishing a Comment *============
  ==============================================*/
  if($("#publishComment")){
    $("#publishComment").click(function(){
      if($("#writeComment").val().replace(/\s/g, '').length !== 0){
        var csrf = $("#csrf_logged").attr("content");
        $.ajax({
          url: "ajax/post.php",
          type: "POST",
          data: {
            'PublishComment': '1',
            'comment': $('#writeComment').val(),
            'post_id': $('.post')[0].id,
            'csrf_token': csrf
          },
          success: function (response) {
            if(response === 'Success'){
              $("#writeComment").val("");
              FetchComments($('.post')[0].id, 4, 0, 'UPDATE');
              CommentsNumber($('.post')[0].id);
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
          }
        });
      }
    });
  }
});

/*==========================================
==========* Get And Scroll Posts *==========
==========================================*/
var lastGetResponse    = '';
var allPosts           = [];
function TimelineGetandScroll(limit, start, type = 'GETandSCROLL'){
  var csrf = $('#csrf_logged').attr('content');
  var user_id = $("#user_id").attr("content");
  $.ajax({
    url: 'ajax/post.php',
    type: 'POST',
    data: {
      'FetchPostsTimeline': '1',
      'csrf': csrf,
      'limit': limit,
      'start': start
    },
    cache: false,
    success: function (response) {
      if(lastGetResponse !== response){
        var json = JSON.parse(response);
        for(var j in json){
          for(var p in allPosts){
            try{
              if(json[j]['id'] === allPosts[p]['id']){
                json.splice(j, 1);
              }
            }catch(error){
              //do nothing
            }
          }
        }
        var html = '';
        for(var post in json){
          html += "<div class='post'><div class='row'><div class='publisher col-xs-10 text-left'><div class='poster-thumb' style='background: url("+json[post]['image_url']+")'></div>";
          html += "<a href='profile.php?username="+json[post]['username']+"'>"+json[post]['name']+"</a>";
          html += "<a href='post.php?username="+json[post]['username']+"&id="+json[post]['id']+"'>"+json[post]['posted_at']+"</a></div>";
          if(user_id === json[post]['user_id']){
            html += "<div class='options dropdown col-xs-2 text-right'>";
            html += "<a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'><span class='caret'></span></a><ul class='dropdown-menu'><li><a href='#' onclick='alert(\"not working yet\")'>Edit</a></li><li>";
            html += "<a href='#' onclick='RemovePost("+json[post]['id']+", this, event)'>Delete</a></li></ul></div>";
          }
          html += "</div><p class='article' dir='auto'>"+json[post]['body']+"</p><span class='stats'><i class='fa fa-thumbs-up like'> "+json[post]['likes']+"</i><i class='fa fa-comment comment'> "+json[post]['comments']+"</i></span>";
          html += "<div class='buttons'><div class='row'><a href='#' onclick='SetULike(" + json[post]['id'] + ", this, event);' class='col-xs-6 like "+json[post]['isLiked']+"'> <i class='fa fa-thumbs-up'></i> Like</a><a href='post.php?username=" + json[post]['username']+"&id="+json[post]['id'] + "' class='col-xs-6 comment'><i class='fa fa-comment'></i> Comment</a></div></div></div>";
        }
        if(type === 'UPDATE'){
          if(html.length === 0 && response === "[]"){
            $('#postarea').html("<div class='post'><p>No posts to view. follow someone to see his posts.</p></div>");
          }else{
            if($('#postarea').html().indexOf('<p>No posts to view. follow someone to see his posts.</p>') > 0){
              $('#postarea').html(html);
            }else{
              $('#postarea').prepend(html);
            }
          }

        }else if(type === 'GETandSCROLL'){
          if(html.length === 0 && response === "[]" && allPosts.length === 0){
            $('#postarea').html("<div class='post'><p>No posts to view. follow someone to see his posts.</p></div>");
          }else{
            if($('#postarea').html().indexOf('<p>No posts to view. follow someone to see his posts.</p>') > 0){
              $('#postarea').html(html);
            }else{
              $('#postarea').append(html);
            }
          }
        }
        allPosts = allPosts.concat(json);
        lastGetResponse = response;
      }
    },
    error: function(jqXHR, textStatus, errorThrown) {
       console.log(textStatus, errorThrown);
    }
  });
}


/*=========================================
==========* Remove My Own Posts *==========
=========================================*/
function RemovePost(PostId, element, e){
  e.preventDefault();
  alertify.confirm("Are you sure that you want to delete this post?",
  function(){
    var csrf = $('#csrf_logged').attr('content');
    $.ajax({
      url: 'ajax/post.php',
      type: 'post',
      data: {
        'RemoveMyPost': '1',
        'csrf': csrf,
        'PostId': PostId
      },
      success: function (response) {
        if(response === 'Success'){
          $(element).parents('.post').remove();
          alertify.success('Post has been deleted!');
        }else{
          alertify.error('An error happened.');
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
         console.log(textStatus, errorThrown);
      }
    });
  });
}

/*=============================================
==========* Get User Timeline Posts *==========
=============================================*/
var lastGetResponse    = '';
var allPosts           = [];
function getUserPosts(user_id, limit, start, type = 'GETandSCROLL'){
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
    url: 'ajax/post.php',
    type: 'POST',
    data: {
      'FetchSomeOneTimeline': '1',
      'user_id': user_id,
      'csrf': csrf,
      'limit': limit,
      'start': start
    },
    cache: false,
    success: function (response) {
      if(lastGetResponse !== response){
        var json = JSON.parse(response);
        for(var j in json){
          for(var p in allPosts){
            try{
              if(json[j]['id'] === allPosts[p]['id']){
                json.splice(j, 1);
              }
            }catch(error){
              //do nothing
            }
          }
        }
        var html = '';
        for(var post in json){
          html += "<div class='post'><div class='row'><div class='publisher col-xs-10 text-left'><div class='poster-thumb' style='background: url("+json[post]['image_url']+")'></div>";
          html += "<a href='profile.php?username="+json[post]['username']+"'>"+json[post]['name']+"</a>";
          html += "<a href='post.php?username="+json[post]['username']+"&id="+json[post]['id']+"'>"+json[post]['posted_at']+"</a></div>";

          var user_id = $("#user_id").attr("content");
          if(user_id === json[post]['user_id']){
            html += "<div class='options dropdown col-xs-2 text-right'>";
            html += "<a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'><span class='caret'></span></a><ul class='dropdown-menu'><li><a href='#' onclick='alert(\"Not working yet\")'>Edit</a></li><li>";
            html += "<a href='#' onclick='RemovePost("+json[post]['id']+", this, event)'>Delete</a></li></ul></div>";
          }
          html += "</div><p class='article' dir='auto'>"+json[post]['body']+"</p><span class='stats'><i class='fa fa-thumbs-up like'> "+json[post]['likes']+"</i><i class='fa fa-comment comment'> "+json[post]['comments']+"</i></span>";
          html += "<div class='buttons'><div class='row'><a href='#' onclick='SetULike(" + json[post]['id'] + ", this, event);' class='col-xs-6 like "+json[post]['isLiked']+"'> <i class='fa fa-thumbs-up'></i> Like</a><a href='post.php?username=" + json[post]['username']+"&id="+json[post]['id'] + "' class='col-xs-6 comment'><i class='fa fa-comment'></i> Comment</a></div></div></div>";
        }
        if(type === 'UPDATE'){
          if(html.length === 0 && response === "[]"){
            $('#postarea').html("<div class='post'><p>This user has not published any posts yet.</p></div>");
          }else{
            if($('#postarea').html().indexOf('This user has not published any posts yet.</p>') > 0){
              $('#postarea').html(html);
            }else{
              $('#postarea').prepend(html);
            }
          }

        }else if(type === 'GETandSCROLL'){
          if(html.length === 0 && response === "[]" && allPosts.length === 0){
            $('#postarea').html("<div class='post'><p>This user has not published any posts yet.</p></div>");
          }else{
            if($('#postarea').html().indexOf('<p>This user has not published any posts yet.</p>') > 0){
              $('#postarea').html(html);
            }else{
              $('#postarea').append(html);
            }
          }
        }
        allPosts = allPosts.concat(json);
        lastGetResponse = response;
      }
    },
    error: function(jqXHR, textStatus, errorThrown) {
       console.log(textStatus, errorThrown);
    }
  });
}

/*============================================
==============* FETCH Comments *==============
============================================*/
var lastCommentsResponse = '';
var allComments = [];
function FetchComments(post_id, limit, start, type = 'GETandSCROLL'){
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
    url: 'ajax/post.php',
    type: "POST",
    data: {
      'getComments': '1',
      'post_id': post_id,
      'csrf': csrf,
      'limit': limit,
      'start': start
    },
    success: function(response){
      if(lastCommentsResponse !== response){
        var json = JSON.parse(response);
        for(var c in json){
          for(var o in allComments){
            try{
              if(allComments[o]['id'] === json[c]['id']){
                json.splice(c, 1);
              }
            }catch(error){
              //pass
            }
          }
        }
        var html = '';
          for(var cj in json){
            html += '<div class="comment"><div class="row"><div class="publisher-img">';
            html += '<div class="poster-thumb" style="background-image: url('+json[cj]['image_url']+')"></div></div>';
            html += '<div class="written-comment"><div class="row"><div class="publisher col-xs-10 text-left">';
            html += '<a href="profile.php?username='+json[cj]['username']+'">'+json[cj]['name']+'</a><a>'+json[cj]['posted_at']+'</a></div>';

            var user_id = $("#user_id").attr("content");
            if(user_id === json[cj]['user_id']){
              html += '<div class="options dropdown col-xs-2 text-right"><a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></a>';
              html += '<ul class="dropdown-menu"><li><a href="#" onclick="alert(\'Not working yet\')">Edit</a></li><li><a href="#" onclick="RemoveComment('+json[cj]['id']+', '+json[cj]['post_id']+', this, event)">Delete</a></li></ul></div>';
            }
            html += '</div><p>'+json[cj]['body']+'</p></div></div></div>';
          }

          if(type === 'UPDATE'){
            if(html.length === 0 && response === "[]"){
              $('#comments-area').html('<div class="comment"><div class="row"><div class="written-comment" style="width: 100%; text-align: center; min-height: auto; padding: 15px 0;"><p>This Post Has No Comments Yet.</p></div></div></div>');
            }else{
              if($('#comments-area').html().indexOf('<p>This Post Has No Comments Yet.</p>') > 0){
                $('#comments-area').html(html);
              }else{
                $('#comments-area').append(html);
              }
            }
          }else if(type === 'GETandSCROLL'){
            if(html.length === 0 && response === "[]" && allComments.length === 0){
              $('#comments-area').html('<div class="comment"><div class="row"><div class="written-comment" style="width: 100%; text-align: center; min-height: auto; padding: 15px 0;"><p>This Post Has No Comments Yet.</p></div></div></div>');
            }else{
              if($('#comments-area').html().indexOf('<p>This Post Has No Comments Yet.</p>') > 0){
                $('#comments-area').html(html);
              }else{
                $('#comments-area').prepend(html);
              }
            }
          }

        allComments = allComments.concat(json);
        lastCommentsResponse = response;
      }
    },
    error: function(jqXHR, textStatus, errorThrown) {
       console.log(textStatus, errorThrown);
   }
 });
}


/*============================================
==========* Remove My Own Comments *==========
============================================*/
function RemoveComment(CommentId, PostId, element, e){
  e.preventDefault();
  alertify.confirm("Are you sure that you want to delete this comment?",
  function(){
    var csrf = $('#csrf_logged').attr('content');
    $.ajax({
      url: 'ajax/post.php',
      type: 'post',
      data: {
        'RemoveMyComment': '1',
        'csrf': csrf,
        'CommentId': CommentId,
        'PostId': PostId
      },
      success: function (response) {
        if(response === 'Success'){
          $(element).parents('.comment').remove();
          alertify.success('The comment has been deleted successifuly.');
        }else{
          alertify.error('An error occured.');
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
         console.log(textStatus, errorThrown);
      }
    });
  });
}
/*=============================================
==========* Liking or Unliking post *==========
=============================================*/
function SetULike(PostId, element, e){
  e.preventDefault();
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
    url: 'ajax/post.php',
    type: 'post',
    data: {
      'Liking': '1',
      'post_id': PostId,
      'csrf': csrf
    },
    success: function (response) {
      var num = parseInt($(element).parents('.post').find('.stats i.like').html());
      if($(element).hasClass('liked')){
        var num = num - 1;
        $(element).parents('.post').find('.stats i.like').html(' ' + num);
      }else{
        var num = num + 1;
        $(element).parents('.post').find('.stats i.like').html(' ' + num);
      }
      $(element).toggleClass('liked');
    },
    error: function(jqXHR, textStatus, errorThrown) {
      console.log(textStatus, errorThrown);
    }
  });
}

//refresh number of comments
var somenummm = '';
function CommentsNumber(PostId){
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
    url: 'ajax/post.php',
    type: "POST",
    data: {
      'getCommentsNumber': '1',
      'post_id': PostId,
      'csrf': csrf
    },
    success: function(response){
      if(response !== somenummm){
        $('i.fa-comment.comment').html(' ' + response);
        somenummm = response;
      }
    },
    error: function(jqXHR, textStatus, errorThrown) {
      console.log(textStatus, errorThrown);
    }
  });
}
