<?php
define('ONE_DAY_IN_SECONDS', 86400);
define('ONE_YEAR_IN_DAYS', 365);
define('DAYS_TO_FINAL_PAYMENT', 15);
define('MAX_TURNAROUND_DAYS', 180);
define('DEFAULT_STEP_NUMBER', 4);

//TODO When loading an existing timeline the load function is called twice.  We need to remove one.
//TODO Create a sample connection file that can be used to create the real one.  Similar to wp-config-sample.php for WordPress.

//Set a blank message to add notes to as we go.
$msg = '';

//Determine what action was just taken by the user.
$timeline_action = find_timeline_action();
function find_timeline_action(){
    if(isset($_POST['load_timeline'])){ //Load existing timeline.
        $timeline_action = 'load';
    }
    elseif(isset($_POST['delete_timeline'])){ //Delete existing timeline.
        $timeline_action = 'delete';
    }
    elseif(isset($_POST['create_timeline'])) { //Create new timeline.
        $timeline_action = 'create';
    }
    else{
        $timeline_action = 'none';
    }
    
    
    return $timeline_action;
}


function build_timeline($timeline_action) {
    $timeline = array();
    $timeline['project_name'] = get_project_name($timeline_action);
    $timeline['start_date'] = get_start_date($timeline_action);
    $timeline['start_date_timestamp'] = strtotime($timeline['start_date']);
    $timeline['business_days'] = FALSE;
    $timeline['due_dates'] = FALSE;
    
    switch($timeline_action){
        case "create":
            //If they checked save, then either add timeline to database or overwrite existing timeline.
            $timeline['business_days'] = build_business_days($timeline['start_date_timestamp']);
            $timeline['due_dates'] = build_due_dates($timeline['start_date_timestamp'], $timeline['business_days']);
            if(isset($_POST['save_timeline'])) store_timeline($timeline['project_name'], $timeline['due_dates']);
            break;
        case "load":
            $timeline['due_dates'] = load_timeline($timeline['project_name']);
            $timeline['business_days'] = build_business_days($timeline['start_date_timestamp']);
            break;
        case "delete":
            delete_timeline($timeline['project_name']);
            break;
        case "none":
            break;
    }
    
    $timeline['tables'] = build_timeline_table($timeline['due_dates']);
    
    
    return $timeline;
}


function get_project_name($timeline_action){
    switch($timeline_action){
        case "create":
            $project_name = htmlentities($_POST['project_name']);
            break;
        case "load":
        case "delete":
            $project_name = htmlentities($_POST['load_project_name']);
            break;
        case "none":
            $project_name = "";
            break;
    }
    
    
    return $project_name;
}


function delete_timeline($project_name){
    include 'connection.php';
    $query = 'DELETE FROM timeline WHERE project_name = "' . $project_name . '"';
    $result = $db->query($query);
    global $msg;
    if ($result) {
        $msg .= 'The ' . $project_name . ' timeline has been deleted.';
    }
    else{
        $msg .= 'There was an error deleting the ' . $project_name . ' timeline.';
    }
    
    $db->close();
}


function get_start_date($timeline_action){
    switch($timeline_action){
        case "create":
            $start_date = htmlentities($_POST['start_date']);
            break;
        case "load":
            $due_dates = load_timeline(get_project_name($timeline_action));
      
            $start_date = $due_dates[0][1];
            $start_date = strtotime($start_date);
            $start_date = date('m/d/Y', $start_date);
            break;
        default:
            $start_date = "";
            break;
    }

    
    return $start_date;
}


function display_step($step_number){
    global $due_dates;
    if(isset($due_dates)){
        return 'value="' . $due_dates[$step_number][0] . '"';
    }
}


function build_business_days($start_timestamp){
    //Place every weekday for the next 365 days into an array.
    //Start the array with the starting day;
    if ($start_timestamp !=  FALSE){
        $business_days = array();
        $business_days[] = $start_timestamp;
        $timestamp = $start_timestamp;
        for ($i = 1; $i < ONE_YEAR_IN_DAYS; $i++) {
            $timestamp += ONE_DAY_IN_SECONDS;
            if (date('D', $timestamp) != 'Sat' && date('D', $timestamp) != 'Sun') {
                $business_days[] = $timestamp;
            }
        }
    }
    else {
        $business_days = FALSE;
    }
    
    
    return $business_days;
}


function build_due_dates($start_timestamp, $business_days){
  if ($start_timestamp != FALSE){
      $due_dates = array();
      $due_dates[] = array('Start of Work', date('l, F d, Y', $start_timestamp), 0);
      $last_due_date = 0;
      for($i = 1; $i < 100; $i++){
          if(isset($_POST['step_' . $i])){
              $last_due_date += $_POST['tt_' . $i];
              $step_due_date = date('l, F d, Y', $business_days[$last_due_date]);
              $due_dates[] = array(htmlentities($_POST['step_' . $i]), $step_due_date, $last_due_date);
          }
          else{
              break;
          }
      }
      
      $due_dates = add_final_payment($due_dates, $business_days);
  }
  else{
      $due_dates = FALSE;
  }
 
  
  return $due_dates;
}


function add_final_payment($due_dates, $business_days){
    $last_due_date = end($due_dates);
    $last_due_date = $last_due_date[2];
    $final_payment_date = date('l, F d, Y', $business_days[$last_due_date] + (ONE_DAY_IN_SECONDS * DAYS_TO_FINAL_PAYMENT));
    $due_dates[] = array('$XX.XX Final Payment Due', $final_payment_date);  
    
    
    return $due_dates;
}


