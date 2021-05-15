<?php
require_once "../layout.inc.php";
require_once "../admin/database.inc.php";
require_once "../admin/config.inc.php";
?>
<?php Starthtml::show('Website Setup'); ?>



<div class="greybox">
    <div class="greyboxbody">
        <h1>Database Setup</h1>
        <form action="setup.php" method="post" id="in_line_position_greyboxbody">
            <input type="hidden" name="setup" value="db_prepare">
            <h3>Prepares the database</h3>
            <input type="submit" style="width: 270px;" value="Create">
        </form>

        <form action="setup.php" method="post" id="in_line_position_greyboxbody">
            <input type="hidden" name="setup" value="db_erase">
            <h3>Deletes the database</h3>
            <input type="submit" style="width: 270px; margin-top: 0px;" value="Delete">
        </form>
    </div>
</div>

<div class="greybox">
    <div class="greyboxbody">
        <h1>Image Setup</h1>
        <form action="setup.php" method="post" id="in_line_position_greyboxbody">
            <input type="hidden" name="setup" value="image_resize">
            <h3>Batch resize all images</h3>
            <input type="submit" style="width: 270px; " value="Resize All Images">
        </form>

        <form action="setup.php" method="post" id="in_line_position_greyboxbody">
            <input type="hidden" name="setup" value="image_delete_all_files">
            <h3>Delete all image files</h3>
            <input type="submit" style="width: 270px; margin-top: 0px;" value="Delete Images">
        </form>
    </div>
</div>

<?php
$db_path = Config::DATABASE_PATH;
if(!(file_exists($db_path))) {
    $db_path = $_SERVER["DOCUMENT_ROOT"].Config::DATABASE_PATH;
}

if(isset($_POST['setup'])) {
    echo '<div class="greybox">';
    switch($_POST['setup']) {
        // PREPARE DATABASE
        case 'db_prepare';

            echo '    <div class="greyboxbody">';

            // SKIP IF DATABASE EXISTS
            if(file_exists($db_path)) {
                echo '<h2 style="text-align: center;">DATABASE EXISTS</h2>';
                echo '</div>';
                break;
            }
            // CREATE DATABASE
            $output_value = null; // FOR DEBUGGING
            $return_value = null; // FOR DEBUGGING
            exec('touch '.$db_path, $output_value, $return_value);
            if($return_value != 0) {
                die('could not create sqlite database file (maybe permission issues)');
            }
            exec('sqlite3 '.$db_path.' ".read sqlite_prepare.sql" ', $output_value, $return_value);
            if($return_value != 0) {
                die('Check script for errors');
            }

            echo '<h2 style="text-align: center;">DATABASE CREATED</h2>';
            echo '</div>';
            break;
        case 'db_erase';
            echo '    <div class="greyboxbody">';
            unlink($db_path);
            echo '<h2 style="text-align: center;">DATABASE DELETED</h2>';
            echo '</div>';
            break;
        case 'image_resize';
            echo <<<EOT
                <div class="greyboxbody">
            <h1>This might take a some time</h1>

            EOT;
            $output_value = null; // FOR DEBUGGING
            $return_value = null; // FOR DEBUGGING
            exec('python3 ../images/batch_resize.py', $output_value, $return_value);
            // exec('python3 ../images/batch_resize_images.py', $output_value, $return_value);
            foreach($output_value as $v) {
                echo '<h2 style="text-align: center;">'.$v.'</h2>';
            }
            echo '</div>';
            break;
        case 'image_delete_all_files';
            echo '    <div class="greyboxbody">';
            foreach(Config::IMAGE_PATHS as $dir) {
                if($dir == Config::IMAGE_PATHS['converted']) {
                    $root_dir = $_SERVER["DOCUMENT_ROOT"].$dir;
                    if(is_dir($root_dir)) {
                        $res_folders = glob($root_dir."*/");
                        foreach($res_folders as $res_folder) {
                            if(file_exists($res_folder)) {
                                $category_folder = glob($res_folder."*/");
                                foreach($category_folder as $category_path) {
                                    if(file_exists($category_path)) {
                                        $contents = scandir($category_path);
                                        foreach($contents as $content) {
                                            if(is_file($category_path.$content)) {
                                                unlink($category_path.$content);
                                                echo 'Deleted:'.$category_path.$content.'<br>';
                                            }
                                        }
                                    }
                                }
                                if(isset($category_path)) {rmdir($category_path);}
                            }
                            if(isset($res_folder)) {rmdir($res_folder);}
                        }
                    }
                }
                else if($dir == Config::IMAGE_PATHS['upload']) {
                    $root_dir = $_SERVER["DOCUMENT_ROOT"].$dir;
                    $category_folder = glob($root_dir."*/");
                    foreach($category_folder as $category_path) {
                        if(file_exists($category_path)) {
                            $contents = scandir($category_path);
                            foreach($contents as $content) {
                                if(is_file($category_path.$content)) {
                                    unlink($category_path.$content);
                                    echo 'Deleted:'.$category_path.$content.'<br>';
                                }
                            }
                        }
                    }
                    if(isset($category_path)) {rmdir($category_path);}
                }
            }
            echo "<h1>All Images Deleted</h1>";
            echo '</div>';

            break;
    }
    echo '</div>';
}
else {
    echo <<<EOT
    <div class="greybox">
    <div class="greyboxbody">
        <h1>Output Messages</h1>
        <p>This block will output info when you press one of the buttons..</p>
    </div>
    </div>
    EOT;
}
?>

