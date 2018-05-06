/*===========================================
============* Changing Password *============
===========================================*/
if($("#ChangePassword")){
  $("#ChangePassword").click(function(e){
    e.preventDefault();
    var oldpass  = $("#oldpass").val(),
        newpass  = $("#newpass").val(),
        confpass = $("#confpass").val(),
        csrfpass = $("#csrf_pass").val();
    if(oldpass.length !== 0 && newpass.length !== 0 && confpass.length !== 0){
      $.ajax({
        url: 'ajax/settings.php',
        type: 'POST',
        data: {
          'change_password': '1',
          'oldpass': oldpass,
          'newpass': newpass,
          'confpass': confpass,
          'csrf_pass': csrfpass
        },
        success: function(response){
          switch(response){
            case 'notMatches':
              $("#errorMsg1").css({'color': 'red'});
              $("#errorMsg1").html('Password and confirmation are not identical.');
            break;
            case 'notValid':
              $("#errorMsg1").css({'color': 'red'});
              $("#errorMsg1").html("Please choose a valid new password. which can't be less than 4 or more then 40.");
            break;
            case 'wrongOld':
              $("#errorMsg1").css({'color': 'red'});
              $("#errorMsg1").html('You entered a wrong password.');
            break;
            case 'newEqold':
              $("#errorMsg1").css({'color': 'red'});
              $("#errorMsg1").html('Your new password is the same as the old one.');
            break;
            case 'Success':
              $("#errorMsg1").html("");
              alertify.success('Your password has been successifully changed');
              $("#oldpass").val('');
              $("#newpass").val('');
              $("#confpass").val('');
            break;
            default:
              alertify.error('An error ocurred. reload the page and try again.');
            break;
          }
        }
      });
    }else{
      $("#errorMsg1").html('You must fill in all these 3 fields');
    }
  });
}



/*==========================================
==========* Changing Information *==========
==========================================*/
if($("#ChangeInfo")){
  $("#ChangeInfo").click(function(){
    var name  = $("#name").val(),
        bio = $("#bio").val(),
        town = $("#town").val(),
        website = $("#website").val(),
        csrfname = $("#csrf_name").val();
    if(name.length !== 0){
      $.ajax({
        url: 'ajax/settings.php',
        type: 'POST',
        data: {
          'changeInfo': '1',
          'name': name,
          'bio': bio,
          'town': town,
          'website': website,
          'csrf_name': csrfname
        },
        success: function(response){
          switch(response){
            case 'notValid':
              $("#errorMsg2").css({'color': 'red'});
              $("#errorMsg2").html("Please enter a valid name. which can't be less than 2 or more then 20.");
            break;
            case 'Success':
              $("#errorMsg2").css({'color': '#00ca38'});
              $("#errorMsg2").html("");
              alertify.success('Your info has been successifully changed');
            break;
            case 'urlnotvalid':
              $("#errorMsg2").css({"color": 'red'});
              $("#errorMsg2").html("You've entered a not valid website url");
            break;
            default:
              alertify.error('An error ocurred. reload the page and try again.');
              console.log(response);
            break;
          }
        }
      });
    }else{
      $("#errorMsg2").html("You must fill in required fields");
    }
  });
}
