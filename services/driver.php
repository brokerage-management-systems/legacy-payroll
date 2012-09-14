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

require_once '../includes/config.php';
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("DatabaseConnection");
$init->loadLibrary("AuthenticationAndAccess");
$aaa = new AuthenticationAndAccess($SITEVARS[4], $SITEVARS[5]);
$aaa->checkSession();
$hasAccess = $aaa->checkPageAccess();
if (!$hasAccess) {
    $init->printMessage("accessRights");
    exit();
}
?>

<?php
include 'DBWrapper.php';
header('Content-Type','application/json');
try {
    $table = null;
    switch($_POST['table']) {
        case 'Brokers':
            $table = $_POST['table'];
            break;
        case 'miscExpenses':
            $table = $_POST['table'];
            break;
        case 'payoutStructure':
            $table = $_POST['table'];
            break;
        case 'repNums':
            $table = $_POST['table'];
            break;
        case 'SalesAssistant':
            $table = $_POST['table'];
            break;
        case 'salesAssistantData':
            $table = $_POST['table'];
            break;
        default:
            throw new Exception('Invalid table name: '.$_POST['table']);
            break;
    }
    $dbw = new DbWrapper(
        new PDO('mysql:host=localhost;dbname=clearingdata', 'tradeDataWrite', 'WJHPNsLefxseDGvq'),
        $table
    );
    $additional = array();
    foreach (array_keys($_POST) as $key) {
        if ($key != 'table' && $key != 'method' && $key != 'id') {
            $additional[$key] = $_POST[$key];
        }
    }
    switch ($_POST['method']) {
        case 'executeQuery':
            print json_encode($dbw->executeQuery($additional));
            break;
        case 'get':
            print json_encode($dbw->get($_POST['id']));
            break;
        case 'getAll':
            print json_encode($dbw->getAll($additional));
            break;
        case 'getByFields':
            print json_encode($dbw->getByFields($additional));
            break;
        case 'insert':
            print json_encode($dbw->insert($additional));
            break;
        case 'update':
            print json_encode($dbw->update($_POST['id'], $additional));
            break;
        case 'delete':
            print json_encode($dbw->delete($_POST['id']));
            break;
        default:
            throw new Exception('Unknown method');
            break;
    }
} catch (Exception $e) {
    print json_encode(array('error' => $e->getMessage()));
}
?>
