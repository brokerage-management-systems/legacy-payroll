<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'login'; //pageid $pageID = 'login';
$SITEVARS[3] = "Payroll Management System - Login"; //pagetitle
$SITEVARS[4] = "user"; // user access
$SITEVARS[5] = "payrollData"; //permission access

require_once realpath(dirname(__FILE__) . '/includes/config.php');
$init = new InitializeSite($SITEVARS);
if (isset($_POST['username']) && isset($_POST['password'])) {
	$init->loadLibrary("DatabaseConnection");
	$init->loadLibrary("AuthenticationAndAccess");
	$aaa = new AuthenticationAndAccess($SITEVARS[4], $SITEVARS[5]);
	$aaa->doLogin($_POST['username'], $_POST['password'], $_POST['site']);
}
$init->loadInitHTML();
?>

<script language="javascript" type="text/javascript">
    function setTextBoxFocus(){
        document.login.username.focus();
    }
    window.onload=setTextBoxFocus;
</script>

<div id="login-form">
  <form action="login.php" method="POST" name="login">
	<fieldset><legend>Login:</legend>
	<dl>
		<dt><label for="username">Username</label></dt>
		<dd><input type="text" name="username" id="username"></dd>
		<dt><label for="password">Password</label></dt>
		<dd><input type="password" name="password" id="password"></dd>
	</dl>
	<input type="submit" value="Login"></fieldset>
  </form>
</div>

<?php
$init->printFooter(); // Include the html footer.
?>
