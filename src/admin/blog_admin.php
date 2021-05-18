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
Starthtml::show('Blog Panel');
Header::show(basename(htmlentities($_SERVER['SCRIPT_NAME'])));
?>

<?php
$cnxn = db_connect();

// GET ONLY DESCRIPTION FROM POSTS WITH STATUS 1 -> NOT ACTIVE
$results = BlogSQL::get_descriptions($cnxn, '1');
$cnxn = null;
?>




<div class="greybox">

    <div class="greyboxbody">
        <h3>Create New Post</h3>
        <form action="blog_create.php" method="post">
            <label for="title"><p>Blog Title</p></label>
            <input class="input_theme_1" type="text"
            placeholder="The title will show on top of your post"
            name="title" required onfocus="this.select()"
            autocomplete="off" autofocus="autofocus">
            <br>
            <label for="description"><p>Description</p></label>
            <input class="input_theme_1" type="text"
            placeholder="Description should be unique for each post"
            name="description" autocomplete="off" required>
            <br>
            <label for="tags"><p>Tags</p></label>
            <input class="input_theme_1" type="text"
            placeholder="Separate with comma tag1, tag2 etc."
            name="tags" autocomplete="off" required >
            <br><br>
            <input class="standalone_button_1" type="submit"
            value="Create">
        </form>
    </div>
</div>

<div class="greybox">
    <div class="greyboxbody">
        <h3>Continue On Post</h3>

        <p>Select unfinished post</p>
        <form action="blog_create.php"  method="post">
            <select class="select_1" name="id_blog">
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
            <br><br>
        <input class="standalone_button_1" type="submit"
        value="Modify"/>
    </form>
    </div>
</div>

<div class="greybox">
    <div class="greyboxbody">
        <h3>Edit Existing Post</h3>
        <form action="blog_modify.php" method="post">
            <input type="hidden" name ="edit" value="true">
            <br>
            <input class="standalone_button_1" type="submit"
            value="Proceed">
        </form>
    </div>
</div>



<?php
Footer::show(basename($_SERVER['SCRIPT_NAME']));
Endhtml::show();
?>
