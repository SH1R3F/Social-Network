function ExpandCollapseChat(element){
  $(element).parents('.chat-frame').toggleClass('expanded');
  $(element).parents('.chat-frame').toggleClass('collapsed');
  $(element).parents('.chat-frame').find('input').focus();
}
function CloseChat(element){
  $(element).parents('.chat-frame').remove();
}
var newMsgs = [];
function updateMessages(allMessagesId, username, limit, offset, type = 'UPDATE'){
  var csrf = $('#csrf_logged').attr('content');
  var user_id = $("#user_id").attr("content");
  $.ajax({
    url: 'ajax/messages.php',
    type: 'POST',
    data: {
      'showOurChat': '1',
      'username': username,
      'csrf': csrf,
      'limit': limit,
      'offset': offset
    },
    success: function(response){
      var json = JSON.parse(response);
      for (var x in json){
        for(var o in allMessagesId){
          try{
            if(allMessagesId[o] === json[x]['id']){
              json.splice(x, 1);
            }
          }catch(error){
            //nothing
          }
        }
      }
      if(json.length > 0){
        var newLitems = '';
        for(var msj in json){
          if(allMessagesId.includes(json[msj]['id']) === false){
            allMessagesId.push(json[msj]['id']);
          }
          //allMessages = allMessages.concat(json);
          var message = json[msj];
          var myDate = new Date(message['sending_time']);
          if(user_id === message['sender_id']){
            //if message is by me
            newLitems += '<li style="width:100%;"><div class="msj-rta macro"><div class="text text-r">';
            if(myDate.getHours() < 12){
              newLitems += "<p>"+message['body']+"</p><p><small>"+myDate.getHours()+":"+myDate.getMinutes()+" AM</small></p>";
            }else{
              newLitems += "<p>"+message['body']+"</p><p><small>"+myDate.getHours()+":"+myDate.getMinutes()+" AM</small></p>";
            }
            newLitems += '</div><div class="avatar" style="padding:0px 0px 0px 10px !important"></div></div></li>';
          }else{
            newLitems += '<li style="width:100%"><div class="msj macro"><div class="text text-l">';
            if(myDate.getHours() < 12){
              newLitems += "<p>"+message['body']+"</p><p><small>"+myDate.getHours()+":"+myDate.getMinutes()+" AM</small></p>";
            }else{
              newLitems += "<p>"+message['body']+"</p><p><small>"+myDate.getHours()+":"+myDate.getMinutes()+"</small></p>";
            }
            newLitems += '</div></div></li>';
          }
        }
        if(type === 'UPDATE'){
          $('#msgs-ar').append(newLitems);
          $('.messages-container').animate({
            scrollTop: $("#scrollToHere").offset().top
          }, 1000);
          SeenMessages(username); // mark as seen
        }else if(type === 'PREPEND'){
          $('#msgs-ar').prepend(newLitems);
        }
      }
    },
    error: function(jqXHR, textStatus, errorThrown){
      console.log(textStatus, errorThrown);
    }
  });
}