function build_timeline_table($due_dates){
  if($due_dates != FALSE){
      //Build the project table.
      $tables['step'] = '<table class="timeline"><tr><th>Step #</th></tr>';
      $tables['task'] = '<table class="timeline"><tr><th>Task</th></tr>';
      $tables['due_date'] = '<table class="timeline"><tr><th>Due Date</th></tr>';

      $step_number = 1;
      foreach ($due_dates as $dates) {
          $tables['step'] .= '<tr><td>' . $step_number . '</td></tr>';
          $tables['task'] .= '<tr><td>' . $dates[0] . '</td></tr>';
          $tables['due_date'] .= '<tr><td>' . $dates[1] . '</td></tr>';

          $step_number++;
      }
      $tables['step'] .= '</table>';
      $tables['task'] .= '</table>';
      $tables['due_date'] .= '</table>';
      
      $tables['all'] = $tables['due_date'] . $tables['task'] . $tables['step'];
      
      global $msg;
      if($msg == ''){
          $msg .= 'Your timeline was built successfully.';
      }
  }
  else {
      $tables['all'] = '';
  }

  
  return $tables['all'];
}


function display_tt_options($tt_number = null) {
    $options = '';
    for ($i = 0; $i <= MAX_TURNAROUND_DAYS; $i++) {
        $options .= '<option value="' . $i . '"';
        if ($tt_number == $i){
            $options .= ' selected="selected"';
        }
        $options .= '>' . $i . '</option>';
    }
    
    
    return $options;
}


function build_form_fields($timeline_action, $due_dates){
    $form_fields = '';
    switch($timeline_action){
        case 'create':
        case 'load':
            for($i = 1; $i < sizeof($due_dates) - 1; $i++){
                $form_fields .= '<p class="step-tt"><input type="text" name="step_' . $i . '" id="step_' . $i . '" placeholder="Project Step" value="' . $due_dates[$i][0] . '" />';
                $form_fields .= ' TT: <select name="tt_' . $i . '" id="tt_' . $i . '">';
                $form_fields .= display_tt_options($due_dates[$i][2] - $due_dates[$i - 1][2]);
                $form_fields .= '</select></p>';
             }                
             break;
        case 'none':
        case 'delete':
            for($i = 1; $i <= DEFAULT_STEP_NUMBER; $i++){
                $form_fields .= '<p class="step-tt"><input type="text" name="step_' . $i . '" id="step_' . $i . '" placeholder="Project Step" />';
                $form_fields .= ' TT: <select name="tt_' . $i . '" id="tt_' . $i . '">';
                $form_fields .= display_tt_options();
                $form_fields .= '</select></p>';
            }
            break;
    }
    
    
    return $form_fields;
}


function store_timeline($project_name, $due_dates){
    include 'connection.php';
    $query = 'SELECT * from timeline WHERE project_name = "' . $project_name . '"';
    $result = $db->query($query);
    $num_results = $result->num_rows;
    
    //Serialize the due dates.
    $due_dates = serialize_due_dates($due_dates);
    
    global $msg;
    if($project_name == ''){ //No project name.
        echo 'You didn\'t enter a project name.';
    }
    elseif($num_results){ //Timeline already exists.  Rewrite over it.
        $query = 'UPDATE timeline SET due_dates = "' . $due_dates . '" WHERE project_name = "' . $project_name . '"';
        $result = $db->query($query);
        if ($result) {
            $msg .= 'This project already exists.  We have overwritten the existing timeline.';
        }
        else {
            $msg .= "An error has occurred. The project was not added.";
            exit();
        }
    }
    else{ //New project.  Add timeline to database.    
        $query = 'insert into timeline values ("", "' . $project_name . '", "' . $due_dates . '")';
        $result = $db->query($query);
        if ($result) {
            $msg .= 'The ' . $project_name . ' timeline has been added to the database.';
        }
        else {
            $msg .= "An error has occurred. The project was not added.";
        }
    }
    
    $db->close();
}


function load_timeline_names(){
    include 'connection.php';
    $query = 'SELECT project_name from timeline';
    $result = $db->query($query);
    $num_results = $result->num_rows;
    
    //Put all the names into an array, then sort the array alphabetically.
    $timeline_names = array();
    for ($i=0; $i <$num_results; $i++) {
        $timeline_name = $result->fetch_assoc();
        array_push($timeline_names, $timeline_name['project_name']);
    }
    sort($timeline_names);
    
    //Create the select list to put the names in.
    $select_input = '<select name="load_project_name" id="load_project_name">';
    for ($i=0; $i < sizeof($timeline_names); $i++) {
        $select_input .= '<option name="' . $timeline_names[$i] . '">' . $timeline_names[$i] . '</option>';
    }
    $select_input .= '</select>';
    
    
    $db->close();
    return $select_input;
}


function load_timeline($project_name){
    include 'connection.php';
    $query = 'SELECT * from timeline WHERE project_name = "' . $project_name . '"';
    $result = $db->query($query);
    global $msg;
    if($result){
        if($msg == '') $msg .= 'The ' . $project_name . ' timeline was loaded successfully.';
    }
    else {
        $msg .= 'There was an error loading your timeline.';
        exit();
    }
    $timeline = $result->fetch_assoc();
    
    $due_dates = $timeline['due_dates'];
    $due_dates = unserialize_due_dates($due_dates);

    
    $db->close();
    return $due_dates;
}


function serialize_due_dates($due_dates){
    $due_dates = serialize($due_dates);
    $due_dates = str_replace('"', '`', $due_dates);
    
    
    return $due_dates;
}
    
function unserialize_due_dates($due_dates){
    $due_dates = str_replace('`', '"', $due_dates);
    $due_dates = unserialize($due_dates);
    
    
    return $due_dates;
}