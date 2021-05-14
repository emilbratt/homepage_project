<?php
session_start();
require_once $_SERVER["DOCUMENT_ROOT"]."/layout.inc.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/database.inc.php";
Starthtml::show('Frontpage Settings');
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));
?>


<?php
// POST REQUEST INSERTS FOR SOCIAL NETWORK LINKS
if(isset($_POST['links'])) {
    $cnxn = db_connect();

    if($_POST['links'] == 'Reset All') {
        Log::user_settings(
            'All links deleted', 1
        );
        $stmt = $cnxn->prepare("
        DELETE FROM social_networks
        ");
        $stmt->execute();
    }
    if($_POST['links'] == 'Update') {
        $stmt = $cnxn->prepare("
        UPDATE social_networks
        SET url = :v
        WHERE name = :k
        ");
        foreach($_POST as $k => $v) {
            if( $k != 'links' and $v != null) {
                Log::user_settings(
                    'URL: '. $v . ' for ' . $k .
                    ' was inserted into database', 1
                );
                $stmt->bindParam(':v', $v);
                $stmt->bindParam(':k', $k);
                $stmt->execute();
            }
        }
        $cnxn = null;
    }

}

if(isset($_POST['paragraphs'])) {

}

if(isset($_POST['profile_pic'])) {

}


?>

<?php
$icons_dir = $_SERVER["DOCUMENT_ROOT"].Config::IMAGE_PATHS['logos'];
$icon_images = glob($icons_dir."*");
$icons = scandir($icons_dir);
?>


    <div class="greybox">
    <div class="greyboxbody">
    <h1>Add paragraphs on homepage</h1>
    <form action=<?php echo htmlentities($_SERVER['PHP_SELF']);?>
    method="post" id="in_line_position_greyboxbody">
    <input type="hidden" name="links" value="true">
    <?php
        foreach($icons as $icon) {
            if($icon != '.' and $icon != '..') {
                // $path = Config::IMAGE_PATHS['logos'].$icon;
                echo "<br>";

                $icon = explode('.',$icon)[0];
                echo "<h3>$icon</h3>";

            }
        }
    ?>
    <br><br>
    <input type="submit" style="width: 270px;" value="Insert">
    </form>
    </div>
    </div>


    <div class="greybox">
    <div class="greyboxbody">

    <h1>Links for Social Networks</h1>
    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ;?>"
    method="post" id="in_line_position_greyboxbodsy">
    <input type="hidden" name="links" value="true">
    <?php
        $cnxn = db_connect();
        $stmt = $cnxn->prepare("
        SELECT * FROM social_networks
        -- WHERE name = :v
        ");
        $stmt->execute();
        $name_exists = array(); // STORE NAMES ONLY
        $networks = array();    // STORE NAMES AND URL
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
                        // INSERT NAME INTO DATABASE IF NAME DOESN`T EXIST
                        Log::user_settings(
                            $icon . ': Was inserted into database', 1
                        );
                        $stmt = $cnxn->prepare("
                        INSERT INTO social_networks (name)
                        VALUES (:v)
                        ");
                        $stmt->bindParam(':v', $icon);
                        $stmt->execute();
                    }

                    $placeholder = null;
                    if(isset($networks[$icon])) {
                        $placeholder = $networks[$icon];
                    }
                    if($placeholder == null) {
                        $placeholder = "https://$icon";
                    }
                    ?>
                    <div id="in_line_position_greyboxbody">
                    <h3><?php echo $icon; ?></h3>

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
    </div>

<?php
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
