<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/admin/database.inc.php";
    require_once "admin/queries.inc.php";
    require_once "admin/config.inc.php";
    // require_once "admin/file_upload.inc.php";
    require_once "admin/logging.inc.php";
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
            'Frontpage' => 'frontpage_admin.php',
            'Blog' => 'blog_admin.php',
            'Create' => 'blog_create.php',
            // 'Preview' => 'blog_preview.php',
            // 'Scan Photos' => 'image_scan.php',
            'Logs' => 'log_admin.php',
            'Preview' => 'blog_preview.php',
        );

        public static $excluded_menu_admin_pagess = array(
            'Create', 'Preview'
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
                foreach($results as $row) {
                    if(!(empty($row['url']))) {
                        echo '
                            <a href="'.$row['url'].'">
                                <img src="logos/' . $row['name'] . '.png" alt="no image"
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

    // class Logpost {
    //     public static function start() {
    //         echo '<div class="greybox">';
    //     }
    //
    //     public static function end() {
    //         echo '</div>';
    //     }
    //
    // }

    class Display {
        public static function start() {
            echo '<div class="greybox">';
        }

        public static function end() {
            echo '</div>';
        }

        public static function table() {

        }

    }

    class Blogpost extends Display {

        private static $media_query_medium = 'min-width:800px';
        private static $media_query_large = 'min-width:1200px';

        // public static function start() {
        //     echo '<div class="greybox">';
        // }

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
                echo '<source media="('.Blogpost::$media_query_large.')" '.
                'loading="lazy" srcset="'.$targets[1].
                '">';

                echo '<source media="('.Blogpost::$media_query_medium.')" '.
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
                echo '<source media="('.Blogpost::$media_query_large.')" '.
                'loading="lazy" srcset="'.$targets[1].
                '">';

                echo '<source media="('.Blogpost::$media_query_medium.')" '.
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

        // public static function end() {
        //     echo '</div>';
        // }

    }



    class Blogcontent {

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
                <input type="file" name="content_file" >
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
                <input type="file" name="content_file" >
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
                        case '6'; //          break; // REMOVE break WHEN IMAGE UPLOAD DONE
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'], 'center'
                            );
                            break;
                        case '7'; //          break; // REMOVE break WHEN IMAGE UPLOAD DONE
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'], 'left'
                            );
                            break;
                        case '8'; //          break; // REMOVE break WHEN IMAGE UPLOAD DONE
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'], 'right'
                            );
                            break;
                        case '9';
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'],
                                'center', $row['img_caption']
                            );
                            break;
                        case '10';
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'],
                                'left', $row['img_caption']
                            );
                            break;
                        case '11';
                            Blogpost::body_image(
                                $row['img_name'], $row['img_folder'],
                                'right', $row['img_caption']
                            );
                            break;
                        default;
                            Log::blog_content_display($row['img_name'].' could not be displayed', 4);

                    }
                }
                Blogpost::end();
            }
            $cnxn = null;
        }
    }

?>
