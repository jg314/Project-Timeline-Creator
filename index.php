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
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>Project Timeline Creator</title>
  <meta name="description" content="The app allows you to input the project start date, each step of the project and the turnaround times.  It will then create a table with due dates for each piece of the project.">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" type="text/css" href="css/jquery-ui.css" />
  <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>

<body>

  <div id="container">
    <div id="main" role="main">
      <h1>Project Timeline Creator</h1>
      <?php if (isset($_POST['start_date'])) $due_dates = build_timeline(); ?>
            
      <p>The final payment will be added <?php echo DAYS_TO_FINAL_PAYMENT; ?> calendar days after the last project step.  All turnaround times (TT) are calculated in business days.</p>
      <form action="index.php" method="post" id="timeline-form">
          <div id="timeline-steps">
              <p>Project Start Date: <input type="text" name="start_date" id="start_date" <?php if(isset($_POST['start_date'])) echo 'value="' . $_POST['start_date'] . '"'; ?>/></p>
              <?php echo build_form_fields(); ?>
          </div>
              <p><input type="button" value="Add Project Step" id="add_step" /> <input type="button" value="Delete Last Project Step" id="delete_step" /></p>
              <p><input type="submit" value="Create Timeline" /></p>
      </form>
    </div>
  </div> <!--! end of #container -->

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
  <script src="js/scripts.js"></script>  
</body>
</html>