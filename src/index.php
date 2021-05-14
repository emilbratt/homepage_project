<?php
require_once "layout.inc.php";
// require_once "logs/logging.inc.php";
Starthtml::show('My homepage');
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));
?>

<?php

$cnxn = db_connect();
$stmt = $cnxn->prepare("
    SELECT body_title, body_text FROM front_page
    ORDER BY id_content ASC
");
$stmt->execute();
$front_page_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $cnxn->prepare("
    SELECT * FROM user_data
");
$stmt->execute();
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
if($front_page_data == null) {
    echo '<p>No frontpage data</p>';
}
if($user_data == null) {
    echo '<p>No user_data data</p>';
}
$cnxn = null;

?>

<div class="greybox">

    <div class="greybox_title_center">
        <h3 style="text-align: center;"><?php echo $user_data['full_name'] ?></h3>
    </div>

    <div class="greybox_inline_block">
        <div class="greyboxbody" id="left_side">
        <?php
        foreach($front_page_data as $k => $v) {
            echo '<h3>'.$v['body_title'].'</h3>';
            echo '<p>'.$v['body_text'].'</p><br>';
        }
        ?>
        </div>
    </div>

    <div class="greybox_inline_block">
        <div class="greyboxbody"  style="text-align: right;">
            <img src="<?php echo $user_data['profile_pic'] ?>"
            id="right_side" alt="" class="profile_pic">
        </div>
    </div>
</div>




<div class="greybox_left">
    <!-- <div class="greyboxbody"> -->
    <div class="greybox_right_body" id="right_side_">
        <h3>Latest blogpost</h3>
        <?php
        $cnxn = db_connect();
        $stmt = $cnxn->prepare("
            SELECT blog_content.id_blog, blog_content.main_title
            FROM blog_content
            INNER JOIN blog
            ON blog_content.id_blog = blog.id_blog
            WHERE blog.id_status = '2'
            AND  blog_content.content_number = '1'
            ORDER BY blog.id_blog DESC
            LIMIT 1
            ;
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result == null) {
            echo '<p>No blogposts</p>';
        }
        echo '
            <div class="standalone_link">
                <p><a  href="/blog.php?id_blog='.$result['id_blog'].'">'.
                $result['main_title'].'</a></p>
            </div>
            ';
        ?>
    </div>
    <!-- </div> -->
</div>
<div class="greybox_right">
    <div class="greybox_left_body" id="left_side_">
        <h3>Contact</h3>
        <div class="standalone_link">
        <p><a href="mailto:<?php echo $user_data['full_name'] ?>">E-mail</a></p>
        </div>
    </div>
</div>

<?php
$user_data = null;
$front_page_data = null;
?>

<?php
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
