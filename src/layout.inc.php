<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . '/admin/database.inc.php';
    require_once 'admin/queries.inc.php';
    require_once 'admin/config.inc.php';
    // require_once "admin/file_upload.inc.php";
    require_once 'admin/logging.inc.php';
?>

<?php
    class Pages {
        public static $main_pagess = array(
            'Home' => 'index.php',
            'Blog' => 'blog.php',
            // 'Gallery' => 'gallery.php',
            // 'About' => 'about.php',
            // 'CV' => 'cv.php',
        );

        public static $admin_pagess = array(
            'Admin' => 'admin.php',
            // 'Upload' => 'upload_admin.php',
            'Frontpage' => 'frontpage_admin.php',
            'Blog' => 'blog_admin.php',
            'Create' => 'blog_create.php',
            // 'Preview' => 'blog_preview.php',
            // 'Scan Photos' => 'image_scan.php',
            'Activity' => 'log_admin.php',
            'Preview' => 'blog_preview.php',
            'Log in' => 'login.php',
        );

        public static $excluded_menu_admin_pagess = array(
            'Create', 'Preview', 'Log in'
        );
    }

    class Starthtml {
        public static function show($title) {
            echo <<<EOT
            <!DOCTYPE html>
            <html lang="no">
            <head>
                <meta charset="utf-8">
                <!--<link rel="stylesheet" href="style.css">-->
                <link rel="stylesheet" href="../style.css">
                <!--<meta http-equiv="refresh" content="5" >-->
                <title>$title</title>
            </head>
            <body>\n
            EOT;
        }
    }


    class Header {
        public static function show($file = null) {
            if(in_array($file, Pages::$main_pagess))  {
                echo <<< EOT
                <header>
                <div class="topbar">
                    <div class="navbar">\n
                EOT;
                $tag_start = '<a href=';
                $active_page = ' id="pageselector" ';
                $tag_end = '</a>';
                foreach(Pages::$main_pagess as $title => $page) {
                    if (stripos($page, $file) !== false) {
                        echo $tag_start.$page.$active_page.">".$title."".$tag_end;
                    }
                    else {
                        echo $tag_start.$page.">".$title.$tag_end;
                    }
                }
                echo <<<EOT

                    </div>
                </div>
                </header>

                EOT;
            }
            else if(in_array($file, Pages::$admin_pagess))  {
                echo <<< EOT
                <header>
                <div class="topbar">
                    <div class="navbar">\n
                EOT;
                $tag_start = '<a href=';
                $active_page = ' id="pageselector" ';
                $tag_end = '</a>';
                foreach(Pages::$admin_pagess as $title => $page) {
                    if(in_array($title,Pages::$excluded_menu_admin_pagess)) {
                        continue; // DON`T INCLUDE IN HEADER BUT ALLOW PAGE
                    }
                    else if(basename($file) == $page) {
                        echo $tag_start.$page.$active_page.">".$title."".$tag_end;
                    }
                    else {
                        echo $tag_start.$page.">".$title.$tag_end;
                    }
                }
                echo <<<EOT

                    </div>
                </div>
                </header>

                EOT;
            } else {
                die("Header::show() -> Not a valid page");
            }

        }
    }


    class Footer {

        public static function show($file = null) {

            if(in_array($file, Pages::$main_pagess)) {
                $cnxn = db_connect();
                $results = FrontpageSQL::get_footer_links($cnxn);
                echo <<<EOT

                <footer>
                <div class="bottombar">
                    <div class="navbar_logo">
                EOT;
                $logo_path = Config::IMAGE_PATHS['logos'];
                foreach($results as $row) {
                    if(!(empty($row['url']))) {
                        echo '
                            <a href="'.$row['url'].'">
                                <img src="'.$logo_path.$row['name'] . '.png" alt="no image"
                                class="logo_link">
                            </a>';
                    }
                }
                echo <<<EOT
                    </div>
                </div>
                </footer>
                EOT;
            }
            else if(in_array($file, Pages::$admin_pagess)) {
                echo <<<EOT

                <footer>
                <div class="bottombar">
                <div class="navbar">
                EOT;
                if(basename($file) == 'blog_create.php') {
                    echo <<<EOT

                    <a href="blog_admin.php">Back</a>
                    EOT;
                }
                echo <<<EOT

                <a href="../index.php">Home</a>
                </div>
                </div>
                </footer>

                EOT;
            }
            else {
                die("Footer::show() -> Not a valid page");
            }
        }
    }

    class Endhtml {
        static public function show() {
            echo <<<EOT
            </body>
            </html>
            EOT;
        }
    }


    class Display {
        protected static $media_query_medium = 'min-width:800px';
        protected static $media_query_large = 'min-width:1200px';

        public static function start($align = null) {
            if($align) {
                // LEFT OR RIGHT GREYBOX
                echo '<div class="greybox_'.$align.'">';
                return null;
            }
            // STANDARD GERYBOX
            echo '<div class="greybox">';
        }


        public static function main_title($title, $alignment = 'center') {
            echo '<div class="greybox_title_'.$alignment.'">';
            echo '<h3>'.$title.'</h3>';
            echo '</div>';
        }

        public static function body_title($title) {
            echo '<div class="greyboxbody">';
            echo '<h3>'.$title.'</h3>';
            echo '</div>';
        }

        public static function body_text($message) {
            echo '<div class="greyboxbody">';
            echo '<p>'.$message.'</p>';
            echo '</div>';
        }

        public static function end() {
            echo '</div>';
        }

        public static function table() {

        }

    }


    class Account extends Display {


        public static function login_form() {
            ?>
            <div class="greyboxbody">
                <h3>Log in</h3>
                <form id="in_line_position_greyboxbody"
                action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
                method="post" >
                    <input type="hidden" name="login" value="true">
                    <input type="text" onfocus="this.select()"
                    autofocus="autofocus"
                    name="usr" placeholder="Username" class="input_theme_1"
                    class="increae_input_width" required>
                    <br><br>
                    <input type="password"
                    name="pwd" placeholder="Password" class="input_theme_1"
                    class="increae_input_width" required>
                    <br><br>
                    <input type="submit" class="submit_theme_1" value="Log in">
                </form>
            </div>
            <?php
        }

        public static function change_usr_form() {
            ?>
            <div class="greyboxbody">
                <h3 style="margin-bottom: 30px;">Change User</h3>
                <form id="in_line_position_greyboxbody"
                action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
                method="post" >
                    <input type="hidden" name="change_usr" value="true">
                    <input type="text"  style="margin-bottom: 20px;"
                    name="usr_change" placeholder="New Username" class="input_theme_1"
                    class="increae_input_width" required>

                    <input type="password"
                    name="pwd" placeholder="Current Password" class="input_theme_1"
                    class="increae_input_width" required>
                    <br><br>
                    <input type="submit" class="submit_theme_1" value="Change">
                </form>

            </div>
            <?php
        }

        public static function change_pwd_form($message = false) {

            // CHECK LOG TO SEE IF NEW PASSWORD HAS BEEN SET AT LEAST ONC SINCE SETUP
            $cnxn = db_connect($pragma = false);
            $stmt = $cnxn->prepare("
            SELECT id_log FROM logging
            WHERE id_log_level = '1' AND message = 'Password was changed'
            LIMIT 1
            ");
            $stmt->execute();
            $val = $stmt->fetchColumn(0);

            // SET WARNING MESSAGE IF PASSWORD NEVER WAS CHANGED (IF NO OTHER MESSAGE WAS PASSED)
            if(!($message)) {
                if(!($val)) {
                    $message = '<strong>IMPORTANT:</strong><br>Please change your password<br>' .
                    'as soon as possible';
                }
            }
            ?>

            <div class="greyboxbody">
                <h3 style="margin-bottom: 30px;">Change Password</h3>
                <form id="in_line_position_greyboxbody"
                action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
                method="post" >
                
                <?php if($message != false) {
                    echo '<label for="change_pwd"><p>'.$message.'</p></label>';
                }?>
                    <input type="hidden" name="change_pwd" value="true">

                    <input type="password" style="margin-bottom: 20px;"
                    name="pwd_old" placeholder="Current Password" class="input_theme_1"
                    class="increae_input_width" required>

                    <input type="password" style="margin-bottom: 20px;"
                    name="pwd_change_1" placeholder="New Password" class="input_theme_1"
                    class="increae_input_width" required>

                    <input type="password"
                    name="pwd_change_2" placeholder="Verify Password" class="input_theme_1"
                    class="increae_input_width" required>
                    <br><br>
                    <input type="submit" class="submit_theme_1" value="Change">
                </form>

            </div>
            <?php
        }

    }

    class Frontpage extends Display {

        public static function text_field($front_page_data) {
            echo '
            <div class="greybox_inline_block">
            <div class="greyboxbody" id="left_side">
            ';
            foreach($front_page_data as $k => $v) {
                echo '<h3>'.$v['body_title'].'</h3>';
                echo '<p>'.$v['body_text'].'</p><br>';
            }
            echo '
            </div>
            </div>
            ';
        }

        public static function profile_pic($profile_pic_name = null) {
            if($profile_pic_name == null) {
                echo '
                <div class="greybox_inline_block">
                <div class="greyboxbody"  style="text-align: right;">
                    <h3 style="text-align: left;">No profile picture</h3>
                    <p style="text-align: left;">Go to admin panel and add a profile picture</p>
                </div>
                </div>
                ';
                return null;
            }
            if($profile_pic_name != null) {
                $profile_pic_name = $profile_pic_name.'.jpg';
                $resize_path = Config::IMAGE_PATHS['converted'];
                echo '
                <div class="greybox_inline_block">
                <div class="greyboxbody"  style="text-align: right;">
                    <picture>
                        <source media="('.Display::$media_query_medium.')" '.
                        'loading="lazy"
                        srcset="'.$resize_path.'1200/profile/'.$profile_pic_name.'">

                        <img src="'.$resize_path.'800/profile/'.$profile_pic_name.'"
                        id="right_side" alt="" class="profile_pic"
                        loading="lazy">
                    </picture>
                </div>
                </div>
                ';
            }
        }

        public static function latest_blogpost($id_blog = null, $main_title = null) {
            if($id_blog == null or $main_title == null) {
                $main_title = 'No blogposts';
            }
            echo '
            <div class="greybox_right_body" id="right_side_">
                <h3>Latest blogpost</h3>
                <div class="standalone_link">
                    <p><a  href="/blog.php?id_blog='.$id_blog.'">'.
                    $main_title.'</a></p>
                </div>
            </div>
                ';
        }

        public static function contact_field($email = null) {
            echo '
            <div class="greybox_left_body" id="left_side_">
                <h3>Contact</h3>
                <div class="standalone_link">
                <p><a href="mailto:'.$email.'">E-mail</a></p>
                </div>
            </div>
            ';
        }
    }


    class Frontpage_content extends Display {

        public static function text_field_left_form() {

            $cnxn = db_connect($pragma = false);
            $stmt = $cnxn->prepare("
                SELECT content_number, body_title
                FROM front_page
            ");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cnxn = null;

            ?>

            <div class="greyboxbody">
                <h1>Textfield</h1>
                <form action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
                method="post" >
                    <input type="hidden" name="textfield" value="add">
                    <h3>Title</h3>
                    <input type="text" onfocus="this.select()"
                    autofocus="autofocus"
                    name="title" placeholder=""
                    class="increae_input_width">
                    <h3>Paragraph</h3>
                    <textarea name="paragraph"
                    style="height: 270px;" required></textarea>
                    <br><br>
                    <input type="submit" style="width: 270px;" value="Add">
                </form>

                <br>

                <form id="in_line_position_greyboxbody"
                action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
                method="post">
                    <input type="hidden" name="textfield" value="delete">
                    <label for="delete"><h3>Delete Text</h3></label>
                    <select name="content_number" style="width: 400px;" >
                    <?php
                    $num = 1;
                    foreach($result as $row) {
                            $content_number = $row['content_number'];
                            $alias = $row['body_title'];
                                    echo "<option value=$content_number>$num. $alias </option>";
                            $num++;
                    }
                    ?>
                    </select><br><br>
                    <input type="submit"   value="Delete Content"/>
                </form>



                <form id="in_line_position_greyboxbody"
                action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
                method="post">
                    <input type="hidden" name="textfield" value="swap">
                    <label for="swap_1"><h3>Swap Position</h3></label>
                    <select name="swap_1" style="width: 200px;" >
                    <?php
                    $num = 1;
                    foreach($result as $row) {
                            $alias = $row['body_title'];
                            $content_number = $row['content_number'];
                                echo "<option value=$content_number>$num. $alias </option>";
                            $num++;

                    }
                    ?>
                    </select>
                    <select name="swap_2" style="width: 200px;" >
                    <?php
                    $num = 1;
                    foreach($result as $row) {
                            $alias = $row['body_title'];
                            $content_number = $row['content_number'];
                                    echo "<option value=$content_number>$num. $alias </option>";
                            $num++;
                        }

                    ?>
                    </select><br><br>
                    <input type="submit"   value="Swap Content"/>
                </form>


            </div>
            <?php

        }



        public static function profile_pic_form() {

            $cnxn = db_connect($pragma = false);
            $stmt = $cnxn->prepare("
                SELECT id_image, file_name
                FROM image_org
                WHERE category = 'profile';
            ");
            $stmt->execute();

            ?>
            <div class="greyboxbody">
            <h1>Frontpage image</h1>
                <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ;?>"
                method="post" id="in_line_position_greyboxbody"
                enctype="multipart/form-data">
                <label for="file"><h3>Upload</h3></label>
                <input type="hidden" name="profile_pic" value="upload">
                <input type="file" name="file" >
                <br><br>
                <input type="submit" value="Upload">
                </form>


                <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ;?>"
                method="post" id="in_line_position_greyboxbody">
                <input type="hidden" name="profile_pic" value="choice">
                <label for="file"><h3>Chose Existing</h3></label>
                <select name="id_image" style="width: 400px;" >
                <?php
                $num = 1;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $alias = $row['file_name'];
                    $id_image = $row['id_image'];
                        echo "<option value=$id_image>$num. $alias </option>";
                    $num++;
                }
                ?>
                </select><br><br>
                <input type="submit" value="Select">
                </form>
            </div>
            <?php

        }


        public static function social_network_form() {

            $icons_dir = $_SERVER["DOCUMENT_ROOT"].Config::IMAGE_PATHS['logos'];
            $icon_images = glob($icons_dir."*");
            $icons = scandir($icons_dir);

            ?>
            <div class="greyboxbody">

            <?php
                // THE SECTION UNDER LOADS THE PNG IMAGES FOUND HERE /logos/..

                // INPUT FIELDS ARE BASED ON EACH IMAGE AND ITS NAME.
                // IF A LOGO HAS THE NAME github.png, IT IS IMPLIED THAT
                // THIS LOGO IS FOR GITHUB AND YOU SHOULD PUT IN YOUR github
                // LINK INTO THIS TEXT FIELD. ADDING MORE LOGOS IN THIS FOLDER
                // WILL THUS ADD CORRESPONDING OPTION INTO THIS SCRIPT
            ?>
            <h1>Links for Social Networks</h1>
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ;?>"
            method="post">
            <input type="hidden" name="links" value="true">
            <?php
                $cnxn = db_connect($pragma = false);
                $stmt = $cnxn->prepare("
                SELECT * FROM social_networks
                -- WHERE name = :v
                ");
                $stmt->execute();
                $name_exists = array(); // STORES NAMES ONLY
                $networks = array();    // STORES NAMES AND URL
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $networks[$row['name']] = $row['url'];
                    array_push($name_exists, $row['name']);
                }

                foreach($icons as $icon) {
                    if($icon != '.' and $icon != '..') { // AVOID GLOBAL . AND .. IN UNIX DIRECTORY

                        // CHECK IF png EXTENTION
                        $path = Config::IMAGE_PATHS['logos'].$icon;
                        $file_ext_check = strtolower(pathinfo($path,PATHINFO_EXTENSION));
                        if(in_array($file_ext_check, Config::FILE_EXT_ALLOWED['image'])) {
                            $icon = explode('.', $icon)[0];

                            if(!(in_array($icon, $name_exists))) {
                                // INSERT SOCIAL NETWORK NAME INTO DATABASE
                                    // IF NAME DOESN`T EXIST
                                Log::user_settings(
                                    'New icon "' . $icon . '" found in /logos/', 1
                                );
                                $stmt = $cnxn->prepare("
                                INSERT INTO social_networks (name)
                                VALUES (:v)
                                ");
                                $stmt->bindParam(':v', $icon);
                                $stmt->execute();
                            }

                            // GET PLACEHOLDER FOR INSERT FIELD
                            $placeholder = null;
                            if(isset($networks[$icon])) {
                                $placeholder = $networks[$icon];
                            }
                            if($placeholder == null) {
                                $placeholder = "https://$icon";
                            }

                            // RENDER INPUT FIELD
                            ?>
                            <div id="in_line_position_greyboxbody">

                            <label for="<?php echo $icon; ?>">
                                <h3><?php echo $icon; ?></h3>
                            </label>
                            <input type="text"
                            name="<?php echo $icon; ?>"
                            placeholder="<?php echo $placeholder; ?>"
                            style="width: 80%;">
                            <br><br>
                            </div>
                            <?php
                        }

                    }
                }
                $cnxn = null;
            ?>
            <br>
            <input type="submit" style="width: 270px;"
            name="links" value="Update">

            <input id="small_screen_button" type="submit" style="width: 270px;"
            name="links" value="Reset All">
            </form>

            </div>
            <?php

        }

    }

    class Blogpost extends Display {




        public static function body_image($img, $cat, $align, $cap = null) {
            $cnxn = db_connect();
            $results = ImageSQL::get_image_resize_target($cnxn, $img, $cat);

            echo "<picture>";
            $targets = array();
            foreach($results as $row) {
                if(in_array($row['long_edge'],Config::IMAGE_RES)) {
                    array_push($targets, $row['resize_target']);
                }
            }
            if($align == 'center' and isset($cap)) {
                echo '<source media="('.Display::$media_query_large.')" '.
                'loading="lazy" srcset="'.$targets[1].
                '">';

                echo '<source media="('.Display::$media_query_medium.')" '.
                'loading="lazy" srcset="'.$targets[2].
                '">';

                echo '<img class="greybox_img_'.$align.
                '" src="'.$targets[0].'" loading="lazy">';

                echo <<<EOT
                <figcaption class="greybox_img_$align" style="margin-top: -20px;">
                $cap
                </figcaption>
                EOT;
                }

            else {
                echo '<source media="('.Display::$media_query_large.')" '.
                'loading="lazy" srcset="'.$targets[1].
                '">';

                echo '<source media="('.Display::$media_query_medium.')" '.
                'loading="lazy" srcset="'.$targets[2].
                '">';

                echo '<img class="greybox_img_'.$align.
                '" src="'.$targets[0].'" loading="lazy">';

                if($cap) {
                    switch ($align) {
                        case 'right';
                            $align = 'left';
                            break;
                        case 'left';
                            $align = 'right';
                            break;
                        default;
                            $align = 'center';
                    }
                    echo <<<EOT
                    <figcaption class="greybox_img_$align">
                    $cap
                    </figcaption>
                    EOT;
                }
            }
            echo '</picture>';
        }

    }



    class Blog_content {

        public static function create($id_type) {
            $script = htmlentities($_SERVER['PHP_SELF']);
            $cnxn = db_connect();
            $stmt = $cnxn->prepare("
                SELECT alias FROM blog_content_type
                WHERE id_type = :id;
                ");
            $stmt->bindParam(':id', $id_type);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $alias = $result['alias'];

            // SET HOW PHP WILL HANDLE FORM INPUT
            if((int)$id_type >= 6) { // FOR FILE INPUT
                $enc_type = 'multipart/form-data'; // HAVE TO BE SET IF FILE
            }
            else { // FOR TEXT INPUT
                $enc_type = 'application/x-www-form-urlencoded'; // IS DEFAULT IF NOT SET
            }


            Blogpost::start();
            echo <<<EOT
            <div class="greyboxbody">
            EOT;

            echo <<<EOT
            <h3>Insert $alias</h3>
            <form action="$script" method="post" style="margin-top: 10px;" enctype="$enc_type">
            <input type="hidden" name="content_id" value="$id_type">
            EOT;

            if((int)$id_type < 5) {
                // FOR TITLE
                echo <<<EOT
                <input type="text" placeholder="$alias"
                style="width: 400px;" name="content"
                onfocus="this.select()" autofocus="autofocus" required>
                EOT;
            }
            else if((int)$id_type == 5) {
                // FOR ARTICLE
                echo <<<EOT
                <textarea name="content"
                onfocus="this.select()" autofocus="autofocus" required>
                </textarea>
                EOT;
            }
            else if((int)$id_type >= 6 and (int)$id_type <= 8) {
                // FOR IMAGE (max 50MB -> 52428800 Bytes)
                $path = '../images/original/blog/';
                $max_image_size = Config::IMAGE_MAX_FILESIZE['upload'];
                echo <<<EOT
                <input type="hidden" name="content" value="$path">
                <input type="hidden" name="MAX_FILE_SIZE" value="$max_image_size">
                <input type="file" name="file" >
                <input type="hidden" name="upload_path" value="$path">
                EOT;
            }
            else if((int)$id_type >= 9 and (int)$id_type <= 11) {
                // FOR IMAGE WITH CAPTION
                $path = '../images/original/blog/';
                $max_image_size = Config::IMAGE_MAX_FILESIZE['upload'];
                echo <<<EOT
                <input type="hidden" name="content" value="$path">
                <input type="hidden" name="MAX_FILE_SIZE" value="$max_image_size">
                <input type="file" name="file" >
                <input type="hidden" name="upload_path" value="$path">
                <textarea name="caption" style="margin-top: 10px;"
                onfocus="this.select()" autofocus="autofocus" required>
                </textarea>
                EOT;

            }
            echo <<<EOT
            <br>
            <input type="submit" value="Post" style="margin-top: 10px;">
            </form>
            </div>
            EOT;
            Blogpost::end();

        }


        public static function show($id_blog) {

             // GET ALL COLUMNS FOR BLOG ID
            $cnxn = db_connect();
            $result = BlogSQL::get_blog_content($cnxn, $id_blog);

            if($result != false) {
                Blogpost::start();
                foreach($result as $row) {
                    // ITERATE OVER BLOG TYPE AND CHOSE CORRECT ILLUSTRATION METHOD
                    switch($row['id_type']) {
                        case '1';
                            Blogpost::main_title($row['main_title'],'center');
                            break;
                        case '2';
                            Blogpost::main_title($row['main_title'],'left');
                            break;
                        case '3';
                            if($row['main_title'] === '__date__') {
                                // CHECK IF DEFAULT VALUE
                                    // ..TO TRIGGER DATE POSTED IN RIGHT TITLE
                                    // ..IN BLOG POST
                                $date_res = BlogSQL::get_blog_post_dates(
                                    $cnxn, $id_blog
                                );
                                $date = null;
                                $str = null;
                                foreach($date_res as $k => $v) {
                                    if($v != null) {
                                        $date = $v;
                                        $str = $k;
                                    }
                                }
                                if($str = 'date_started' or
                                $str = 'date_posted'
                                ) {
                                    $str = 'Posted: ';
                                }
                                else {
                                    $str = 'Modified: ';
                                }

                                $str = $str . $date;
                                Blogpost::main_title($str,'right');

                            }
                            else {
                                Blogpost::main_title($row['main_title'],'right');
                            }
                            break;
                        case '4';
                            Blogpost::body_title($row['body_title']);
                            break;
                        case '5';
                            Blogpost::body_text($row['body_text']);
                            break;
                        case '6';
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'], 'center'
                            );
                            break;
                        case '7';
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'], 'left'
                            );
                            break;
                        case '8'; //
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'], 'right'
                            );
                            break;
                        case '9';
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'], 'center',
                                $row['img_caption']
                            );
                            break;
                        case '10';
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'], 'left',
                                $row['img_caption']
                            );
                            break;
                        case '11';
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'], 'right',
                                $row['img_caption']
                            );
                            break;
                        default;
                            Log::blog_content_display('Blog content with blog id: ' .
                            $id_blog. ' and content number: ' .$row['id_type'] .
                            ' could not be displayed', 4);

                    }
                }
                Blogpost::end();
            }
            $cnxn = null;
        }
    }

?>
