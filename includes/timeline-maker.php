<?php
//TODO Create a constant for one day in seconds.

function build_timeline() {
    $timeline = $_POST;
    $start_date = $_POST['start_date'];
    $start_timestamp = strtotime($start_date);
    
    //Place every weekday for the next 365 days into an array.
    //Start the array with the starting day;
    $business_days = array();
    $business_days[] = $start_timestamp;
    $timestamp = $start_timestamp;
    for ($i = 1; $i < 365; $i++) {
        $timestamp += 86400;
        if (date('D', $timestamp) != 'Sat' && date('D', $timestamp) != 'Sun') {
            $business_days[] = $timestamp;
        }
    }
    
    $due_dates = array();
    $due_dates[] = array('Start of Work', date('l, F d, Y', $start_timestamp));
    $last_due_date = 0;
    for($i = 1; $i < 100; $i++){
        if(isset($_POST['step_' . $i])){
            $last_due_date += $_POST['tt_' . $i];
            $step_due_date = date('l, F d, Y', $business_days[$last_due_date]);
            $due_dates[] = array($_POST['step_' . $i], $step_due_date);
        }
        else{
            break;
        }
    }
    
    //Add the final payment 15 calendar days after the final date.
    $final_payment_date = date('l, F d, Y', $business_days[$last_due_date] + (86400 * 15));
    $due_dates[] = array('Final Payment Due', $final_payment_date);
    
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
    echo $project_table;
    
    return $due_dates;
}



function display_step($step_number){
    global $due_dates;
    if(isset($due_dates)){
        return 'value="' . $due_dates[$step_number][0] . '"';
    }
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
                $form_fields .= '<p><input type="text" name="step_' . $i . '" id="step_' . $i . '" placeholder="Project Step"' . display_step($i) . ' />';
                $form_fields .= ' TT: <select name="tt_' . $i . '" id="tt_' . $i . '">';
                $form_fields .= display_tt_options($i);
                $form_fields .= '</select> <a href="" class="remove">Remove</a></p>';
            }
            else{
                break;
            }
        }
        
        return $form_fields;
    }
    else{
        for($i = 1; $i < 4; $i++){
            $form_fields .= '<p><input type="text" name="step_' . $i . '" id="step_' . $i . '" placeholder="Project Step" />';
            $form_fields .= ' TT: <select name="tt_' . $i . '" id="tt_' . $i . '">';
            $form_fields .= display_tt_options();
            $form_fields .= '</select> <a href="" class="remove">Remove</a></p>';
        }
        
        return $form_fields;
    }
    
}