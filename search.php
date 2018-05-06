<?php
require_once 'core/init.php';
if(!isset($_GET['q'])){
  Redirect::to('index.php');
}
get_header();
?>
<div class='search-page'>
  <div class='container'>
    <div class='results'>
      <!-- Start Content -->
      <div class="content" style="width: 100%;">
        <?php
        if(!strlen(str_replace(' ', '', $_GET['q']))){
            ?>
            <div class='one-post'>
              <div class='publishInfo'>
                <p style="text-align: center; font-size: 1.2rem;">You did'nt enter a value to search for. Try again with a valid one.</p>
              </div>
            </div>
            <?php
          //end the page;
          get_footer();
          die();
        }
        $search = htmlspecialchars($_GET['q']);
        $results = $user->Search($search);
        if(count($results)):
          foreach($results as $result): ?>
            <div class='one-result'>
              <a href='profile.php?username=<?php echo $result->username; ?>'>
                <div class='pull-left profileImg' style='background: url("<?php echo $result->image_url; ?>");'></div>
              </a>
              <div class='pull-left'>
                <a href='profile.php?username=<?php echo $result->username; ?>'>
                  <h4><?php echo $result->name; ?></h4>
                </a>
                <a href='profile.php?username=<?php echo $result->username; ?>' class='username'>@<?php echo $result->username; ?></a>
              </div>
              <div style='clear: both'></div>
            </div>
          <?php endforeach;
        else: ?>
        <div class='one-result'>
          <p style="text-align: center; font-size: 1.2rem;">No results found. Try another search using more specific words.</p>
        </div>
        <?php endif; ?>

      </div>
      <!-- End Content -->
    </div>
  </div>
</div>
<?php get_footer(); ?>
