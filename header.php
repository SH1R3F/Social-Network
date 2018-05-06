<?php $user = new User(); ?>
<!DOCTYPE html>
<html>
  <head>
    <!-- META TAGS -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if($user->isLogged()): ?>
      <meta id="csrf_logged" content="<?php echo Token::generate('csrf_logged'); ?>">
      <meta id="user_id" content="<?php echo Session::get(Config::get('sessions/user_login')); ?>" />
    <?php endif; ?>
    <noscript>
      <meta http-equiv="refresh" content="0; url=includes/no-js.html" />
    </noscript>
    <title>
      <?php
      $page = basename($_SERVER['PHP_SELF']);
        switch($page){
          case 'register.php':
            echo "Sign up | " . Config::get('site/name');
          break;
          case 'login.php':
            echo "Sign In | " . Config::get('site/name');
          break;
          default:
            echo Config::get('site/name');
          break;
        }
      ?>
    </title>
    <!-- INCLUDED FONTS -->
    <link href="https://fonts.googleapis.com/css?family=Chicle|Cairo|Black+Han+Sans|Gaegu|Gamja+Flower|Gugi" rel="stylesheet">
    <!-- INCLUDED CSS LIBRARIES -->
    <link rel="stylesheet" href="css/libs/font-awesome-4.7.min.css">
    <link rel="stylesheet" href="css/libs/bootstrap-3.3.7.min.css">
    <link rel="stylesheet" href="css/libs/alertify.min.css"/>
    <!-- INCLUDED JAVASCRIPT LIBRARIES -->
    <script src="js/libs/jquery-3.3.1.min.js"></script>
    <script src="js/libs/bootstrap-3.3.7.min.js"></script>
    <script src="js/libs/alertify.min.js"></script>

    <!-- INCLUDING MY CSS FILES -->
    <link rel='stylesheet' href='css/style.css' />
    <link rel='stylesheet' href='css/landing.css' /> <!-- Style of the landing page: [ landing.php ] -->
    <link rel='stylesheet' href='css/settings.css' /> <!-- Style of the Settings page: [ settings.php ] -->
    <link rel='stylesheet' href='css/messenger.css' /> <!-- Style of the chats content -->
    <?php includeIn('login.css', array('login.php', 'register.php')); ?>

    <!-- INCLUDING MY JS FILES -->
    <?php
    $user = new User();
    if($user->isLogged()){
      includeIn('messages.js', array('!landing.php'));
      includeIn('notifications.js', array('!landing.php'));
      includeIn('profile.js', array('!landing.php'));
      includeIn('posts.js', array('index.php', 'profile.php', 'post.php'));
    }
    ?>
  </head>
  <body>
  <nav class="navbar navbar-default">
    <div class="container">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="index.php"><?php echo Config::get('site/name'); ?></a>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <?php if($user->isLogged()): ?>
          <form class="navbar-form navbar-left" action='search.php' method='GET'>
            <div class="form-group">
              <input type="text" class="form-control" placeholder="Search" name='q'>
            </div>
          </form>
        <?php endif; ?>

        <ul class="nav navbar-nav navbar-right">
          <?php if($page === 'landing.php'): ?>
            <li class="active"><a href="#header">Home <span class="sr-only">(current)</span></a></li>
            <li><a href="#contactus">Contact Us</a></li>
            <li><a href="#statistics">Stats</a></li>
            <li><a href="#team">Our Team</a></li>

            <li class="dropdown">
              <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></a>
              <ul class="dropdown-menu">
                <?php if(!$user->isLogged()): ?>
                  <li><a href="login.php">Login</a></li>
                  <li><a href="register.php">Register</a></li>
                <?php else: ?>
                  <li><a href="index.php">Timeline</a></li>
                  <li><a href="profile.php">Profile</a></li>
                <?php endif; ?>
              </ul>
            </li>

          <?php elseif($user->isLogged()): // On Other User Logged In Pages ?>
            <li class="litem"><a href="index.php">Home <span class="sr-only">(current)</span></a></li>
            <li class='litem'><a href="profile.php">Profile</a></li>

            <li class="litem dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Notifications<span class='notifs'><i></i></span> <span class="caret"></span></a>
              <ul class="dropdown-menu" id="notifications">
              </ul>
            </li>

            <li class="litem dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li><a href="landing.php">Home <span class="sr-only">(current)</span></a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
          <?php endif; ?>

        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav>

  <?php
  if($user->isLogged() && $page !== 'landing.php'): ?>
    <!-- Sidebar chat heads -->
    <div class="sidenav" id='sidenav'>
    </div><!-- Sidebar chat heads -->
  <?php endif; ?>
