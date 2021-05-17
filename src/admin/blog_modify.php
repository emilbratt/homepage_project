<?php
session_start();
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/credential.inc.php";
if(Credential::verify_session()) {
    require_once $_SERVER["DOCUMENT_ROOT"]."/layout.inc.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/admin/queries.inc.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/admin/database.inc.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/admin/upload.inc.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/admin/logging.inc.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/admin/config.inc.php";
}
if(Credential::verify_session() == false) {
    unset($_SESSION['verified_login']);
    header("Location: login.php");
    exit();
}
?>
