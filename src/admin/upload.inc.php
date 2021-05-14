<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/layout.inc.php";
?>

<?php
class Upload {

    public static function image($category, $ref_key = 'file') {
        $root_path = $_SERVER["DOCUMENT_ROOT"];


        // AVOID URL CONFLICTS BY REPLACING WHITESPACE WITH UNDERSCORE IN FILENAME
        $file = str_replace(' ', '_', $_FILES[$ref_key]["name"]);


        $upload_path = $root_path.Config::IMAGE_PATHS['upload'].'/'.$category.'/';
        $upload_path = str_replace(' ', '_', $upload_path);

        if(!(is_dir($upload_path))) {mkdir($upload_path, 0775);}

        $target =  $upload_path . $file;

        Log::debug(
        '$target: '.$target. "<br>" .
        '$file: '.$file . "<br>" .
        basename($_FILES[$ref_key]["name"]) . ' ' . basename($_FILES[$ref_key]["name"]) . "<br>"
        );


        $abort = false; // UPLOAD WILL ABORT IF SET TO TRUE
        $ret_msgs = array(); // RETURN MESSAGES IF UPLOAD FAILS

        Log::upload('Starting upload of image file '.$target);


        // GO THORUGH STEPS TO VALIDATE NEW IMAGE

        // FILE SIZE
        if($_FILES[$ref_key]["error"] == 2) {
            $max_file_size = Config::IMAGE_MAX_FILESIZE['upload'];
            Log::upload(
                $file . ': File exceeds '.$max_file_size.
                ' byte size limit!',
                4
            );
            array_push(
                $ret_msgs,
                $file . ': File exceeds '.
                $max_file_size.' byte size limit!'
            );
            $abort = true;
        }

        // FILE EXTENTION
        $file_ext_check = strtolower(pathinfo($target,PATHINFO_EXTENSION));
        if(!(in_array($file_ext_check, Config::FILE_EXT_ALLOWED['image']))) {
            Log::upload($file . ': File extention is not valid', 4);
            array_push(
                $ret_msgs, $file .
                ': File extention is not valid'
            );
            $abort = true;
        }

        // IS IMAGE FILE BASED ON METADATA
        $file_check = getimagesize($_FILES[$ref_key]["tmp_name"]);
        if($file_check == false) {
            Log::upload($file . ': File upload is not an image!', 4);
            array_push($ret_msgs, $file . ': File upload is not an image!');
            $abort = true;
        }

        // CHECK FOR NAME COLLISION OF EXISTING IMAGE REGARDLESS OF FILE EXTENTION
        $file_split = explode(".", $file);
        $image_name = $file_split[0];
        foreach(Config::FILE_EXT_ALLOWED['image'] as $extention) {
            if(file_exists($upload_path.$image_name.'.'.$extention)) {
                echo <<<EOT
                <div class="greybox"><div class="greyboxbody">
                <h2 style="text-align: center;">
                    Image already exists<br>Using existing image
                </h2>
                <p style="text-align: center;">
                    If image is incorrect<br>upload same image<br>with different filename
                </p>
                </div></div>
                EOT;
                Log::upload(
                    $target.' already exists with extention '.
                    $extention,
                    1
                );
                array_push(
                    $ret_msgs, $target.' already exists with extention '.
                    $extention
                );
                $abort = true;
            }
        }

        if(!(is_uploaded_file($_FILES[$ref_key]["tmp_name"]))) {
            $abort = true;
            Log::upload($file . ': Upload aborted due to avoiding a file upload exploit', 2);
        }


        if($abort == false) {
            echo '<div class="greybox">';
            echo '<div class="greyboxbody">';
            if (move_uploaded_file(
                $_FILES[$ref_key]["tmp_name"], $target)
            ) {
                chmod($target,0770);
                echo <<<EOT
                <h3>$file uploaded successfully</h3><br>
                EOT;
                Log::upload(
                    htmlspecialchars($file) . ': Was uploaded to '.$upload_path,
                    1
                );
            } else {
                foreach($ret_msgs as $msg) {
                    echo <<<EOT
                    <h3>$msg</h3><br>
                    EOT;
                }
                Log::upload(
                    'Could not upload ' .htmlspecialchars($file).
                    'to '.$upload_path,
                    1
                );
            }
            echo '</div>';
            echo '</div>';
        }


        if($abort == false) {
            // DO EXTRA CHECK TO ENSURE FILE IS UPLOADED
            // TO AVVOID A FILE UPLOAD ATTACK BEFORE SCRIPT RUNS

            $script = $root_path.Config::IMAGE_SCRIPTS['resize_image'];
            if(!(is_file($script))) {
                Log::upload('Could not resize '.$file.' the script '.$script.' does not exist', '4');
            }

            $output_value = null; // FOR DEBUGGING
            $return_value = null; // FOR DEBUGGING

            exec("python3 $script '$image_name' '$target' '$category' jpg", $output_value, $return_value);

            foreach($output_value as $v) {
                echo <<<EOT

                <div class="greybox"><div class="greyboxbody">
                <h2 style="text-align: center;">$v</h2>
                </div></div>
                EOT;
            }

            if($return_value != 0) {
                Log::image_resize(
                    'Could not resize ' .htmlspecialchars($file).
                    'to '.$upload_path,
                    5
                );
                die('<h2 style="text-align: center;">Could not resize image, check log</h2>');
            }

            Log::image_resize(
                'successfully resized ' .htmlspecialchars($file),
                1
            );
        }


    }
}

?>
