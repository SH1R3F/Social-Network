<?php
require_once 'core/init.php';
get_header();
$user = new User();
?>
<div class='container settings-container'>
  <!-- PASSWORD UPDATING -->
  <div class='settings password-change'>
    <div class='title text-center'>
      <h3>Change your password</h3>
    </div>
    <div class='inputs'>
      <a style="color: red; margin-bottom: 10px; display: block;" id="errorMsg1"></a>
      <div class='group-form'>
        <input type="password" name="oldpass" id="oldpass" placeholder="Type your old password" />
      </div>
      <div class='group-form'>
        <input type="password" name="newpass" id="newpass" value="<?php echo Input::get('newpass'); ?>" placeholder="Your new password" autocomplete="off" />
      </div>
      <div class='group-form'>
        <input type="password" name="confpass" id="confpass" value="<?php echo Input::get('confpass'); ?>" placeholder="Confirm new password" autocomplete="off" />
      </div>
    </div>
    <input type="hidden" id="csrf_pass" value="<?php echo Token::generate('csrf_pass'); ?>" />

    <div class='submit' style='position: relative'>
      <input type="hidden" name="csrf" value="<?php echo Token::generate('csrf'); ?>" />
      <button id="ChangePassword"><i class='fa fa-arrow-right'></i></button>
    </div>
  </div><!-- PASSWORD UPDATING -->

  <!-- CHANGIN INFO -->
  <div class='settings basic-info'>


    <div class='title text-center'>
      <h3>Change your info</h3>
    </div>
    <div class='inputs'>
      <a style="color: red; margin-bottom: 10px; display: block;" id="errorMsg2"></a>
      <div class='group-form'>
        <input type="text" name="name" id="name" placeholder="Your name" value="<?php echo htmlspecialchars($user->data()->name); ?>" />
      </div>
      <div class='group-form'>
        <textarea id="bio" placeholder="Breifly write some information about you."><?php echo str_replace("<br/>", "", $user->data()->bio); ?></textarea>
      </div>
      <div class='group-form'>
        <input type="text" name="town" id="town" placeholder="Your home town" value="<?php echo htmlspecialchars($user->data()->town); ?>" />
      </div>
      <div class='group-form'>
        <input type="url" name="website" id="website" placeholder="Your website" value="<?php echo htmlspecialchars($user->data()->website); ?>" />
      </div>
    </div>

    <div class='submit' style='position: relative'>
      <input type="hidden" id="csrf_name" value="<?php echo Token::generate('csrf_name'); ?>" />
      <button id="ChangeInfo"><i class='fa fa-arrow-right'></i></button>
    </div>
  </div><!-- CHANGIN INFO -->
</div>


<?php
 get_footer();
?>
