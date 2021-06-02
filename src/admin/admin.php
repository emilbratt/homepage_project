<?php
session_start();
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/credential.inc.php";
if(Credential::verify_session()) {
    require_once $_SERVER["DOCUMENT_ROOT"]."/layout.inc.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/admin/queries.inc.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/admin/database.inc.php";
}
if(Credential::verify_session() == false) {
    unset($_SESSION['verified_login']);
    header("Location: login.php");
    exit();
}

$message = null;
?>

<?php

if(isset($_POST['change_usr'])) {
    if(Credential::verify_credential($_SESSION['usr'], $_POST['pwd'])) {
        Credential::change_user($_POST['usr_change']);
        $message = 'Username successfully changed';
        Log::user_settings('Username was changed to '.$_POST['usr_change'],1);
        $_SESSION['usr'] = $_POST['usr_change'];
    }

}

if(isset($_POST['change_pwd'])) {
    if($_POST['pwd_change_1'] === $_POST['pwd_change_2']) {
        if(Credential::verify_credential($_SESSION['usr'], $_POST['pwd_old'])) {
            Credential::change_pwd($_POST['pwd_change_1']);
            $message = 'Password successfully changed';
            Log::user_settings('Password was changed',1);
        }

    }
    else {
        $message = 'Passwords don`t match';
    }

}
?>

<?php
Starthtml::show('Adminpage');
Header::show(basename(htmlentities($_SERVER['SCRIPT_NAME'])));
?>

<div style="text-align: center;" class="greybox">
    <h1 style="margin-bottom: 30px;">Admin Page</h1>
    <div class="standalone_link">
        <h3><a  class="standalone_button_1" href="logout.php">Log Out<a></h3>
    </div>
</div>

<?php

    echo Account::start();
    Account::change_usr_form($message);
    echo Account::end();
?>

<?php
    echo Account::start();
    Account::change_pwd_form($message);
    echo Account::end();
?>



?>

<?php
Footer::show(basename($_SERVER['SCRIPT_NAME']));
Endhtml::show();
?>
