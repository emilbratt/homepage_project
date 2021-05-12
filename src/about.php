<?php
require_once "layout.inc.php";
Starthtml::show('My Blog');
Header::show(basename($_SERVER['PHP_SELF']));

?>
<div class="greybox">
    <div class="greyboxtitleleft">
        <h3>About</h3>
    </div>
    <div class="greyboxtitleright">
        <?php echo "<h3>Today is " . date("Y-m-d")."</h3>"?>
    </div>
    <div class="greyboxbody">
        <p>If this website renders bad on your device<br>
            keep in mind that I build it from scratch using no frameworks
        </p>
    </div>
</div>

<div class="greybox">
    <div class="greyboxtitleleft">
        <h3>About</h3>
    </div>
    <div class="greyboxtitleright">
        <?php echo "<h3>Today is " . date("Y-m-d")."</h3>"?>
    </div>
    <div class="greyboxbody">
        <p>
            Hope this site will be finisehd in time
        </p>
    </div>
</div>



<?php
Footer::show();
Endhtml::show();
?>
