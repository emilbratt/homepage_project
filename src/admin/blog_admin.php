<?php
session_start();
$_SESSION['begin'] = 'OK';
require_once "../layout.inc.php";
require_once "queries.inc.php";
require_once "database.inc.php";
Starthtml::show('Blog Admin');
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));
?>

<?php
$cnxn = db_connect(); //  will only load remaining page if successfully connected
// GET ONLY DESCRIPTION WITH STATUS 1 == NOT POSTED
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
            <input class="increae_input_width" type="text" placeholder="Description should be unique for each post"
            name="description" required>
            <br>
            <label for="tags"><p>Tags</p></label>
            <input class="increae_input_width" type="text" placeholder="Separate with comma tag1, tag2 etc."
            name="tags" required >
            <br>
            <br>
            <input type="submit"  value="Create">
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
            <input type="submit"   value="Modify"/>
        </form>
    </div>
</div>



<?php
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
