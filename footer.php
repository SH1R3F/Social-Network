    <div id='Chat-Area'></div>
    <!-- INCLUDE SOME JS FILES -->
    <?php includeIn('landing.js', array('landing.php')); ?>
    <?php includeIn('settings.js', array('settings.php')); ?>
    <script>
      $(document).ready(function(){
        //for navbar active class organizing.
        var path = window.location.pathname;
        var page = path.split("/").pop();
        $("a[href='"+page+"']").parent().addClass('active');
        $("a[href='"+page+"']").parents('.litem').siblings().removeClass('active');

      });
      if(document.cookie === ""){
        alertify.alert("It seems that cookies are disabled in your browser. We're sorry but you can't use our website before enabling your cookies.");
      }
    </script>
  </body>
</html>
