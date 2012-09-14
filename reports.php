<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Reports"; //pagetitle
$SITEVARS[4] = "user"; //user access
$SITEVARS[5] = "payrollData"; //permission access

require_once realpath(dirname(__FILE__) . '/includes/config.php');
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("DatabaseConnection");
$init->loadLibrary("AuthenticationAndAccess");
$aaa = new AuthenticationAndAccess($SITEVARS[4], $SITEVARS[5]);
$aaa->checkSession();
$hasAccess = $aaa->checkPageAccess();
if (!$hasAccess) {
    $init->loadInitHTML();
    $init->printMessage("accessRights");
}
$init->loadComponent("default");
$init->loadLibrary("CommissionDates");
$init->loadLibrary("Reports");
$init->loadModel("Broker");
$init->loadDAO("Broker");
$init->loadModel("SalesAssistant");
$init->loadDAO("SalesAssistant");

?>

<?php
if (isset($_GET['submit'])) {

    $init->loadInitHTML();
    $report = new Reports();
    switch ($_GET['reportType']) {
        case 'firmGrossReport':
            $report->runFirmGrossReport($_GET['reportDateStart'], $_GET['reportDateEnd']);
            break;
        case 'payrollReport':
            $report->runPayrollReport($_GET['reportMonth']);
            break;
        case 'grossSummaryReport':
            $reportResults = $report->runGrossSummaryReport($_GET['repNum'], $_GET['reportDateStart'], $_GET['reportDateEnd']);
            echo $reportResults[0];
            echo $reportResults[1];
            break;
        case 'salesAssistantPayrollReport':
            $report->runSalesAssistantPayrollReport($_GET['reportMonth']);
            break;
        default:
            break;
    }
    $init->printFooter();
    
} else if (isset($_GET['isCallback'])) {
    // Ajax Report Menu
    $reportType = $_GET['reportType'];
    switch ($reportType) {
        // case 'firmGrossReport':
        //     echo '<div align=center>';
        //     echo '<table>';
        //     echo '<tr><td>Report Start:</td><td><select name=reportDateStart>';
        //     HtmlComponents::dateDropDown();
        //     echo '</select></td></tr>';
        //     echo '<tr><td>(THROUGH)</td></tr>';
        //     echo '<tr><td>Report End:</td><td><select name="reportDateEnd">';
        //     HtmlComponents::dateDropDown();
        //     echo '</select></td></tr>';
        //     echo '</table><br />';
        //     break;
        case 'grossSummaryReport':
            echo '<div align="center">';
            echo '<table>';
            echo '<tr><td colspan="2" class="centeralign italic notbold">(For Firm Report choose "*RegRep")</td></tr>';
            echo '<tr><td>Rep:</td><td><select name="repNum">';
            HtmlComponents::repNameNumDropDown();
            echo '</select></td></tr>';
            echo '<tr><td>Report Start:</td><td><select name="reportDateStart">';
            HtmlComponents::dateDropDown();
            echo '</select></td></tr>';
            echo '<tr><td colspan="2" class="centeralign italic notbold">(THROUGH)</td></tr>';
            echo '<tr><td>Report End:</td><td><select name="reportDateEnd">';
            HtmlComponents::dateDropDown();
            echo "</select></td></tr>";
            echo "</table><br />";
            break;
        case 'payrollReport':
            echo "<div align=center>";
            echo "<table><tr>";
            echo "<td>Commission Month:</td><td><select name=reportMonth>";
            HtmlComponents::dateDropDown();
            echo '</select></td>';
            echo '</tr></table><br />';
            break;
        case 'salesAssistantPayrollReport':
            echo '<div align="center">';
            echo '<table><tr>';
            echo '<td>Commission Month:</td><td><select name="reportMonth">';
            HtmlComponents::dateDropDown();
            echo '</select></td>';
            echo '</tr></table><br />';
            break;
        default:
            break;
    }
} else {
    $init->loadInitHTML();
    echo '<form method="get" action="">';
    echo '<div align="center"><select id="report-type-selection" name="reportType">';
    echo '<option value="null">*Choose Report Type</option>';
    //echo '<option value="firmGrossReport">Firm Gross Report</option>';
    echo '<option value="grossSummaryReport">Gross Summary Report</option>';
    echo '<option value="payrollReport">Payroll Report</option>';
    echo '<option value="salesAssistantPayrollReport">Sales Assistant Payroll Report</option>';
    echo '</select></div>';
    echo '<div id="reportTypeOptions"></div>';
    echo '<div align="center"><input type="submit" name="submit" /></div></form>';
    $init->printFooter();
}
?>
