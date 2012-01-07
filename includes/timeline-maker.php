<?php
define('ONE_DAY_IN_SECONDS', 86400);
define('ONE_YEAR_IN_DAYS', 365);
define('DAYS_TO_FINAL_PAYMENT', 15);

//TODO Load timeline correctly into form fields.
//TODO Finish the delete timeline function.

function build_timeline() { 
    $project_name = get_project_name();
    
    if(isset($_POST['load_timeline'])){ //Load existing timeline.
        $due_dates = load_timeline($project_name);
    }
    elseif(isset($_POST['delete_timeline'])){ //Delete existing timeline.
        
    }
    else { //Create new timeline.
        $start_date = htmlentities($_POST['start_date']);
        $start_timestamp = strtotime($start_date);
        $business_days = build_business_days($start_timestamp);
        $due_dates = build_due_dates($start_timestamp, $business_days); 
    }
   
    $tables = build_timeline_table($due_dates);
    
    //If they checked save, then either add timeline to database or overwrite existing timeline.
    if(isset($_POST['save_timeline'])) store_timeline($project_name, $due_dates);
    
    echo $tables['due_date'];
    echo $tables['task'];
    echo $tables['step'];
    
    
    return $due_dates;
}

function get_project_name(){
    if(isset($_POST['load_timeline'])){
      $project_name = htmlentities($_POST['load_project_name']);
    }
    elseif(isset($_POST['create_timeline'])){
      $project_name = htmlentities($_POST['project_name']);  
    }
    else{
        $project_name = "";
    }
    
    
    return $project_name;
}


function get_start_date(){
    if(isset($_POST['load_timeline'])){
      $due_dates = load_timeline(get_project_name());
      
      $start_date = $due_dates[0][1];
      $start_date = strtotime($start_date);
      $start_date = date('m/d/Y', $start_date);
    }
    elseif(isset($_POST['create_timeline'])){
      $start_date = htmlentities($_POST['start_date']);  
    }
    else{
        $start_date = "";
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
    $business_days = array();
    $business_days[] = $start_timestamp;
    $timestamp = $start_timestamp;
    for ($i = 1; $i < ONE_YEAR_IN_DAYS; $i++) {
        $timestamp += ONE_DAY_IN_SECONDS;
        if (date('D', $timestamp) != 'Sat' && date('D', $timestamp) != 'Sun') {
            $business_days[] = $timestamp;
        }
    }
    
    
    return $business_days;
}


function build_due_dates($start_timestamp, $business_days){
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

  
  return $tables;
}


function display_tt_options($tt_number = null) {
    $options = '';
    $max_days = 180;
    for ($i = 0; $i <= $max_days; $i++) {
        $options .= '<option value="' . $i . '"';
        if (isset($_POST['tt_' . $tt_number]) && $_POST['tt_' . $tt_number] == $i)
            $options .= ' selected="selected"';
        $options .= '>' . $i . '</option>';
    }
    
    
    return $options;
}


function build_form_fields(){
    $form_fields = '';
    if(isset($_POST['step_1'])){
        for($i = 1; $i < 100; $i++){
            if(isset($_POST['step_' . $i])){
                $form_fields .= '<p class="step-tt"><input type="text" name="step_' . $i . '" id="step_' . $i . '" placeholder="Project Step"' . display_step($i) . ' />';
                $form_fields .= ' TT: <select name="tt_' . $i . '" id="tt_' . $i . '">';
                $form_fields .= display_tt_options($i);
                $form_fields .= '</select></p>';
            }
            else{
                break;
            }
        }
        
        
        return $form_fields;
    }
    else{
        for($i = 1; $i < 4; $i++){
            $form_fields .= '<p class="step-tt"><input type="text" name="step_' . $i . '" id="step_' . $i . '" placeholder="Project Step" />';
            $form_fields .= ' TT: <select name="tt_' . $i . '" id="tt_' . $i . '">';
            $form_fields .= display_tt_options();
            $form_fields .= '</select></p>';
        }
        
        
        return $form_fields;
    }
}


function store_timeline($project_name, $due_dates){
    include 'connection.php';
    $query = 'SELECT * from timeline WHERE project_name = "' . $project_name . '"';
    $result = $db->query($query);
    $num_results = $result->num_rows;
    
    //Serialize the due dates.
    $due_dates = serialize_due_dates($due_dates);
    
    if($project_name == ''){ //No project name.
        echo 'You didn\'t enter a project name.';
    }
    elseif($num_results){ //Timeline already exists.  Rewrite over it.
        echo 'This project already exists.  We are going to save over the existing timeline.';
        $query = 'UPDATE timeline SET due_dates = "' . $due_dates . '" WHERE project_name = "' . $project_name . '"';
        $result = $db->query($query);
        if ($result) {
            echo $db->affected_rows." projects inserted into database.";
        }
        else {
            echo "An error has occurred. The project was not added.";
        }
    }
    else{ //New project.  Add timeline to database.    
        $query = 'insert into timeline values ("", "' . $project_name . '", "' . $due_dates . '")';
        $result = $db->query($query);
        if ($result) {
            echo '<span class="msg">You\'re timeline has been added to the database.</span>';
        }
        else {
            echo "An error has occurred. The project was not added.";
        }
    }
    
    $db->close();
}


function load_timeline_names(){
    include 'connection.php';
    $query = 'SELECT project_name from timeline';
    $result = $db->query($query);
    $num_results = $result->num_rows;
    
    $select_input = '<select name="load_project_name">';
    for ($i=0; $i <$num_results; $i++) {
        $timeline_name = $result->fetch_assoc();
        $select_input .= '<option name="' . $timeline_name['project_name'] . '">' . $timeline_name['project_name'] . '</option>';
    }
    $select_input .= '</select>';
    
    
    $db->close();
    return $select_input;
}


function load_timeline($project_name){
    include 'connection.php';
    $query = 'SELECT * from timeline WHERE project_name = "' . $project_name . '"';
    $result = $db->query($query);
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