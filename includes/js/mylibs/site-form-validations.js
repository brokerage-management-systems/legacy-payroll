
$(document).ready(function() {
    
    /*
     * Expense Submission
     */
    $('#expense-submission').submit(function() {
        var $errorDialog = $('<div></div>').dialog({
    		autoOpen: false,
    		buttons: {
    			'Ok': function() {
    				$(this).dialog('close');
    			}
    		},
    		height: 'auto',
    		modal: true,
    		title: 'Form Submission Error',
    		width: 'auto'
    	});
        var errorMessage = '';
        var formValid = true;
        
        if ($('option:selected','#expense-submission-expense').index() == 0 || $('option:selected','#expense-submission-expense').index() == 4) {
            errorMessage += 'Please select an Expense!<br />';
            formValid = false;
        }
        if ($('option:selected','#expense-submission-commission-month').index() == 0) {
            errorMessage += 'Please select the Commission Month!<br />';
            formValid = false;
        }
        if (!formValid) {
            $errorDialog.html(errorMessage);
            $errorDialog.dialog('open');
        }
        return formValid;
    });
    
});

