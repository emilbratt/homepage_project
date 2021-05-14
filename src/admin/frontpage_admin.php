<?php
session_start();
require_once $_SERVER["DOCUMENT_ROOT"]."/layout.inc.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/database.inc.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/admin/upload.inc.php";
Starthtml::show('Frontpage Settings');
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));
?>


<?php
// POST REQUEST INSERTS FOR SOCIAL NETWORK LINKS
if(isset($_POST['links'])) {
    $cnxn = db_connect($pragma = false);

    if($_POST['links'] == 'Reset All') {
        Log::front_page(
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
                Log::front_page(
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

if(isset($_POST['textfield'])) {
    if($_POST['textfield'] == 'add') {
        $cnxn = db_connect($pragma = false);
        $n = FrontpageSQL::get_next_content_number($cnxn);
        $stmt = $cnxn->prepare("
            INSERT INTO front_page
                (content_number, body_title, body_text)
            VALUES
                (:n, :t, :p)
        ");
        $t = $_POST['title'];
        $p = $_POST['paragraph'];
        $stmt->bindParam(':n', $n);
        $stmt->bindParam(':t', $t);
        $stmt->bindParam(':p', $p);
        $stmt->execute();
        $cnxn = null;
        Log::front_page('Added text field with content number: ' . $n);
    }
    if($_POST['textfield'] == 'swap') {

        $cnxn = db_connect($pragma = false);
        FrontpageSQL::swap_text($cnxn, $_POST['swap_1'], $_POST['swap_2']);
        $cnxn = null;
        Log::front_page('Swapped text field content number: ' .
        $_POST['swap_1'].' with ' .$_POST['swap_1']);
    }
    if($_POST['textfield'] == 'delete') {
        $cnxn = db_connect($pragma = false);
        FrontpageSQL::delete_text($cnxn, $_POST['content_number']);
        $cnxn = null;
        Log::front_page('Deleted text field with content number: ' .
        $_POST['content_number'].' ');
    }
}

if(isset($_POST['profile_pic'])) {
    Upload::image('profile', 'file');
}


?>

<?php
// DISPLAY FORMS FOR CONTENT
Frontpage_content::text_field_left();
Frontpage_content::profile_pic();
Frontpage_content::social_network();
?>



<?php
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
