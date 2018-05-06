<?php require_once 'core/init.php';
if(Input::exists()){
  if(Token::check('pre_login', Input::get('csrf'), 'Delete')){
    $validate = new Validation();
    $validate->check($_POST, array(
      "name" => array(
        "required" => true,
        "min" => 2,
        "max" => 20,
        "regexp" => "/^[a-z ,.'-]+$/i"
      ),
      "username" => array(
        "required" => true,
        "min" => 2,
        "max" => 30,
        "regexp" => "/^(?=.*[A-z])[A-Za-z0-9]+(?:[_-][A-Za-z0-9]+)*$/",
        "unique" => "users->username"
      ),
      "email" => array(
        "required" => true,
        "min" => 4,
        "max" => 40,
        "regexp" => "/^\S+@\S+\.\S+$/",
        "unique" => "users->email"
      ),
      "password" => array(
        "required" => true,
        "min" => 4,
        "max" => 40,
      ),
      "passconfirm" => array(
        "required" => true,
        "min" => 4,
        "max" => 40,
        "matches" => "password"
      )
    ));
    if($validate->passed()){
      //Hashing Password
      $hash = new PasswordHash(8, false);
      $hashed = $hash->HashPassword(Input::get('password'));
      $user = new User();
      try{
        if($user->create('users', array(
          'name' => Input::get('name'),
          'username' => Input::get('username'),
          'email' => Input::get('email'),
          'password' => $hashed,
          'joined' => Date('Y-m-d h:i:s')
        ))){
          Session::flash('created', 'Your account have been successifully created... now you can login.');
          Redirect::to('login.php');
          print_r($_SESSION);
        }
      }catch(Exception $e){
        die($e->getMessage());
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
      <span>Create an account</span>
    </div>
  </div>
  <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
    <div class='inputs'>
      <a style="color: #000; margin-bottom: 15px; display: block;"><?php echo @$credErr; ?></a>
      <div class='group-form'>
        <?php  echo '<a style="color: green; display: block; margin-bottom: 5px;">'.Session::flash('created').'</a>'; ?>
        <input type="text" name="name" id="name" value="<?php echo Input::get('name'); ?>" placeholder="Enter your name" />
        <a style="color: #000; margin-bottom: 15px; display: block;"><?php echo @$err['name']; ?></a>
      </div>
      <div class='group-form'>
        <input type="text" name="username" id="username" value="<?php echo Input::get('username'); ?>" placeholder="Pick up a username" />
        <a style="color: #000; margin-bottom: 15px; display: block;"><?php echo @$err['username']; ?></a>
      </div>

      <div class='group-form'>
        <input type="text" name="email" id="email" value="<?php echo Input::get('email'); ?>" placeholder="Enter your email" />
        <a style="color: #000; margin-bottom: 15px; display: block;"><?php echo @$err['email']; ?></a>
      </div>

      <div class='group-form'>
        <input type="password" name="password" id="password" value="<?php echo Input::get('password'); ?>" placeholder="Choose a strong password" />
        <a style="color: #000; margin-bottom: 15px; display: block;"><?php echo @$err['password']; ?></a>
      </div>

      <div class='group-form'>
        <input type="password" name="passconfirm" id="passconfirm" value="<?php echo Input::get('passconfirm'); ?>" placeholder="Confirm your password" />
        <a style="color: #000; margin-bottom: 15px; display: block;"><?php echo @$err['passconfirm']; ?></a>
      </div>
    </div>
    <div class='submit'>
      <input type="hidden" name="csrf" value="<?php echo Token::generate('pre_login'); ?>" />
      <button type='submit'><i class='fa fa-arrow-right'></i></button>
    </div>
  </form>
</div><!-- LOGIN BOX -->

<?php get_footer(); ?>
