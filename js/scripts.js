$(function() {
    $("#start_date").datepicker();
    
    $('#add_step').click(function(){
        var new_step_id = parseInt($('#timeline-steps input:last').attr('id').substring(5)) + 1;
        var new_tt_id = parseInt($('#timeline-steps select:last').attr('id').substring(3)) + 1;
        var input_html = '<p class="step-tt"><input type="text" name="step_' + new_step_id + '" id="step_' + new_step_id + '" class="step" placeholder="Project Step">';
        input_html += ' TT: <select name="tt_' + new_tt_id + '" id="tt_' + new_tt_id + '" class="tt"></p>';
        var max_days = 180;
        for(var i = 0; i <= max_days; i++){
            input_html += '<option value="' + i + '">' + i + '</option>';
        }
        input_html += '</select>';
        $('#timeline-steps').append(input_html);
        
        if($('#timeline-steps p.step-tt').length > 1 && $('#delete_step').is(':disabled') == true){
          $('#delete_step').removeAttr('disabled');
        }
    });
    
    $('#delete_step').click(function(){
      $('#timeline-steps p.step-tt:last').remove();
      if($('#timeline-steps p.step-tt').length == 1){
        $('#delete_step').attr('disabled', true);
      }
    });
});