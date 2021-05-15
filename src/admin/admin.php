<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/layout.inc.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/queries.inc.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/database.inc.php";

Starthtml::show('Adminpage');
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));
?>

<div style="text-align: center;" class="greybox">
        <h1>Admin Page</h1>
        <h1> <?php // echo $_SERVER["DOCUMENT_ROOT"].Config::DATABASE_PATH; ?></h1>
        <p>Log in</p>
</div>

<?php
if(Config::INSTALL) {
    // run install script /setup script etc..
}

?>

<?php
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
