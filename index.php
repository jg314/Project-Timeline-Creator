<?php include('includes/timeline-maker.php'); ?>

<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- Consider adding an manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">

  <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/b/378 -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title></title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" type="text/css" href="css/jquery-ui.css" />
  <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>

<body>

  <div id="container">
    <header>

    </header>
    <div id="main" role="main">
      <h1>Project Timeline Generator</h1>
      <?php if (isset($_POST['start_date'])) $due_dates = build_timeline(); ?>
            
      <p>The final payment will be added <?php echo DAYS_TO_FINAL_PAYMENT; ?> calendar days after the last project step.</p>
      <form action="" method="post" id="timeline-form">
          <div id="timeline-steps">
              <p>Project Start Date: <input type="text" name="start_date" id="start_date" <?php if(isset($_POST['start_date'])) echo 'value="' . $_POST['start_date'] . '"'; ?>/></p>
              <?php echo build_form_fields(); ?>
          </div>
              <p><input type="button" value="Add Project Step" id="add_step" /> <input type="button" value="Delete Last Project Step" id="delete_step" /></p>
              <p><input type="submit" value="Submit" /></p>
      </form>
      
      
    </div>
    <footer>

    </footer>
  </div> <!--! end of #container -->


  <!-- JavaScript at the bottom for fast page loading -->

  <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/libs/jquery-1.6.2.min.js"><\/script>')</script>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
  <script src="js/scripts.js"></script>
  
  <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 7 ]>
    <script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
    <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
  <![endif]-->
  
</body>
</html>