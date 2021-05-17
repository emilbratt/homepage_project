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
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));
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
    </div>
    <div class="greyboxbody">
        <form action="blog_create.php" method="post">
            <label for="title"><p>Blog Title</p></label>
            <input class="increae_input_width" type="text" placeholder="The title will show on top of your post"
            name="title" required onfocus="this.select()" autofocus="autofocus">
            <br>
            <label for="description"><p>Description</p></label>
            <input class="input_theme_2" type="text" placeholder="Description should be unique for each post"
            name="description" required>
            <br>
            <label for="tags"><p>Tags</p></label>
            <input class="input_theme_1" type="text" placeholder="Separate with comma tag1, tag2 etc."
            name="tags" required >
            <br>
            <br>
            <input class="standalone_button_1" type="submit"  value="Create">
        </form>
    </div>
</div>

<div class="greybox">
    <div class="greyboxbody">
        <h3>Continue On Post</h3>
    </div>
    <div class="greyboxbody">
        <p>Select unfinished post</p>
            <form action="blog_create.php" id="desc" method="post">
                <select name="id_blog" form="desc">
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
            <input class="standalone_button_1" type="submit"   value="Modify"/>
        </form>
    </div>
</div>

<div class="greybox">
    <div class="greyboxbody">
        <h3>Continue On Post</h3>

        <form action="blog_modify.php" method="post">
            <label for="title"><p>Blog Title</p></label>
            <input class="increae_input_width" type="text" placeholder="The title will show on top of your post"
            name="title" required onfocus="this.select()" autofocus="autofocus">
            <br>
            <label for="description"><p>Description</p></label>
            <input class="input_theme_2" type="text" placeholder="Description should be unique for each post"
            name="description" required>
            <br>
            <label for="tags"><p>Tags</p></label>
            <input class="input_theme_1" type="text" placeholder="Separate with comma tag1, tag2 etc."
            name="tags" required >
            <br>
            <br>
            <input class="standalone_button_1" type="submit"  value="Create">
        </form>
    </div>
</div>



<?php
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
