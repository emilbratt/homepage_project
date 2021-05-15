<?php
session_start();
require_once $_SERVER["DOCUMENT_ROOT"]."/layout.inc.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/database.inc.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/upload.inc.php";
Starthtml::show('File Uploads');
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));
?>


<?php
Display::start();


Display::end();
?>


<?php
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
