<?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/admin/database.inc.php";
?>

<?php
class Log {

    private $levels = [
        'info' => '1',
        'Warning' => '2',
        'Debug' => '3',
        'Error' => '4',
        'Fatal' => '5'
    ];

    private static function insert_log($m, $l, $s) {
        $cnxn = db_connect();
        $stmt = $cnxn->prepare("
        INSERT INTO logging
            (message, id_log_level, subject)
        VALUES
            (:a,:b, :c)
        ");
        $stmt->bindParam(':a', $m);
        $stmt->bindParam(':b', $l);
        $stmt->bindParam(':c', $s);
        $stmt->execute();
        $cnxn = null;
    }

    public static function debug($msg, $level = 2) {
        $subject = 'Debugging';
        Log::insert_log($msg,$level,$subject);
    }

    public static function upload($msg, $level = 2) {
        $subject = 'File Upload';
        Log::insert_log($msg,$level,$subject);
    }

    public static function front_page($msg, $level = 1 /*1 = info*/) {
        $subject = 'Front-Page';
        Log::insert_log($msg,$level,$subject);
    }

    public static function user_settings($msg, $level = 1 /*1 = info*/) {
        $subject = 'User Settings';
        Log::insert_log($msg,$level,$subject);
    }

    public static function image_resize($msg, $level = 2) {
        $subject = 'Image Resizing';
        Log::insert_log($msg,$level,$subject);
    }

    public static function blog_content_display($msg, $level = 2) {
        $subject = 'Blog content display';
        Log::insert_log($msg,$level,$subject);
    }

}
?>