<div class="greybox">
    <div class="greyboxbody">
    <h1>Server Setup</h1>
<pre>

----------------FOR DEBIAN BUSTER----------------

LINES IN PHP CONFIG -> php.ini
    extension=pdo_odbc
    extension=pdo_pgsql
    extension=pdo_sqlite
    extension=pgsql
    file_uploads = On ;enable file upload
    upload_max_filesize = 50M ;max allowed file size for uploading files
    post_max_size = 50M ;mainly used for uploading images
    max_input_time = 60 ;seconds a script is allow to run parsing upload data (files)
    memory_limit = 256M ;max amount of memory a single php script can consume

FOR SQLITE
install packages (change to correct php-version)
    sqlite3
    php7.3-sqlite3


ADD WRITE PERMISSIONS
    /admin
    /logs
    /images


IMAGE RESIZE SCRIPT DEPENDENCIES
    $ sudo apt update && sudo apt install python3-pip -y
    $ sudo python3 -m pip install --upgrade pip
    $ sudo python3 -m pip install --upgrade Pillow
        # might not need.. -> $ sudo apt install php7.3-cli -y # change version number if needed



----------------FOR RASPBERRY PI OS LITE----------------

WEB SERVER
    Install a functoning LAMP server (with apache or nginx)
    NOTE: you do not need the database as this webserver runs sqlite as backend
    $ sudo apt update
    $ sudo apt-get install apache2 -y
    $ sudo apt-get install php -y
    $ sudo apt install libapache2-mod-php
    $ sudo systemctl restart apache2
    $ a2enmod php

IMAGE SCRIPT DEPENDENCIES
    $ sudo apt update
    $ sudo apt install python3-pip -y
    $ sudo python3 -m pip install --upgrade pip
    $ sudo python3 -m pip install --upgrade Pillow
    $ sudo apt install libopenjp2-7
    $ sudo apt install libtiff5
        # might not need.. -> $ sudo apt install php7.3-cli -y # change version number if needed

LINES IN PHP CONFIG -> php.ini
    extension=pdo_sqlite
    file_uploads = On ;enable file upload
    upload_max_filesize = 50M ;max allowed file size for uploading files
    post_max_size = 50M ;mainly used for uploading images
    max_input_time = 60 ;seconds a script is allow to run parsing upload data (files)
    memory_limit = 256M ;max amount of memory a single php script can consume

FOR SQLITE
install packages (change to correct php-version)
    #sqlite3
    #php7.3-sqlite3


ADD WRITE PERMISSIONS
    /admin
    /logs
    /images


</pre>
</div>
</div>





<?php Endhtml::show(); ?>
