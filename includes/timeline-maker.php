<?php
define('ONE_DAY_IN_SECONDS', 86400);
define('ONE_YEAR_IN_DAYS', 365);
define('DAYS_TO_FINAL_PAYMENT', 15);

function build_timeline() {
    $time = explode(' ', microtime());
    $time = $time[1] + $time[0]; // return array
    $begintime = $time; //define begin time
    
    $start_date = htmlentities($_POST['start_date']);
    $start_timestamp = strtotime($start_date);
    
    $business_days = build_business_days($start_timestamp);
    
    $due_dates = build_due_dates($start_timestamp, $business_days);       
    
    echo build_timeline_table($due_dates);
    
    $time = explode(" ", microtime());
    $time = $time[1] + $time[0];
    $endtime = $time; //define end time
    $totaltime = ($endtime - $begintime); //decrease to get total time
    echo $totaltime.' seconds'; //echo it to appear in browser
    
    return $due_dates;
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
  $project_table = '<table class="timeline">';
  $project_table .= '<tr><th>Step</th><th>Due Date</th></tr>';
  foreach ($due_dates as $dates) {
      $project_table .= '<tr>';
      $project_table .= '<td>' . $dates[0] . '</td>';
      $project_table .= '<td>' . $dates[1] . '</td>';
      $project_table .= '</tr>';
  }
  $project_table .= '</table>';

  return $project_table;
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