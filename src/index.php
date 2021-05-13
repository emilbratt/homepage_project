<?php
require_once "layout.inc.php";
// require_once "logs/logging.inc.php";
Starthtml::show('My homepage');
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));

?>

<div class="greybox">

    <div class="greybox_title_center">
    <h3 style="text-align: center;">John Travolta</h3>
    </div>



    <div class="greybox_inline_block">

        <div class="greyboxbody" id="left_side_">
            <h3>Programming</h3>
            <p>
                Check out my projects on this site or on github
                Check out my projects on this site or on github
                Check out my projects on this site or on github
                Check out my projects on this site or on github
            </p>
        </div>
        <br>

        <div class="greyboxbody" id="left_side_">
        <h3>Photographing</h3>
            <p>
                Nature, Weddings, People and more
                Nature, Weddings, People and more
                Nature, Weddings, People and more
                Nature, Weddings, People and more
            </p>
        </div>
        <br>

        <div class="greyboxbody" id="left_side_">
            <h3>Undergrad student</h3>
            <p>I have a special love for computers</p>
        </div>
        <br>
    </div>
    <div class="greybox_inline_block">
        <div class="greyboxbody" id="right_side_" style="text-align: right;">
        <img src="/extra/profile_pic.png" alt="" class="profile_pic" style="">
        <!-- <figcaption style="text-align: left; margin-left: 3%; margin-right: 3%;">
            Image Caption textImage Caption textImage Caption textImage Caption text
        </figcaption> -->
        </div>
    </div>
</div>

<div class="greybox_left">
    <div class="greyboxbody" id="left_side_">
        <!-- <div class="greyboxbody"> -->
        <h3>Contact</h3>
        <div class="standalone_link">
            <p><a href="mailto:johnson@gmail.com">E-mail</a></p>
        </div>
        <!-- </div> -->
    </div>
</div>


<div class="greybox_right">
    <!-- <div class="greyboxbody"> -->
        <div class="greyboxbody" id="right_side_">
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


<?php
Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
