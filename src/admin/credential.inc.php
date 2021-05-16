<?php

    class Credential {
        public static function verify_session() {
            if(isset($_SESSION['usr'])) {
                if(isset($_SESSION['verified_login'])) {
                    if($_SESSION['verified_login'] === true) {
                        return true;
                    }
                }
            }
            return false;
        }

        public static function verify_credential($usr, $pwd) {
            $cnxn = db_connect($pragma = false);
            $stmt = $cnxn->prepare('
                SELECT user_name, password FROM user_data
            ');
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cnxn = null;


            if(password_verify($pwd, $row['password']) and $usr == $row['user_name']) {
                $_SESSION['verified_login'] = true;
                $_SESSION['usr'] = $usr;
                return true;
            }
            return false;
        }

        public static function change_user($usr) {
            $cnxn = db_connect();
            $stmt = $cnxn->prepare("
            UPDATE user_data SET user_name =:u
            ");
            $stmt->bindParam(':u', $usr);
            $stmt->execute();
        }

        public static function change_pwd($pwd) {
            $pwd_hash = password_hash($pwd, Config::PWD_HASH_METHOD);
            $cnxn = db_connect();
            $stmt = $cnxn->prepare("
            UPDATE user_data SET password =:p
            ");
            $stmt->bindParam(':p', $pwd_hash);
            $stmt->execute();

        }

    }



?>
