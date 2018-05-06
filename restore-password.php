<?php
require_once 'core/init.php';
get_header();
if(Input::exists('GET')){
  if($user->checkToken(Input::get('token', 'GET'))){
    if(Input::exists('POST')){
      if(Token::check('csrf', Input::get('csrf'), 'Delete')){//checking csrf
        if(Input::get('Newpassword') !== "" && Input::get('Newpassword') !== ""){
          $newpass  = Input::get('Newpassword');
          $confpass = Input::get('confpassword');
          if(
            strlen($confpass) < 4 || strlen($newpass) < 4 || //Min
            strlen($confpass) > 40 || strlen($newpass) > 40 //Max
          ){
            $state = '<a style="color: red; margin-bottom: 15px; display: block;">please enter a valid password.</a>';
          }elseif($newpass !== $confpass){
            $state = '<a style="color: red; margin-bottom: 15px; display: block;">Password and confirmation are not identical</a>';
          }else{

            //if password is valid change it and remove the token from db
            if($user->resetPassword($user->checkToken(Input::get('token', 'GET')), $newpass)):?>
            <div class='container settings-container'>
              <!-- RESTORING PASSWORD -->
              <div class='settings password-change'>
                <div class='title text-center'>
                  <h3>Password successifully changed</h3>
                </div>
                <p style="padding: 20px 0 5px">Your password had been successifully changed. now you can <a href="login.php">login</a>.</p>
              </div><!-- RESTORING PASSWORD -->
            </div>
            <?php
              get_footer();
              exit();
            endif;
          }
        }else{
          $state = '<a style="color: red; margin-bottom: 15px; display: block;">You can\'t leave these fields empty</a>';
        }
      }else{
        Redirect::re();
      }
    }
  }
}
?>

<div class='container settings-container'>
  <!-- RESTORING PASSWORD -->
  <div class='settings password-change'>
    <div class='title text-center'>
      <h3><?php echo (Input::exists('GET') && $user->checkToken(Input::get('token', 'GET')))? 'Change password' : 'Invalid token.' ; ?></h3>
    </div>
    <?php if(Input::exists('GET') && $user->checkToken(Input::get('token', 'GET'))): ?>
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . Input::get('token', 'GET') ; ?>" method="POST">
        <div class='inputs'>
          <?php echo @$state; ?>
          <div class='group-form'>
            <input type="password" name="Newpassword" id="Newpassword" placeholder="Choose New Password" />
          </div>
          <div class='group-form'>
            <input type="password" name="confpassword" id="confpassword" placeholder="Confirm your password" />
          </div>
        </div>
        <input type="hidden" id="csrf_pass" value="<?php echo Token::generate('csrf_pass'); ?>" />
        <div class='submit' style='position: relative'>
          <input type="hidden" name="csrf" value="<?php echo Token::generate('csrf'); ?>" />
          <button type='submit'><i class='fa fa-arrow-right'></i></button>
        </div>
      </form>
    <?php else: ?>
      <p style="padding: 20px 0 5px">The token you've followed is invalid. check your email and try follow the link again or ask for a new link from <a href="forget-password.php">here</a></p>
    <?php endif; ?>
  </div><!-- RESTORING PASSWORD -->
</div>
<?php get_footer(); ?>
