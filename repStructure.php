<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Broker Structure"; //pagetitle
$SITEVARS[4] = "admin"; //user access
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
$init->loadLibrary("CommissionDates");
$init->loadComponent("default");

$init->loadModel("Broker");
$init->loadDAO("Broker");
$init->loadDAO("BrokerStructure");

?>

<?php
$init->loadInitHTML();
echo '<h3>Create Broker:</h3><span class="clickable" id="rep-structure-new-broker">Create Broker</span>';
echo '<h3>Rep Management:</h3>';
echo '<table><tr><td><select id="rep-structure-broker-selector" name="brokerNumber">';
HtmlComponents::fetchBrokerSelector();
echo '</select></td></tr></table>';
echo '<table><tr><td><div id="brokerStructure"></div></td></tr></table>';
//TODO - This shouldnt be here, move to some place that makes sense.
//echo "<h3>Preform a Rep Change</h3><table><tr><th></th></tr><tr><td><a href=\"javascript:;\"  onClick=\"window.open('views/editBrokerStructure.php?modify=repChange','no','scrollbars=yes,width=600,height=400')\" >Rep Change</a></td></tr></tr></table>";
$init->printFooter();
?>
