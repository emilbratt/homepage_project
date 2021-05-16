<?php
session_start();
require_once "../layout.inc.php";
require_once "queries.inc.php";
require_once "database.inc.php";
require_once "../admin/logging.inc.php";
require_once "config.inc.php";
Starthtml::show('Blog Preview');
echo <<< EOT
<header>
<div class="topbar">
    <div class="navbar">\n
    <a href="blog_create.php">Edit Blogpost</a>
    </div>
</div>
</header>
<div class="topbarmargin">
</div>\n
EOT;

?>


<?php
if(isset($_POST['id_blog'])) {
    $_SESSION['id_blog'] = $_POST['id_blog'];
}
if(
    !(isset($_SESSION['tags']))
    or
    !(isset($_SESSION['description']))
) {
    // VALIDATE IF VALUES ARE PASSED FROM blog_admin.php
    $cnxn = db_connect();
    $stmt = $cnxn->prepare("
        SELECT description, tags FROM blog WHERE id_blog = :v
    ");
    $stmt->bindParam(':v', $_POST['id_blog']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['description'] = $result['description'];
    $_SESSION['tags'] = $result['tags'];

}

if(isset($_SESSION['id_blog'])) {
    Blog_content::show($_SESSION['id_blog']);
}
else {
    $cnxn = db_connect(); //  will only load remaining page if successfully connected
    // GET ONLY DESCRIPTION WITH STATUS 0 == NOT POSTED
    $results = BlogSQL::get_descriptions($cnxn);
    $cnxn = null;
    ?>

    <div style="text-align: center;" class="greybox">
        <div class="greyboxbody">
            <h3>No Post Selected</h3>
        </div>
        <div class="greyboxbody">
        <p>Select Post</p>
        <form action="blog_preview.php" id="desc" method="post">
            <select name="id_blog" style="width: 400px;" form="desc">
            <?php
            foreach ($results as $row) {
                $id = $row['id_blog'];
                $desc = $row['description'];
                echo <<<EOT
                <option value=$id>
                $desc
                </option>
                EOT;
            }
            ?>
            </select>
            <br>
            <br>
            <input type="submit"   value="Show"/>
        </form>
        </div>


    </div>

<?php

}

?>

<?php
Footer::show(basename(htmlentities($_SERVER['PHP_SELF'])));
Endhtml::show();
?>
