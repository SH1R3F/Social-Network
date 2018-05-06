<?php
require_once 'core/init.php';

if(!$user->isLogged() && Input::exists()){
  if(Token::check('csrf', Input::get('csrf'), 'Delete')){
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
        Session::flash('login_success', 'You\'ve been successifully logged in.');
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
require_once 'header.php';
?>
<header class='landing' id="header">
  <div class='container'>
    <div class='intro'>
      <h1>Socialize.</h1>
      <p>Searching for online friends is difficult. With a bit of FUN & social interactions, everything becomes much simpler!</p>
      <?php if(!$user->isLogged()): ?>
        <div class="card card-container">
            <!-- <img class="profile-img-card" src="//lh3.googleusercontent.com/-6V8xOA6M7BA/AAAAAAAAAAI/AAAAAAAAAAA/rzlHcD0KYwo/photo.jpg?sz=120" alt="" /> -->
            <img id="profile-img" class="profile-img-card" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
            <p id="profile-name" class="profile-name-card"></p>
            <form class="form-signin" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <span id="reauth-email" class="reauth-email"><?php echo @$credErr; ?></span>
                <input type="email" id="inputEmail" class="form-control" placeholder="Email address" name='credintial' required autofocus>
                <input type="password" id="inputPassword" class="form-control" placeholder="Password" name='password' required>
                <div id="remember" class="checkbox">
                    <label>
                        <input type="checkbox" value="remember-me" name='rememberme'> Remember me
                    </label>
                </div>
                <input type='hidden' name='csrf' value="<?php echo Token::generate('csrf'); ?>"/>
                <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit">Sign in</button>
            </form><!-- /form -->
            <a href="forget-password.php" class="forgot-password all-block">
                Forgot the password?
            </a>
            <a href="register.php" class="forgot-password all-block">
                Create an account
            </a>
        </div><!-- /card-container -->
      <?php endif; ?>
    </div>
  </div>
</header>

<section class='statistics' id="statistics">
  <div class='container'>
    <div class='col-xs-6'>
      <div class='bubble-container users'>
        <div class='desc'>
          <?php
          $usrs = DB::getInstance()->get('id', 'users')->count();
          $liks = DB::getInstance()->get('id', 'likes')->count();
          ?>
          <h2><?php echo $usrs; ?></h2>
          <span>Registered users</span>
        </div>
      </div>
    </div>
    <div class='col-xs-6'>
      <div class='bubble-container likes'>
        <div class='desc'>
          <h2><?php echo $liks; ?></h2>
          <span>Posted Likes</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class='our-team' id="team">
  <div class='container'>
    <h3 class='page-header'>Meet Our Team</h3>
      <div class='col-xs-12 col-sm-6'>
        <div class='flip'>
          <div class='frontside'>
            <div class='img rami'></div>
            <h3>Mahmoud Shiref</h3>
            <p>This is basic card with image on top, title, description and button.</p>
          </div>
          <div class='backside'>
            <div class='layer'>
              <h3>Mahmoud Shiref</h3>
              <p>This is basic card with image on top, title, description and button.This is basic card with image on top, title, description and button.This is basic card with image on top, title, description and button.</p>
              <div class='social'>
                <a href='#'><i class='fa fa-google-plus'></i></a>
                <a href='#'><i class='fa fa-twitter'></i></a>
                <a href='#'><i class='fa fa-youtube'></i></a>
                <a href='#'><i class='fa fa-instagram'></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class='col-xs-12 col-sm-6'>
        <div class='flip'>
          <div class='frontside'>
            <div class='img robot'></div>
            <h3>Mahmoud Shiref</h3>
            <p>This is basic card with image on top, title, description and button.</p>
          </div>
          <div class='backside'>
            <div class='layer'>
              <h3>Mahmoud Shiref</h3>
              <p>This is basic card with image on top, title, description and button.This is basic card with image on top, title, description and button.This is basic card with image on top, title, description and button.</p>
              <div class='social'>
                <a href='#'><i class='fa fa-google-plus'></i></a>
                <a href='#'><i class='fa fa-twitter'></i></a>
                <a href='#'><i class='fa fa-youtube'></i></a>
                <a href='#'><i class='fa fa-instagram'></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
  </div>
</section>

<section class='subscribe'>
  <div class='container'>
    <h3 class='page-header'>Keep in Touch with the Community</h3>
    <p>Subscribe to our newsletter and stay updated with latest news & events!</p>
    <div class='form'>
      <input type='email'/>
      <input type='submit' value='Subscribe' />
    </div>
  </div>
</section>


<section class='contact-us' id='contactus'>
  <div class='container'>
    <div class='col-xs-12 col-sm-6'>
      <div class='input'>
        <label>Your Name: </label>
        <input type='text' name='name' placeholder='Your name.'/>
      </div>
      <div class='input'>
        <label>Your Email: </label>
        <input type='email' name='email' placeholder='Your email.' />
      </div>
      <div class='input'>
        <label>Phone number: </label>
        <input type='number' name='number' placeholder='Your phone number.' />
      </div>
    </div>
    <div class='col-xs-12 col-sm-6'>
      <div class='input'>
        <label>Your Message: </label>
        <textarea></textarea>
      </div>
    </div>
    <div class='col-xs-12'>
      <input type='submit' value='Send' />
    </div>
  </div>
</section>

<footer class='landing'>
  <div class='container'>
    <div class='row'>
      <div class='col-xs-6'>
        Coded & Programmed by: <a href='https://www.facebook.com/slumdog.mellionare'>Mahmoud Shiref</a>
      </div>
      <div class='col-xs-6'>
        <div class='social'>
          <a href='#'><i class='fa fa-facebook'></i></a>
          <a href='#'><i class='fa fa-youtube'></i></a>
          <a href='#'><i class='fa fa-google-plus'></i></a>
          <a href='#'><i class='fa fa-instagram'></i></a>
        </div>
      </div>
    </div>
  </div>
</footer>

<?php require_once 'footer.php'; ?>
