<?php
// function db_connect() {
//     $usr = 'emil';
//     $psw = 'password';
//     $host = '127.0.0.1';
//     $port = '3306';
//     $cnxn = 'emil';
//     try {
//         $cnxn = new PDO(
//             "mysql:host=$host;dbname=$cnxn;" .
//             "port=$port",$usr,$psw
//         );
//         // $cnxn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
//         $cnxn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     }
//     catch(Exception $e)
//     {
//         echo '<h2>';
//         print_r($e->getMessage());
//         echo '</h2>';
//         die;
//     }
//     return $cnxn;
// }
?>


<?php
function db_connect($pragma = true) {
    $db_path = Config::DATABASE_PATH;
    if(!(file_exists($db_path))) {
        $db_path = $_SERVER["DOCUMENT_ROOT"].Config::DATABASE_PATH;
    }
    try {
        $cnxn = new PDO("sqlite:".$_SERVER["DOCUMENT_ROOT"].Config::DATABASE_PATH);
        if($pragma == true) {
            $cnxn->exec('PRAGMA foreign_keys = ON;');
        }
        $cnxn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(Exception $e)  {
        echo '<h2>';
        print_r($e->getMessage());
        echo '</h2>';
        die;
    }
    return $cnxn;
}


// function sqlite_prepare() {
//     $output_value=null;
//     $return_value=null;
//     exec('touch database.sqlite', $output_value, $return_value);
//     if($return_value != 0) {
//         die('could not create sqlite database');
//     }
//     echo $return_value."<br>";
//     exec('sqlite3 database.sqlite ".read sqlite_prepare.sql" ', $output_value, $return_value);
//     if($return_value != 0) {
//         die('Check script for errors');
//         foreach($output_value as $v) {
//             print_r($v);
//             echo "<br>";
//         }
//     }
//     echo $return_value."<br>";
//     die('OK');
//
// }

?>
