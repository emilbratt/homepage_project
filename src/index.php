<?php
    require_once "layout.inc.php";

    Starthtml::show('My homepage');
    Header::show(basename(htmlentities($_SERVER['SCRIPT_NAME'])));

    $cnxn = db_connect();
    try {
        $stmt = $cnxn->prepare("
            SELECT body_title, body_text FROM front_page
            ORDER BY content_number ASC
        ");
    } catch (Exception $e) {
        echo Display::start();
        Display::body_title('No content found, is your database installed?');
        Display::body_text('Open "yourhost/setup/setup.php" in your browser and click "CREATE DATABASE"');
        Display::end();
        echo Endhtml::show();
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

    echo Display::start();
    Display::main_title($user_data['full_name'], 'center');
    Display::text_field($front_page_data);
    Display::profile_pic($profile_pic_name);
    echo Display::end();

    echo Display::start('left');
    Display::latest_blogpost($id_blog, $main_title);
    echo Display::end();

    echo Display::start('right');
    Display::contact_field($user_data['email']);
    echo Display::end();

    $cnxn = null;
    $user_data = null;
    $front_page_data = null;
    $latest_blogpost = null;

    Footer::show(basename($_SERVER['SCRIPT_NAME']));
    Endhtml::show();
