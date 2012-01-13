<?php include 'includes/timeline-maker.php'; ?>
<?php $timeline = build_timeline($timeline_action); ?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- Consider adding an manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>Project Timeline Creator v0.2</title>
  <meta name="description" content="The app allows you to input the project start date, each step of the project and the turnaround times.  It will then create a table with due dates for each piece of the project.">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" type="text/css" href="css/jquery-ui.css" />
  <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>

<body>

  <div id="container">
    <div id="main" role="main">
      <h1>Project Timeline Creator v0.2</h1>
      <div class="timeline-tables">
        <?php echo $timeline['tables']; ?>
      </div>
      <span class="msg"><?php echo $msg; ?></span>    
      <p>The final payment will be added <?php echo DAYS_TO_FINAL_PAYMENT; ?> calendar days after the last project step.  All turnaround times (TT) are calculated in business days.</p>
      <form action="index.php" method="post" id="timeline-form">
          <div id="timeline-steps">
              <p>Load Existing Timeline:
                  <?php echo load_timeline_names(); ?>
                  <input type="submit" name="load_timeline" value="Load Timeline" />
                  <input type="submit" name="delete_timeline" id="delete_timeline" value="Delete Timeline" />
              </p>
              <p>Project Name: <input type="text" name="project_name" id="project_name" value="<?php echo get_project_name($timeline_action); ?>" /> Save: <input type="checkbox" name="save_timeline" id="save_timeline" value="1" /></p>
              <p>Project Start Date: <input type="text" name="start_date" id="start_date" value="<?php echo get_start_date($timeline_action); ?>" /><input type="submit" name="create_timeline" id="create_timeline" value="Create Timeline" /></p>
              <p><input type="button" value="Add Project Step" id="add_step" /> <input type="button" value="Delete Last Project Step" id="delete_step" /></p>
              <?php echo build_form_fields($timeline_action, $timeline['due_dates'], $timeline['business_days']); ?>
          </div>
      </form>
    </div>
  </div> <!--! end of #container -->

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
  <script src="js/scripts.js"></script>  
</body>
</html>