<?php
session_start();
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/credential.inc.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/database.inc.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/config.inc.php";
?>

<?php
if(isset($_SESSION['verified_login'])) {
    if($_SESSION['verified_login'] === true) {
        header("Location: admin.php");
        exit();
    }
}

if(
isset($_POST['login']) and
isset($_POST['usr']) and
isset($_POST['pwd'])) {
    if(Credential::verify_credential($_POST['usr'], $_POST['pwd'])) {
        header("Location: admin.php");
    }

}

require_once $_SERVER["DOCUMENT_ROOT"]."/layout.inc.php";
Starthtml::show('Adminpage');
?>

<?php
Display::start();
Account::login_form();
Display::end();
?>


<?php
Footer::show(basename($_SERVER['SCRIPT_NAME']));
Endhtml::show();
?>