var allMessagesId = [];
function PopChatUp(username, limit, offset){
  var csrf = $('#csrf_logged').attr('content');
  var user_id = $("#user_id").attr("content");
  var HisName = ''; // to get his name

  $.ajax({                     // This ajax request to get the partener name
    url: 'ajax/messages.php',  // to use in the chat and in the next ajax
    type: 'POST',              // It also checks if the username entered is true connected with a real username
    data: {
      'GetMsgrName': '1',
      'username': username,
      'csrf': csrf
    },
    success: function(response1){

      if(response1.includes('Success')){
        HisName = response1.replace('Success:', '');
        $.ajax({
          url: 'ajax/messages.php',
          type: 'POST',
          data: {
            'showOurChat': '1',
            'username': username,
            'csrf': csrf,
            'limit': limit,
            'offset': offset
          },
          success: function(response){
            var json = JSON.parse(response);
            var ChatHtml = '';
            ChatHtml += '<div class="col-xs-10 col-xs-offset-1 col-sm-4 col-sm-offset-0-5 col-md-3 col-lg-2 chat-frame expanded"><div class="partener" onclick="ExpandCollapseChat(this);">';
            ChatHtml += "<a href='profile.php?username="+username+"' class='name'>"+HisName+"</a><i class='pull-right fa fa-times cross' onclick='CloseChat(this);'></i></div>";
            ChatHtml += '<ul class="messages-container"><div id="scrollTop"></div><div id="msgs-ar">';
            for(var msj in json){
              if(allMessagesId.includes(json[msj]['id']) === false){
                allMessagesId.push(json[msj]['id']);
              }
              //allMessages = allMessages.concat(json);
              var message = json[msj];
              var myDate = new Date(message['sending_time']);
              if(user_id === message['sender_id']){
                //if message is by me
                ChatHtml += '<li style="width:100%;"><div class="msj-rta macro"><div class="text text-r">';
                if(myDate.getHours() < 12){
                  ChatHtml += "<p>"+message['body']+"</p><p><small>"+myDate.getHours()+":"+myDate.getMinutes()+" AM</small></p>";
                }else{
                  ChatHtml += "<p>"+message['body']+"</p><p><small>"+myDate.getHours()+":"+myDate.getMinutes()+" AM</small></p>";
                }
                ChatHtml += '</div><div class="avatar" style="padding:0px 0px 0px 10px !important"></div></div></li>';
              }else{
                ChatHtml += '<li style="width:100%"><div class="msj macro"><div class="text text-l">';
                if(myDate.getHours() < 12){
                  ChatHtml += "<p>"+message['body']+"</p><p><small>"+myDate.getHours()+":"+myDate.getMinutes()+" AM</small></p>";
                }else{
                  ChatHtml += "<p>"+message['body']+"</p><p><small>"+myDate.getHours()+":"+myDate.getMinutes()+"</small></p>";
                }
                ChatHtml += '</div></div></li>';
              }
            }
            ChatHtml += '</div><div id="scrollToHere"></div></ul><div><div class="msj-rta macro" style="margin:auto">';
            ChatHtml += '<div class="text text-r" style="background:whitesmoke !important">';
            ChatHtml += '<input dir="auto" class="mytext" id="sendMsg" placeholder="Type a message" onkeydown="sendMessage(this, event);" data-msg="'+username+'" /></div></div></div></div>';
            $('#Chat-Area').html(ChatHtml);
            $('#sendMsg').focus();
            $('.messages-container').animate({
              scrollTop: $("#scrollToHere").offset().top
            }, 200);
            var MsgsOffset = 20;
            $('.messages-container').scroll(function(){
              setTimeout(function(){
                if($('.messages-container').scrollTop() <= ($("#scrollTop").height() + 200)){
                  //call the function to get older msgs/
                  updateMessages(allMessagesId, username, 20, MsgsOffset, 'PREPEND');
                  MsgsOffset = MsgsOffset + 20;
                }
              }, 500);
            });
            setInterval(function(){
              updateMessages(allMessagesId, username, 20, 0, 'UPDATE');
            }, 2000);
            SeenMessages(username); // mark as seen
          },
          error: function(jqXHR, textStatus, errorThrown){
            console.log(textStatus, errorThrown);
          }
        });
      }
    }
  });
}
function sendMessage(element, event){
  if(event.keyCode == 13 && $(element).val()){
    var csrf = $('#csrf_logged').attr('content'),
    to = $(element).attr('data-msg'),
    body = $(element).val();
    $.ajax({
      url: 'ajax/messages.php',
      type: 'POST',
      data: {
        'sendAMessage': '1',
        'to': to,
        'body': body,
        'csrf': csrf
      },
      success: function(response){
        if(response === 'Success'){
          $(element).val('');
        }
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.log(textStatus, errorThrown);
      }
    });
  }
}


/*======================================================
===================* Setup chatters *===================
======================================================*/
function setupMessengers(){
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
    url: 'ajax/messages.php',
    type: 'POST',
    data: {
      'setupMessengers': '1',
      'csrf': csrf
    },
    success: function(response){
      json = JSON.parse(response);
      if(json.length > 13){
        json = json.slice(0, 12)
      }
      var usrsHtml = '';
      for(var usr in json){
        var usrname = json[usr]["username"];
        var TheFunction = "PopChatUp('"+usrname+"', '"+json[usr]['user_id']+"', 20, 0)";
        usrsHtml += '<a href="#" onclick="'+TheFunction+'" title="'+json[usr]['name']+'">';
        usrsHtml += "<div class='partener-thumbnail' style='background: url("+json[usr]['image_url']+");'>";
        if(json[usr]['seen_state'] === '1'){
          if(json[usr]['active_state'] === '1'){
            usrsHtml += "<i class='msg-mark online'></i>";
          }
        }else{
          usrsHtml += "<i class='msg-mark new'></i>";
        }
        usrsHtml += "</div></a>";
      }
      $('#sidenav').html(usrsHtml);
    },
    error: function(jqXHR, textStatus, errorThrown){
      console.log(textStatus, errorThrown);
    }
  });
}
setupMessengers();
setInterval(function(){
  setupMessengers(); // Smoothly update each 5 mins
}, 5000);
/*===========================================
============* Mark Message Seen *============
===========================================*/
function SeenMessages(user_id){ // Auto detecting id or username
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
    url: 'ajax/messages.php',
    type: 'POST',
    data: {
      'SeenMessages': '1',
      'csrf': csrf,
      'user_id': user_id
    },
    success: function(response){
      if(response === 'Success'){
        setupMessengers(); // update sidebar seen icons
      }
    },
    error: function(jqXHR, textStatus, errorThrown){
      console.log(textStatus, errorThrown);
    }
  });
}
function markeMeOffline(){
  //make me offline when i logout
  var csrf = $('#csrf_logged').attr('content');
  $.ajax({
    url: 'ajax/messages.php',
    type: 'POST',
    data: {
      'MakeMeOffline': '1',
      'csrf': csrf
    },
    success: function(response){
      console.log(response);
    },
    error: function(jqXHR, textStatus, errorThrown){
      //console.log(textStatus, errorThrown);
    }
  });
}
$(window).on('unload', function(){
  markeMeOffline()
});
