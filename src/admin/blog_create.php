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


<?php
Starthtml::show('Blog Edit');
echo <<< EOT
<header>
<div class="topbar">
    <div class="navbar">\n
    <a href="blog_preview.php">Preview Blogpost</a>
    </div>
</div>
</header>
<div class="topbarmargin">
</div>\n
EOT;
?>

<?php
// ADD BLOG CONTENT TO DATABASE
// CHECK TO SEE IF BLOG CONTENT IS ADDED
if(isset($_POST['content']) and isset($_POST['content_id'])) {

    function change_blog_content_title() {

            $cnxn = db_connect();
            $stmt = $cnxn->prepare("
            UPDATE blog_content
            SET
                main_title = :a
            WHERE
                id_blog = :b AND id_type = '2' AND content_number = '1'
            ");
            $stmt->bindParam(':a', $_POST['content']);
            $stmt->bindParam(':b', $_SESSION['id_blog']);
            $stmt->execute();
            $cnxn = null;
    }

    function insert_blog_content_body($field) {
        $cnxn = db_connect();
        $content_id = BlogSQL::get_next_content_number($cnxn, $_SESSION['id_blog']);
        $stmt = $cnxn->prepare("
            INSERT INTO blog_content
                (id_blog, id_type, content_number, $field)
            VALUES
                (:a, :b, :c, :d)
            ");
        $stmt->bindParam(':a', $_SESSION['id_blog']);
        $stmt->bindParam(':b', $_POST['content_id']);
        $stmt->bindParam(':c', $content_id);
        $stmt->bindParam(':d', $_POST['content']);
        $stmt->execute();
        $cnxn = null;
    }

    function insert_blog_content_image() {

        $image_name = Upload::image('blog');

        $category = 'blog';

        $caption = null;
        if(isset($_POST['caption'])) {
            $caption = $_POST['caption'];
        }

        $cnxn = db_connect();
        $content_id = BlogSQL::get_next_content_number($cnxn, $_SESSION['id_blog']);
        $stmt = $cnxn->prepare("
            INSERT INTO blog_content
                (id_blog, id_type, content_number, img_name, img_folder, img_caption)
            VALUES
                (:a, :b, :c, :d, :e, :f)
            ");
        $stmt->bindParam(':a', $_SESSION['id_blog']);
        $stmt->bindParam(':b', $_POST['content_id']);
        $stmt->bindParam(':c', $content_id);
        $stmt->bindParam(':d', $image_name);
        $stmt->bindParam(':e', $category);
        $stmt->bindParam(':f', $caption);
        $stmt->execute();
        $cnxn = null;

    }

    if((int)$_POST['content_id'] == 2) {
        change_blog_content_title();
    }
    else if((int)$_POST['content_id'] == 4) {
        insert_blog_content_body('body_title');
    }
    else if((int)$_POST['content_id'] == 5) {
        insert_blog_content_body('body_text');
    }
    else if((int)$_POST['content_id'] >= 6 and
        (int)$_POST['content_id'] <= 8) {
        insert_blog_content_image();
    }
    else if((int)$_POST['content_id'] >= 9 and
        (int)$_POST['content_id'] <= 11) {
        insert_blog_content_image();
    }

}

if(isset($_POST['blog_content_delete'])) {
    $cnxn = db_connect();
    $stmt = $cnxn->prepare(
        "DELETE FROM blog_content
        WHERE id_blog = :v AND content_number = :n"
    );
    $stmt->bindParam(':v', $_SESSION['id_blog']);
    $stmt->bindParam(':n', $_POST['blog_content_delete']);
    $stmt->execute();
    $cnxn = null;
}

if(isset($_POST['content_swap_1']) and
isset($_POST['content_swap_2'])) {
    if($_POST['content_swap_1'] != $_POST['content_swap_2']) {
        $cnxn = db_connect();
        BlogSQL::swap_blog_content(
            $cnxn, $_SESSION['id_blog'],
            $_POST['content_swap_1'], $_POST['content_swap_2']
        );
        $cnxn = null;
    }
}

if(isset($_POST['blog_content_status_change'])) {
    $cnxn = db_connect();
    BlogSQL::change_blog_status(
        $cnxn, $_SESSION['id_blog'],
        $_POST['blog_content_status_change']
    );
    $cnxn = null;
}

// CONTINUE ON POST
if(isset($_POST['id_blog'])) {

    // VALIDATE IF VALUES ARE PASSED FROM blog_admin.php
    $cnxn = db_connect();
    $stmt = $cnxn->prepare("
        SELECT description, tags
        FROM blog
        WHERE id_blog = :v
    ");
    $stmt->bindParam(':v', $_POST['id_blog']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $_POST['description'] = $result['description'];
    $_POST['tags'] = $result['tags'];

    $_SESSION['id_blog'] = $_POST['id_blog'];
    $_SESSION['description'] = $_POST['description'];
    $_SESSION['tags'] = $_POST['tags'];
}


// CREATE NEW POST
else if(isset($_POST['description']) and
    isset($_POST['tags']) and
    isset($_POST['title'])
) {
    // IF IDENTICAL POST EXISTS
    $cnxn = db_connect();
    $stmt = $cnxn->prepare("SELECT * FROM blog WHERE description = :v");
    $stmt->bindParam(':v', $_POST['description']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);


    // if($stmt->rowCount() > 0) {
    if($result != null) {
        // IF EXISTS, END SCRIPT AFTER SHOWING CONTENT
        echo Blogpost::start();
        Blogpost::main_title('Blog Already Exists','left');
        Blogpost::body_title('Blog description');
        Blogpost::body_text($result['description']);
        Blogpost::body_title('Tags');
        Blogpost::body_text($result['tags']);
        Blogpost::body_title('Created:');
        Blogpost::body_text(
            'Date: '.$result['date_started'].' - Time: '.substr(
                $result['time_started'],0,5
            )
        );
        $blog_id = $result['id_blog'];
        $form = <<<EOT
        <form action="" method="post">
        <input type="hidden" name="id_blog" value="$blog_id">
        <input placeholder="1000" type="submit" value="Continue">
        <form>
        EOT;
        Blogpost::body_text($form);

        echo Blogpost::end();
        Footer::show(basename($_SERVER['SCRIPT_NAME']));
        Endhtml::show();
        $cnxn = null;
        exit;
    }
    else {
        // IF IDENTICAL DONT EXIST, INSERT BASE DATA AND ASK FOR BLOG TITLE
        $result = BlogSQL::get_last_blog_id($cnxn);
        $_SESSION['id_blog'] = $result['next_id_blog'];
        $_SESSION['description'] = $_POST['description'];
        $_SESSION['tags'] = $_POST['tags'];


        BlogSQL::insert_base_data($cnxn,
            $_SESSION['description'],  // DESCRIPTON
            $_SESSION['tags'],         // TAGS
            $_POST['title'],
            date("Y-m-d"),          // DATE
            date("H:i:s")           // TIME
        );
        // echo $cnxn->lastInsertId();
        $cnxn = null;

    }

}

?>


<?php
// VISUAL RENDERING OF BLOG POST OPTIONS STARTS HERE

$cnxn = db_connect();
$results_content_types = BlogSQL::get_all_content_types($cnxn);
$results_blog_status = BlogSQL::get_blog_status_types($cnxn);
$title_placeholder = BlogSQL::get_blog_post_title($cnxn, $_SESSION['id_blog']);
$blog_status = BlogSQL::get_blog_status($cnxn, $_SESSION['id_blog']);
$results_content = BlogSQL::get_blog_content_and_type($cnxn, $_SESSION['id_blog']);


if(!(isset($_SESSION['description'])) or !(isset($_SESSION['tags']))) {
    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $extra = 'blog_admin.php';
    header("Location: http://$host$uri/$extra");
    exit;
}

$cnxn = null;
?>



<div class="greybox">

    <div class="greyboxbody">
        <p><b>Title:</b> <?php echo $title_placeholder; ?></p>
        <p><b>Status:</b> <?php echo $blog_status; ?></p>
        <p><b>Description:</b> <?php echo $_SESSION['description']; ?></p>
        <p><b>Tags:</b> <?php echo $_SESSION['tags']; ?></p>
    </div>
    <br>

    <div class="greyboxbody">
        <form
        action=<?php echo htmlentities($_SERVER['SCRIPT_NAME']);?>
        method="post">
            <input class="increae_input_width" type="hidden" name="content_id" value="2">
            <label for="content"><h3>Change Blogpost Title</h3></label>
            <input class="input_theme_1" type="text" placeholder="<?php echo $title_placeholder; ?>"
            style="width: 400px;" autocomplete="off" name="content">
            <input class="submit_theme_1" type="submit"  value="Change Title">
        </form>
        <br>
        <form id="in_line_position_greyboxbody"
        action=<?php echo htmlentities($_SERVER['SCRIPT_NAME']);?>
        method="post">
            <label for="blog_content_status_change"><h3>Change Blogpost Status</h3></label>
            <select class="input_theme_1" name="blog_content_status_change" style="width: 400px;" >
            <?php
            foreach($results_blog_status as $row) {
                $alias = $row['alias'];
                $id = $row['id_status'];
                    echo <<<EOT
                    <option value=$id>
                    $alias
                    </option>
                    EOT;
                }
            ?>
            </select>
            <input class="submit_theme_1" type="submit"   value="Change Status"/>
        </form>

        <form id="in_line_position_greyboxbody"
        action=<?php echo htmlentities($_SERVER['SCRIPT_NAME']);?>
        method="post">
            <label for="blog_content_type_create"><h3>Insert Blog Content</h3></label>
            <select class="input_theme_1" name="blog_content_type_create" style="width: 400px;" >
            <?php
            foreach($results_content_types as $row) {
                if($row['id_type'] > 3) { // DO NOT SHOW TITLE OPTION
                    // $desc = $row['desc_type'];
                    $alias = $row['alias'];
                    $id = $row['id_type'];
                    echo <<<EOT
                    <option value=$id>
                    $alias
                    </option>
                    EOT;
                }
            }
            ?>
            </select>
            <input class="submit_theme_1" type="submit" value="Add Content"/>
        </form>
        <br><br>
        <form id="in_line_position_greyboxbody"
        action=<?php echo htmlentities($_SERVER['SCRIPT_NAME']);?>
        method="post">
            <label for="blog_content_delete"><h3>Delete Blog Content</h3></label>
            <select class="input_theme_1" name="blog_content_delete"  style="width: 400px;" >
            <?php
            $num = 1;
            foreach($results_content as $row) {
                if($row['content_number'] > 2) {
                    $desc = $row['desc_type'];
                    $alias = ( //CONCATENATE WHATEVER HAS VALUE IN IT
                        strval($num).'. '. ' '.$row['alias'].' '.$row['body_title'].substr($row['body_text'],0,50).
                        $row['img_name'].$row['img_caption'].$row['body_title']
                    );
                    $content_number = $row['content_number'];
                        echo <<<EOT
                        <option value=$content_number>
                        $alias
                        </option>
                        EOT;
                    $num++;
                }
            }
            ?>
            </select>
            <input class="submit_theme_1" type="submit" value="Delete Content"/>
        </form>

        <form id="in_line_position_greyboxbody"
        action=<?php echo htmlentities($_SERVER['SCRIPT_NAME']);?>
        method="post">
            <input type="hidden" name="content" value="true">
            <label for="content_swap_1"><h3>Swap Content Position</h3></label>
            <select class="input_theme_1" name="content_swap_1" style="display: inline-block; width: 200px;">
            <?php
            $num = 1;
            foreach($results_content as $row) {
                if($row['content_number'] > 2) {
                    $desc = $row['desc_type'];
                    $alias = ( //CONCATENATE WHATEVER HAS VALUE IN IT
                        strval($num).'. '. ' '.$row['alias'].' '.$row['body_title'].substr($row['body_text'],0,50).
                        $row['img_name'].$row['img_caption'].$row['body_title']
                    );
                    $content_number = $row['content_number'];
                        echo "<option value=$content_number> $alias </option>";
                    $num++;
                }
            }
            ?>
            </select>
            <select class="input_theme_1" name="content_swap_2" style="display: inline-block; width: 200px;">
            <?php
            $num = 1;
            foreach($results_content as $row) {
                if($row['content_number'] > 2) {
                    $desc = $row['desc_type'];
                    $alias = ( //CONCATENATE WHATEVER HAS VALUE IN IT
                        strval($num).'. '. ' '.$row['alias'].' '.$row['body_title'].substr($row['body_text'],0,50).
                        $row['img_name'].$row['img_caption'].$row['body_title']
                    );
                    $content_number = $row['content_number'];
                        echo "<option value=$content_number> $alias </option>";
                    $num++;
                }
            }
            ?>
        </select><br>
            <input class="submit_theme_1" type="submit" value="Swap Content"/>
        </form>



    </div>


</div>


<?php
// ADD/CHANGE/REMOVE CONTENT FROM DATABASE
if(isset($_POST['blog_content_type_create'])) {
    Blog_content::create($_POST['blog_content_type_create']);
}

?>

<?php
Footer::show(basename($_SERVER['SCRIPT_NAME']));
Endhtml::show();
?>
