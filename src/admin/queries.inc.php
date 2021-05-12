<?php
class FrontpageSQL {

    public static function get_footer_links($cnxn) {
        $stmt = $cnxn->prepare("
        SELECT * FROM social_networks
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

}

class BlogSQL {

    public static function get_last_blog_id($cnxn) {
        $stmt = $cnxn->prepare("
        SELECT CASE
            WHEN MAX(id_blog) IS NULL THEN '1'
            WHEN MAX(id_blog) ='0' THEN '1'
            ELSE MAX(id_blog) + 1
        END AS next_id_blog
        FROM blog
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function get_blog_status($cnxn, $id_blog) {
        $stmt = $cnxn->prepare("
        SELECT desc_status
        FROM blog
        LEFT JOIN blog_status
        ON blog.id_status = blog_status.id_status
        WHERE blog.id_blog = :v;
        ");
        $stmt->bindParam(':v', $id_blog);
        $stmt->execute();
        $result = $stmt->fetchColumn(0);
        return $result;
    }

    public static function change_blog_status($cnxn, $id_blog, $id_status) {
        $stmt = $cnxn->prepare("
            UPDATE blog
            SET id_status = :a
            WHERE id_blog = :v
        ");
        $stmt->bindParam(':v', $id_blog);
        $stmt->bindParam(':a', $id_status);
        $stmt->execute();
    }

    public static function get_blog_status_types($cnxn) {
        $stmt = $cnxn->prepare("
        SELECT *
        FROM blog_status
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }



    public static function get_descriptions($cnxn, $status = null) {
        if($status == null) {
            // GET ALL BLOG POSTS DESCRIPTIONS
            $stmt = $cnxn->prepare("
            SELECT id_blog, description
            FROM blog
            ");
        }
        else {
            // GET IN-ACTIVE OR ACTIVE BLOG POST DESCRIPTIONS
                //.. BASED ON THE PASSED $status CODE 0 or 1
            $stmt = $cnxn->prepare("
            SELECT id_blog, description
            FROM blog
            WHERE id_status = :code
            ");
            $stmt->bindParam(':code', $status);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function insert_base_data($cnxn, $dsc,$tags,$title,$d,$t) {
        $stmt = $cnxn->prepare("
            INSERT INTO blog
                (description,tags,date_started,time_started)
            VALUES
                (:description,:tags,:date_started,:time_started)
        ");
        $stmt->bindParam(':description', $dsc);
        $stmt->bindParam(':tags', $tags);
        $stmt->bindParam(':date_started', $d);
        $stmt->bindParam(':time_started', $t);
        $stmt->execute();

        $id = $cnxn->lastInsertId();
        $stmt = $cnxn->prepare("
        INSERT INTO blog_content
            (id_blog, id_type, content_number, main_title)
        VALUES
            (:a, '2', '1', :d) -- id_type = title on the left side
        ");
        $stmt->bindParam(':a', $id);
        $stmt->bindParam(':d', $title);
        $stmt->execute();

        $stmt = $cnxn->prepare("
        INSERT INTO blog_content
            (id_blog, id_type, content_number, main_title)
        VALUES
            (:a, '3', '2', '__date__') -- id_type = title on the right side
        ");
        $stmt->bindParam(':a', $id);
        $stmt->execute();


    }

    public static function get_blog_content($cnxn, $id_blog = null) {
        if(empty($id_blog)) {
            return false;
        }
        else {
            $stmt = $cnxn->prepare("
            SELECT *
            FROM blog_content
            WHERE id_blog = :v
            ORDER BY content_number
            ");
            $stmt->bindParam(':v', $id_blog);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }

    }

    public static function get_blog_content_and_type($cnxn, $id_blog = null) {
        if(empty($id_blog)) {
            return false;
        }
        else {
            $stmt = $cnxn->prepare("
            SELECT * FROM blog_content
            INNER JOIN blog_content_type
            ON blog_content.id_type = blog_content_type.id_type
            WHERE id_blog = :v ORDER BY content_number;
            ");
            $stmt->bindParam(':v', $id_blog);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }

    }

    public static function get_all_content_types($cnxn) {
        $stmt = $cnxn->prepare("
            SELECT id_type, desc_type, alias
            FROM blog_content_type
            ORDER BY id_type
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function get_next_content_number($cnxn, $id_blog) {
        $stmt = $cnxn->prepare("
            SELECT CASE
                WHEN MAX(content_number) IS NULL THEN '1'
                ELSE MAX(content_number) + 1
            END AS next_content_number
            FROM blog_content
            WHERE id_blog =:v
        ");
        $stmt->bindParam(':v', $id_blog);
        $stmt->execute();
        $result = $stmt->fetchColumn(0);
        // $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
        // return $result['next_content_number'];
    }

    public static function get_blog_post_dates($cnxn, $id_blog) {
        $stmt = $cnxn->prepare("
            SELECT
                date_mod,
                date_posted,
                date_started
            FROM blog
            WHERE id_blog =:v
        ");
        $stmt->bindParam(':v', $id_blog);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function get_blog_post_times($cnxn, $id_blog) {
        $stmt = $cnxn->prepare("
            SELECT
                time_mod,
                time_posted,
                time_started
            FROM blog
            WHERE id_blog =:v
        ");
        $stmt->bindParam(':v', $id_blog);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function get_blog_post_title($cnxn, $id_blog) {
        $stmt = $cnxn->prepare("
            SELECT main_title FROM blog_content
            WHERE id_blog =:v AND content_number = '1'
            ");
        $stmt->bindParam(':v', $id_blog);
        $stmt->execute();
        $result = $stmt->fetchColumn(0);
        return $result;
    }

    public static function swap_blog_content($cnxn, $id_blog, $c_n_a, $c_n_b) {
        $stmt = $cnxn->prepare("
            UPDATE blog_content
            SET content_number = '0'
            WHERE id_blog = :v AND content_number = :a

        ");
        $stmt->bindParam(':v', $id_blog);
        $stmt->bindParam(':a', $c_n_a);
        $stmt->execute();
        $stmt = $cnxn->prepare("
            UPDATE blog_content
            SET content_number = :a
            WHERE id_blog = :v AND content_number = :b

        ");
        $stmt->bindParam(':v', $id_blog);
        $stmt->bindParam(':a', $c_n_a);
        $stmt->bindParam(':b', $c_n_b);
        $stmt->execute();

        $stmt = $cnxn->prepare("
            UPDATE blog_content
            SET content_number = :b
            WHERE id_blog = :v AND content_number = '0'
        ");
        $stmt->bindParam(':v', $id_blog);
        $stmt->bindParam(':b', $c_n_b);
        $stmt->execute();




    }

}


class ImageSQL {
    public static function get_all_targets($cnxn) {
        $stmt = $cnxn->prepare("SELECT target FROM image");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $result;
    }

    public static function get_image_resize_target($cnxn, $img_name, $category) {
        // PDO WILL NOT ALLOW % PLACEHOLDERS INSIDE prepare STATEMENTS
        // SHOULD BE OK SINCE THIS IS ONLY CALLED BY USER WITH ADMIN PRIVILEGES


        $stmt = $cnxn->prepare("
        SELECT
            image_resize.resize_target,
            image_resize.long_edge
        FROM
            image_resize
        INNER JOIN
            image_org
        ON
            image_resize.id_image = image_org.id_image
        WHERE
            image_org.file_name = :name
        AND
        	image_org.category = :category
        ");
        $stmt->bindParam(':name', $img_name);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

}

?>
