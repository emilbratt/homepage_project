<?php
    require_once "layout.inc.php";
?>

<?php
    Starthtml::show('My homepage');
    Header::show(basename(htmlentities($_SERVER['SCRIPT_NAME'])));
?>

<?php
    $cnxn = db_connect();
    try {
        $stmt = $cnxn->prepare("
            SELECT body_title, body_text FROM front_page
            ORDER BY content_number ASC
        ");
    } catch (Exception $e) {
        Frontpage::start();
        Frontpage::body_title('No content found, is your database installed?');
        Frontpage::body_text('Open host_or_ip/setup/setup.php in your browser and click "CREATE DATABASE"');
        Frontpage::end();
        Endhtml::show();
        exit;
    }
    $stmt->execute();
    $front_page_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if($front_page_data == null) {
        $front_page_data = array(
            [
            'body_title' => 'No frontpage text',
            'body_text' => 'Go to admin panel and add text'
            ],
        );
    }

    $stmt = $cnxn->prepare("
        SELECT * FROM user_data
    ");
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if($user_data == null) {
        echo '<p>No user_data data</p>';
    }

    $profile_pic_name = null;
    if($user_data['profile_pic'] != null) {
        $n = $user_data['profile_pic'];
        $stmt = $cnxn->prepare("
            SELECT image_org.file_name
            FROM image_org
            WHERE id_image =:n
        ");
        $stmt->bindParam(':n', $n);
        $stmt->execute();
        $profile_pic_name = $stmt->fetchColumn(0);

    }

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
    $latest_blogpost = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_blog = null;
    $main_title = null;
    if($latest_blogpost != null) {
        $id_blog = $latest_blogpost['id_blog'];
        $main_title = $latest_blogpost['main_title'];
    }
?>

<?php
    Frontpage::start();
    Frontpage::main_title($user_data['full_name'], 'center');
    Frontpage::text_field($front_page_data);
    Frontpage::profile_pic($profile_pic_name);
    Frontpage::end();

    Frontpage::start('left');
    Frontpage::latest_blogpost($id_blog, $main_title);
    Frontpage::end();

    Frontpage::start('right');
    Frontpage::contact_field($user_data['email']);
    Frontpage::end();
?>

<?php
    $cnxn = null;
    $user_data = null;
    $front_page_data = null;
    $latest_blogpost = null;
?>

<?php
    Footer::show(basename($_SERVER['SCRIPT_NAME']));
    Endhtml::show();
?>
