$(document).ready(function(){
  /* upload profile picture */
  if($("#changeImg")){
    $("#profilepicture").change(function(){
      $("body").prepend('<div id="loading"> <ul class="bokeh"> <li></li> <li></li> <li></li> </ul> </div>');
      $("#changeImg").submit();
    });
  }
});
/*=======================================
==========* Following Someone *==========
=======================================*/
function FollowAction(username, fname, element, e){
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
    url: 'ajax/user.php',
    type: "POST",
    data: {
      'following': '1',
      'username': username,
      'csrf': csrf
    },
    success: function(response){
      if(response === 'Success'){
        $(element).find('i').toggleClass('fa-user');
        $(element).find('i').toggleClass('fa-users');
        $(element).toggleClass('follow');
        $(element).toggleClass('followed');
        if($(element).attr('title') === 'Do you know ' + fname + '? click to follow him.'){
          $(element).attr('title', 'You are following ' + fname + '. click to unfollow him.');
          $('#followers').html( (parseInt($('#followers').html()) + 1) );
        }else{
          $(element).attr('title', 'Do you know ' + fname + '? click to follow him.');
          $('#followers').html( (parseInt($('#followers').html()) - 1) );
        }
      }else{
        console.log(response)
      }
    },
    error: function(jqXHR, textStatus, errorThrown) {
       console.log(textStatus, errorThrown);
    }
  });
}
