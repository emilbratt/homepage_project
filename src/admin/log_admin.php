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
?>

<?php
Starthtml::show('Display Logs');
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));
?>

<?php
$cnxn = db_connect();
$stmt = $cnxn->prepare("
    SELECT * FROM logging
    INNER JOIN log_level
    ON logging.id_log_level = log_level.id_log_level
    ORDER BY id_log DESC LIMIT 100
");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$cnxn = null;
// print_r($result);
// die;
?>

<div style="text-align: center;" class="greybox">
    <div class="greyboxbody">
        <h3>Activity Log</h3>
    </div>

    <div class="greyboxbody">

<table class="log_table" style="width:100%">
    <!-- <caption >Past 100 Logs</caption> -->
    <tr>
    <th>Date</th><th>Log Level</th><th>Subject</th><th>Message</th><th>Time</th>
    </tr>
    <?php
    foreach($result as $row) {
        $level = $row['level_description'];
        $date = $row['date_log'];
        $time = $row['time_log'];
        $subject = $row['subject'];
        $message = $row['message'];
        echo <<<EOT
        <tr>
            <td style="width: 62px;">$date</td>
            <td style="width: 58px;">$level</td>
            <td style="width: 90px;">$subject</td>
            <td>$message</td>
            <td style="width: 48px;">$time</td>
        </tr>
        EOT;

    }
    ?>
</table>


    <pre>
            <?php
                // NOT FINISHED
                // ..CONNECT TO DB AND GET Logs
            ?>
    </pre>


    </div>

</div>
<?php
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
