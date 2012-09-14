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
$SITEVARS[4] = "user"; //user access
$SITEVARS[5] = "payrollData"; //permission access

require_once '../../includes/config.php';
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("DatabaseConnection");
$init->loadLibrary("AuthenticationAndAccess");
$aaa = new AuthenticationAndAccess($SITEVARS[4], $SITEVARS[5]);
$aaa->checkSession();
$hasAccess = $aaa->checkPageAccess();
if (!$hasAccess) {
    $init->loadInitHTML();
    $init->printMessage("accessRights");
    $init->printFooter();
    exit();
}
/* --- End Boilerplate --- */
?>

<?php
$database = new DatabaseConnection();
$database->setConnectionSettings("tradeDataWrite");
if ($GLOBALS['DEBUG']) {
    $database->changeToDevelopmentDatabase();
}
$database->openConnection();

$repNum = null;
if (!empty($_GET['repNum'])) {
    $repNum = $_GET['repNum'];
}

echo "<table><tr><td><h4>Broker Structure</h4></td></tr>";

/* --- Begin Broker Payout --- */
echo "<tr><th>Payout Percent</th></tr>";
$selectPayQuery = "SELECT * FROM payoutStructure WHERE repNum='$repNum' ORDER BY minAmt ASC";
//echo $selectQuery;
$selectPayResult = mysql_query($selectPayQuery);
if (mysql_num_rows($selectPayResult) > 0) {
    while ($pay = mysql_fetch_array($selectPayResult, MYSQL_NUM)) {
        echo "<tr><td> If Commission is > ".$pay[2]." AND < ".$pay[3]." Payout is: ".($pay[4] * 100)."%</td></tr>";
    }
    echo '<tr><td>Existing Payout - <span class="clickable" id="rep-structure-edit-payout">Edit</span></td></tr>';
}
else {
    echo '<tr><td>Not Set - <span class="clickable" id="rep-structure-new-payout">Edit</span></td></tr>';
}
/* --- End Broker Payout --- */

/* --- Begin Broker Joint Reps --- */
echo "<tr><th>JointReps</th></tr>";
$selectJointQuery = "SELECT * FROM repNums WHERE mainRep='$repNum' AND type='JointRep' ORDER BY altRep ASC";
//echo $selectJointQuery;
$selectJointResult = mysql_query($selectJointQuery);
if (mysql_num_rows($selectJointResult) > 0) {
    while ($jRep = mysql_fetch_array($selectJointResult, MYSQL_NUM)) {
        echo "<tr><td> ".$jRep[3].", ".$jRep[2]." between: ".$jRep[5]." - Split ".$jRep[4]."</td></tr>";
    }
    echo '<tr><td>Existing JointReps - <span class="clickable" id="rep-structure-edit-joint">Edit</span></td></tr>';
}
else {
    echo '<tr><td>Not Set - <span class="clickable" id="rep-structure-new-joint">Edit</span></td></tr>';
}
/* --- End Broker Joint Reps --- */

/* --- Begin Broker Overrides --- */
echo "<tr><th>Rep Overrides</th></tr>";
$selectOverQuery = "SELECT * FROM repNums WHERE mainRep='$repNum' AND type='Override' ORDER BY altRep ASC";
//echo $selectOverQuery;
$selectOverResult = mysql_query($selectOverQuery);
if (mysql_num_rows($selectOverResult) > 0) {
    while ($oRep = mysql_fetch_array($selectOverResult, MYSQL_NUM)) {
        echo "<tr><td>Rep: ".$oRep[1]." receives a ".($oRep[4] * 100)."% Override on Rep: ".$oRep[2].'</td></tr>';
    }
    echo '<tr><td>Existing Overrides - <span class="clickable" id="rep-structure-edit-override">Edit</span></td></tr>';
} else {
    echo '<tr><td>Not Set - <span class="clickable" id="rep-structure-new-override">Edit</span></td></tr>';
}
/* --- End Broker Overrides --- */

/* --- Begin Sales Assistant Bonus --- */
echo "<tr><th>Sales Assistant Bonus</th></tr>";
$selectSAQuery = "SELECT saName, percent FROM salesAssistantData WHERE repNum='$repNum'";
//echo $selectSAQuery;
$selectSAResult = mysql_query($selectSAQuery);
if (mysql_num_rows($selectSAResult) > 0) {
    while ($sa = mysql_fetch_array($selectSAResult, MYSQL_NUM)) {
        echo '<tr><td>'.$sa[0].'\'s bonus is: '.($sa[1] * 100).'% - <span class="clickable" id="rep-structure-update-sa-bonus">Edit</span></td><td></td></tr>';
    }
}
else {
    echo '<tr><td>Not Set - <span class="clickable" id="rep-structure-insert-sa-bonus">Edit</span></td></tr>';
}
/* --- End Sales Assistant Bonus --- */

$database->closeConnection();

/* --- Begin Broker Status --- */
echo '<tr><th>Broker Status</th></tr></tr><tr><td><span class="clickable" id="rep-structure-edit-broker">Modify</span></td></tr>';
/* --- End Broker Status --- */

echo '</table>';

?>
