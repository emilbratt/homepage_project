<?php
die;
require_once "queries.inc.php";
require_once "database.inc.php";

$cnxn = db_connect();
$existing_paths = ImageSQL::get_all_targets($cnxn);


// PREPARE INSERT STATEMENT
$stmt = $cnxn->prepare("
    INSERT INTO image
    (
        name,file_name,format,aspect,target,category, category_dir, width
    )
    VALUES
    (
        :name,:file_name,:format,:aspect,:target,:category,:category_dir,:width
    )
");

// IMAGE BASE PATH
// $base_dir = __DIR__."/images/converted/*";
$base_dir = "../images/converted/*";
// ITERATE THROUGH ALL SUB FOLDERS - category->resolution->imagefile
$categories_folders = glob($base_dir);
foreach($categories_folders as $category_folder) {
    $res_folders = glob($category_folder."/*");
    foreach($res_folders as $res_folder) {
        $images = glob($res_folder."/*");
        foreach($images as $target) {
            if(!(in_array ($target,$existing_paths ))) {
                $cat = basename($category_folder);
                $res = basename($res_folder);
                $file_name = basename($target);
                $aspect = explode('.',$file_name)[0];
                $name = explode('.',$file_name)[1];
                $format = explode('.',$file_name)[2];

                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':file_name', $file_name);
                $stmt->bindParam(':format', $format);
                $stmt->bindParam(':aspect', $aspect);
                $stmt->bindParam(':target', $target);
                $stmt->bindParam(':category', $cat);
                $stmt->bindParam(':category_dir',$category_folder);
                $stmt->bindParam(':width', $res);
                $stmt->execute();

            }
        }
    }
}

$cnxn = null;
?>
