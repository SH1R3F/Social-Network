
// Pre-Defined Functions
/*===========================================
==========* Get Num Notifications *==========
===========================================*/
function GetUnseenNotifications(){
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
    url: 'ajax/notifications.php',
    type: 'POST',
    data: {
      'GetUnseenNotifications': '1',
      'csrf': csrf
    },
    success: function(response){
      if(parseInt(response) > 0){
        $(".notifs").css({'background': 'red'});
        $(".notifs>i").html(parseInt(response));
      }else{
        $(".notifs").css({'background': 'transparent'});
        $(".notifs>i").html('');
      }
    },
    error: function(jqXHR, textStatus, errorThrown){
      console.log(textStatus, errorThrown);
    }
  })
}
/*===========================================
==========* Get All Notifications *==========
===========================================*/
var something = '';
function GetMyNotifications(){
  if($("#csrf_logged")){
    var csrf = $("#csrf_logged").attr('content');
    $.ajax({
       url: "ajax/notifications.php",
       type: "POST",
       data: {
         'GetMyNotifications': '1',
         'csrf': csrf
       },
       success: function (response) {
         if(something !== response){ // This means only execute if there are new notifications
           $("#notifications").html(response);
           something = response;
           GetUnseenNotifications();
         }
       },
       error: function(jqXHR, textStatus, errorThrown) {
          console.log(textStatus, errorThrown);
       }
    });
  }
}
/*============================================
==========* Mark Notification Seen *==========
============================================*/
function SeenNotify(Id){
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
     url: "ajax/notifications.php",
     type: "POST",
     data: {
       'SeeMyNotify': '1',
       'csrf': csrf,
       'NotifyId': Id
     },
     success: function (response) {
       GetUnseenNotifications();
     },
     error: function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
     }
  });
}
setInterval(function(){
  GetMyNotifications();
}, 2000);
