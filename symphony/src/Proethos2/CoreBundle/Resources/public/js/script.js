
// This file is part of the ProEthos Software. 
// 
// Copyright 2013, PAHO. All rights reserved. You can redistribute it and/or modify
// ProEthos under the terms of the ProEthos License as published by PAHO, which
// restricts commercial use of the Software. 
// 
// ProEthos is distributed in the hope that it will be useful, but WITHOUT ANY
// WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
// PARTICULAR PURPOSE. See the ProEthos License for more details. 
// 
// You should have received a copy of the ProEthos License along with the ProEthos
// Software. If not, see
// https://github.com/bireme/proethos2/blob/master/LICENSE.txt


$(function(){
        
    // masks
    $('.mask-money').mask('00000000000000000000000000000000000.00', {reverse: true});

    // initters
    $('[data-toggle="tooltip"]').tooltip()

    /* START: protocol first step */
    $( "#radio_clinical_trial_yes" ).on( "click", function() {
        $("input[name=is_consultation]").attr('disabled', true);
    });

    $( "#radio_clinical_trial_no" ).on( "click", function() {
        $("input[name=is_consultation]").attr('disabled', false);
    });

    $( "#radio_consultation_yes" ).on( "click", function() {
        $("input[name=is_clinical_trial]").attr('disabled', true);
    });

    $( "#radio_consultation_no" ).on( "click", function() {
        $("input[name=is_clinical_trial]").attr('disabled', false);
    });

    $( "#first_step, #first_step_created_protocol" ).submit( function() {
        $("input[name=is_clinical_trial]").attr('disabled', false);
        $("input[name=is_consultation]").attr('disabled', false);

    })

    if( $("#first_step_created_protocol").length ) {
        if( $('#radio_clinical_trial_yes').is(':checked') ) {
            $("input[name=is_consultation]").attr('disabled', true);
        }

        if( $('#radio_consultation_yes').is(':checked') ) {
            $("input[name=is_clinical_trial]").attr('disabled', true);
        }
    }
    /* END: protocol first step */

    /* START: protocol third step */
    $( "#radio_multiple_clinical_study_yes" ).on( "click", function() {
        $("#default-clinical-study").hide();
        $("#multiple-clinical-study").show();
        $("#default-clinical-study input").prop('required',false);
        $("#default-clinical-study select[name!=recruitment-status]").prop('required',false);
    });

    $( "#radio_multiple_clinical_study_no" ).on( "click", function() {
        $("#default-clinical-study").show();
        $("#multiple-clinical-study").hide();
        $("#default-clinical-study input").prop('required',true);
        $("#default-clinical-study select[name!=recruitment-status]").prop('required',true);
    });
    /* END: protocol third step */

    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        // increaseArea: '20%'
    });

    sortTable(); // sort table rows

});

function sortTable() {
  var table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementById("table-data");
  switching = true;
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 1; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("TD")[0];
      y = rows[i + 1].getElementsByTagName("TD")[0];
      // Check if the two rows should switch place:
      if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
        // If so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}