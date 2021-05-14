<?php
require_once "layout.inc.php";
require_once "admin/database.inc.php";
Starthtml::show('Emils Blog');
Header::show(basename(htmlentities($_SERVER['PHP_SELF'])));
?>



<?php

function blogpost_menubar($b = null,$f = null) {
    // DISPLAY MENUBAR FOR JUMPING TO NEXT OR PREVIOUS BLOGPOST
    echo <<<EOT
    <footer>
    <div class="bottombar" style="margin-top: 20px;">
    <div class="navbar">
    EOT;

    if($f != null) {
        echo '<a href="blog.php?id_blog='.$f.'">&lt;-- Newer</a>';
    }
    if($b != null) {
        echo '<a href="blog.php?id_blog='.$b.'">Older --&gt;</a>';
    }
    echo<<<EOT
    </div>
    </div>
    </footer>
    EOT;
}

if(isset($_GET['id_blog'])) {

    // SHOW SPECIFIED BLOG POST
    $cnxn = db_connect();

    // GET NEXT POST
    $stmt = $cnxn->prepare("
        SELECT MIN(id_blog) FROM blog
        WHERE id_status = '2' AND id_blog > :v
        LIMIT 1
    ");
    $stmt->bindParam(':v', $_GET['id_blog']);
    $stmt->execute();
    $next_id = $stmt->fetchColumn(0);

    // GET PREVIOUS POST
    $stmt = $cnxn->prepare("
        SELECT MAX(id_blog) FROM blog
        WHERE id_status = '2' AND id_blog < :v
        LIMIT 1
    ");
    $stmt->bindParam(':v', $_GET['id_blog']);
    $stmt->execute();
    $previous_id = $stmt->fetchColumn(0);

    // GET CURRENT POST
    $stmt = $cnxn->prepare("
        SELECT id_blog FROM blog
        WHERE id_status = '2' AND id_blog = :v
    ");
    $stmt->bindParam(':v', $_GET['id_blog']);
    $stmt->execute();
    $current_id = $stmt->fetchColumn(0);

    $cnxn = null;
    Blog_content::show($current_id);
    blogpost_menubar($previous_id,$next_id);
}

if(!(isset($_GET['id_blog']))) {

    // SHOW LAST BLOG POST
    $next = null;
    $previous = null;

    $cnxn = db_connect();
    $stmt = $cnxn->prepare("
        SELECT id_blog FROM blog WHERE id_status = '2'
        ORDER BY id_blog DESC LIMIT 2
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    switch(count($result)) {
        case 1; // IF ONLY 1 POST EXISTS, DON`T SHOW MENU BAR UNDER POST
            Blog_content::show($result[0]['id_blog']);
            break;
        case 2; // IF 2 EXISTS, SHOW MENUBAR UNDER POST
            Blog_content::show($result[0]['id_blog']);
            blogpost_menubar($result[1]['id_blog']);
            break;
        default;
            Blogpost::start();
            Blogpost::main_title('No blogposts','center');
            Blogpost::end();
    }

    $cnxn = null;


}

?>


<!-- <footer>
<div class="bottombar" style="margin-top: 20px;">
<div class="navbar">
    <a href="blog_admin.php">Back</a>
    <a href="../index.php">Home</a>
</div>
</div>
</footer> -->


<?php
// Footer::show(basename($_SERVER['PHP_SELF']));
Endhtml::show();
?>
