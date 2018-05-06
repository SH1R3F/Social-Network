<?php
require_once 'core/init.php';
if(Input::exists()){
  if(Token::check('csrf', Input::get('csrf'), 'Delete')){
    if(preg_match('/^\S+@\S+\.\S+$/', Input::get('credintial'))){
      $restoring = $user->restorePassword(Input::get('credintial'));
      if($restoring){
//        echo "Sending email is not available currently. follow this link to change pass: http://mahmoud-sherif.esy.es/SocialNetwork1/restore-password.php?token=" . $restoring;
        echo "Sending email is not available currently.";
        $state = '<a style="color: #00ca38; margin-bottom: 15px; display: block;">We\'ve sent you an email with password restore link.</a>';
      }else{
        $state = '<a style="color: red; margin-bottom: 15px; display: block;">The email you\'ve entered is not exists in our database.</a>';
      }
    }else{
      $state = '<a style="color: red; margin-bottom: 15px; display: block;">Please enter a valid email</a>';
    }
  }else{
    Redirect::re();
  }
}
get_header();
?>
<div class='container settings-container'>
  <!-- RESTORING PASSWORD -->
  <div class='settings password-change'>
    <div class='title text-center'>
      <h3>Restore your password</h3>
    </div>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
      <div class='inputs'>
        <?php echo @$state; ?>
        <div class='group-form'>
          <input type="email" name="credintial" id="credintial" value="<?php echo Input::get('credintial'); ?>" placeholder="Your email" />
        </div>
      </div>
      <input type="hidden" id="csrf_pass" value="<?php echo Token::generate('csrf_pass'); ?>" />

      <div class='submit' style='position: relative'>
        <input type="hidden" name="csrf" value="<?php echo Token::generate('csrf'); ?>" />
        <button type='submit'><i class='fa fa-arrow-right'></i></button>
      </div>
    </form>
  </div><!-- RESTORING PASSWORD -->
</div>
<?php
get_footer();
?>
