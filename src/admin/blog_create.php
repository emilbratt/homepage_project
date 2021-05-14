<?php
session_start();
require_once "../layout.inc.php";
require_once "queries.inc.php";
require_once "database.inc.php";
require_once "../admin/logging.inc.php";
require_once "config.inc.php";
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

    function insert_blog_content_image($field) {
        $root_path = $_SERVER["DOCUMENT_ROOT"];

        // AVOID URL CONFLICTS BY REPLACING WHITESPACE WITH UNDERSCORE IN FILENAME
        $file = str_replace(' ', '_', $_FILES["content_file"]["name"]);

        $caption = null;
        if(isset($_POST['caption'])) {
            $caption = $_POST['caption'];
        }

        if(!(is_dir($root_path.Config::IMAGE_PATHS['upload']))) {
            mkdir($root_path.Config::IMAGE_PATHS['upload'], 0775);
        }

        $upload_path = $root_path.Config::IMAGE_PATHS['upload'].'/blog/';
        $upload_path = str_replace(' ', '_', $upload_path);

        if(!(is_dir($upload_path))) {mkdir($upload_path, 0775);}

        // $target =  $upload_path . basename($_FILES["content_file"]["name"]);
        $target =  $upload_path . $file;


        // $file = basename($target);


        Log::debug(
        '$target: '.$target. "<br>" .
        '$file: '.$file . "<br>" .
        'basename($_FILES["content_file"]["name"]) '. basename($_FILES["content_file"]["name"]) . "<br>"
        );


        $abort = false; // UPLOAD WILL ABORT IF SET TO TRUE
        $ret_msgs = array(); // RETURN MESSAGES IF UPLOAD FAILS

        Log::upload('Starting upload of file '.$target.
        ' for blog id '.$_SESSION['id_blog'].
        ' and content number '.$_POST['content_id'],1
        );

        // GO THORUGH STEPS TO VALIDATE NEW IMAGE

        // FILE SIZE
        if($_FILES["content_file"]["error"] == 2) {
            $max_file_size = Config::IMAGE_MAX_FILESIZE['upload'];
            Log::upload(
                $file . ': File exceeds '.$max_file_size.
                ' byte size limit!',
                4
            );
            array_push(
                $ret_msgs,
                $file . ': File exceeds '.
                $max_file_size.' byte size limit!'
            );
            $abort = true;
        }

        // FILE EXTENTION
        $file_ext_check = strtolower(pathinfo($target,PATHINFO_EXTENSION));
        if(!(in_array($file_ext_check, Config::FILE_EXT_ALLOWED['image']))) {
            Log::upload($file . ': File extention is not valid', 4);
            array_push(
                $ret_msgs, $file .
                ': File extention is not valid'
            );
            $abort = true;
        }

        // IS IMAGE FILE BASED ON METADATA
        $file_check = getimagesize($_FILES["content_file"]["tmp_name"]);
        if($file_check == false) {
            Log::upload($file . ': File upload is not an image!', 4);
            array_push($ret_msgs, $file . ': File upload is not an image!');
            $abort = true;
        }

        // CHECK FOR NAME COLLISION OF EXISTING IMAGE REGARDLESS OF FILE EXTENTION
        $file_split = explode(".", $file);
        $image_name = $file_split[0];
        foreach(Config::FILE_EXT_ALLOWED['image'] as $extention) {
            if(file_exists($upload_path.$image_name.'.'.$extention)) {
                echo <<<EOT
                <div class="greybox"><div class="greyboxbody">
                <h2 style="text-align: center;">
                    Image already exists<br>Using existing image
                </h2>
                <p style="text-align: center;">
                    If image is incorrect<br>upload same image<br>with different filename
                </p>
                </div></div>
                EOT;
                Log::upload(
                    $target.' already exists with extention '.
                    $extention,
                    1
                );
                array_push(
                    $ret_msgs, $target.' already exists with extention '.
                    $extention
                );
                $abort = true;
            }
        }

        if(!(is_uploaded_file($_FILES["content_file"]["tmp_name"]))) {
            $abort = true;
            Log::upload($file . ': Upload aborted due to avoiding a file upload exploit', 2);
        }


        if($abort == false) {
            echo '<div class="greybox">';
            echo '<div class="greyboxbody">';
            if (move_uploaded_file(
                $_FILES["content_file"]["tmp_name"], $target)
            ) {
                chmod($target,0770);
                echo <<<EOT
                <h3>$file uploaded successfully</h3><br>
                EOT;
                Log::upload(
                    htmlspecialchars($file) . ': Was uploaded to '.$upload_path,
                    1
                );
            } else {
                foreach($ret_msgs as $msg) {
                    echo <<<EOT
                    <h3>$msg</h3><br>
                    EOT;
                }
                Log::upload(
                    'Could not upload ' .htmlspecialchars($file).
                    'to '.$upload_path,
                    1
                );
            }
            echo '</div>';
            echo '</div>';
        }


        if($abort == false) {
            // DO EXTRA CHECK TO ENSURE FILE IS UPLOADED
            // TO AVVOID A FILE UPLOAD ATTACK BEFORE SCRIPT RUNS

            // $script = $root_path.Config::IMAGE_SCRIPTS['resize_uploaded_image'];
            $script = $root_path.Config::IMAGE_SCRIPTS['resize_image'];
            if(!(is_file($script))) {
                Log::upload('Could not resize '.$file.' the script '.$script.' does not exist', '4');
            }

            $output_value = null; // FOR DEBUGGING
            $return_value = null; // FOR DEBUGGING

            exec("python3 $script '$image_name' '$target' blog jpg", $output_value, $return_value);

            foreach($output_value as $v) {
                echo <<<EOT

                <div class="greybox"><div class="greyboxbody">
                <h2 style="text-align: center;">$v</h2>
                </div></div>
                EOT;
            }

            if($return_value != 0) {
                Log::image_resize(
                    'Could not resize ' .htmlspecialchars($file).
                    'to '.$upload_path,
                    5
                );
                die('<h2 style="text-align: center;">Could not resize image, check log</h2>');
            }

            Log::image_resize(
                'successfully resized ' .htmlspecialchars($file),
                1
            );
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
        $stmt->bindParam(':e', $field);
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
        insert_blog_content_image('blog');
    }
    else if((int)$_POST['content_id'] >= 9 and
        (int)$_POST['content_id'] <= 11) {
        insert_blog_content_image('blog');
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
        Blogpost::start();
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

        Blogpost::end();
        Footer::show(basename($_SERVER['PHP_SELF']));
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
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
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
        action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
        method="post">
            <input class="increae_input_width" type="hidden" name="content_id" value="2">
            <label for="content"><h3>Change Blogpost Title</h3></label>
            <input type="text" placeholder="Current Title: <?php echo $title_placeholder; ?>"
            style="width: 400px;" name="content">
            <br><br>
            <input type="submit"  value="Change Title">
        </form>

        <form id="in_line_position_greyboxbody"
        action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
        method="post">
            <label for="blog_content_status_change"><h3>Change Blogpost Status</h3></label>
            <select name="blog_content_status_change" style="width: 400px;" >
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
            </select><br><br>
            <input type="submit"   value="Change Status"/>
        </form>

        <form id="in_line_position_greyboxbody"
        action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
        method="post">
            <label for="blog_content_type_create"><h3>Insert Blog Content</h3></label>
            <select name="blog_content_type_create" style="width: 400px;" >
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
            </select><br><br>
            <input type="submit"   value="Add Content"/>
        </form>

        <form id="in_line_position_greyboxbody"
        action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
        method="post">
            <label for="blog_content_delete"><h3>Delete Blog Content</h3></label>
            <select name="blog_content_delete" style="width: 400px;" >
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
            </select><br><br>
            <input type="submit"   value="Delete Content"/>
        </form>

        <form id="in_line_position_greyboxbody"
        action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
        method="post">
            <input type="hidden" name="content" value="true">
            <label for="content_swap_1"><h3>Swap Content Position</h3></label>
            <select  name="content_swap_1" style="width: 200px;" >
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
            <select name="content_swap_2" style="width: 200px;" >
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
            </select><br><br>
            <input type="submit"   value="Swap Content"/>
        </form>



    </div>


</div>


<?php
// ADD/CHANGE/REMOVE CONTENT FROM DATABASE
if(isset($_POST['blog_content_type_create'])) {
    Blogcontent::create($_POST['blog_content_type_create']);
}

?>

<?php
// Blogcontent::show($_SESSION['id_blog']);
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
