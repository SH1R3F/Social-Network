<?php require_once 'core/init.php';
if(Input::exists()){
  if(Token::check('pre_login', Input::get('csrf'), 'Delete')){
    $validate = new Validation();
    $validate->check($_POST, array(
      "credintial" => array(
        "required" => true,
        "min" => 2,
        "max" => 40
      ),
      "password" => array(
        "required" => true,
        "min" => 4,
        "max" => 40
      )
    ));
    if($validate->passed()){
      $user = new User();
      if($user->login('users', Input::get('credintial'), Input::get('password'))){
        Redirect::to('index.php');
      }else{
        $credErr = "You've entered a not valid data";
      }
    }else{
      $err = $validate->errors();
    }
  }else{
    Redirect::re();
  }
}
?>
<?php get_header(); ?>

<!-- LOGIN BOX -->
<div class='login-box'>
  <div class='title'>
    <img class='pull-left' src='https://cdn1.iconfinder.com/data/icons/jetflat-multimedia-vol-4/90/0042_099_lock_access_denied_blocked-512.png' />
    <div class='pull-left'>
      <h3><?php echo Config::get('site/name'); ?></h3>
      <span>Login to continue</span>
    </div>
  </div>
  <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
    <div class='inputs'>
      <a style="color: #000; margin-bottom: 15px; display: block;"><?php echo @$credErr; ?></a>
      <div class='group-form'>
        <?php  echo '<a style="color: green; display: block; margin-bottom: 5px;">'.Session::flash('created').'</a>'; ?>
        <input id='credintial' type='text' name='credintial' placeholder='Your email or username' required="on" value="<?php echo Input::get('credintial'); ?>" />
      </div>
      <div class='group-form'>
        <input id='password' type='password' name='password' placeholder='Your password' required="on" />
      </div>
      <div class='options'>
        <a href="register.php">Register</a>
        <a href="forget-password.php">Forgot your password?</a>
      </div>
    </div>
    <div class='submit'>
      <input type="hidden" name="csrf" value="<?php echo Token::generate('pre_login'); ?>" />
      <button type='submit'><i class='fa fa-arrow-right'></i></button>
    </div>
  </form>
</div><!-- LOGIN BOX -->
<?php get_footer(); ?>
