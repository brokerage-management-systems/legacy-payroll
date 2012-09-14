/*
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 *  Payroll Management System
 *  Version 2.2
 */

function displayRepStructureData(rn) { 
    var params = {header:'no',repNum:rn,sid:Math.random()};
    $.ajax({
        async: true,
        url: '/views/ajax/viewBrokerStructure.php',
        data: params,
        success: function(data) {
            $('#brokerStructure').html(data);
        }
    });
}
function displayExistingExpenseData(e,m) {  
    var params = {e:e,m:m,sid:Math.random()};
    $.ajax({
        async: true,
        url: 'views/ajax/editExpenses.php',
        data: params,
        success: function(data) {
            $('#existingExpenseData').html(data);
        }
    });
}
function displayReportMenu(rt) {  
    var params = {reportType:rt,isCallback:true,sid:Math.random()};
    $.ajax({
        async: true,
        url: 'reports.php',
        data: params,
        success: function(data) {
            $('#reportTypeOptions').html(data);
        }
    });
}

/*
 * NEW - AJAX FUNCTIONS
 */
$(document).ready(function() {
    $('#rep-structure-broker-selector').change(function() {
        displayRepStructureData($(this).val()); 
    });
    $('#expense-submission-expense').change(function() {
        displayExistingExpenseData($(this).val(),$('#expense-submission-commission-month').val());
    });
    $('#expense-submission-commission-month').change(function() {
        displayExistingExpenseData($('#expense-submission-expense').val(),$(this).val());
    });
    $('#report-type-selection').change(function() {
        displayReportMenu($('#report-type-selection').val(),$(this).val());
    });
});