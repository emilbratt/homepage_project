<?php

    class Config {

        // setup.php WILL ONLY LOAD IF false, SET TO true IF IN PRODUCTION
        public const SETUP_DISABLE = false;

        // REFERENCES PATH/FILE FOR DATABASE CONNECTION
        public const DATABASE_PATH = "/admin/database.sqlite";

        // SET ALGORITHM FOR PASSWORD-HASHING
        public const PWD_HASH_METHOD = PASSWORD_ARGON2ID;
            // MORE OPTIONS: https://www.php.net/manual/en/function.password-hash.php

        // ALLOWED HOST CONNETIONS TO FETCH CONFIG VALUES
        public const CONFIG_FETCH_ALLOWED_HOSTS = [
            '127.0.0.1',    // IPv4 loopback
            '::1'           // IPv6 loopback
        ];

        // IMAGE DEFAULT PATHS
        public const IMAGE_PATHS = [
            'upload' => '/images/original/',
            'converted' => '/images/converted/',
            'logos' => '/logos/'
        ];

        // SET MAX LONG EDGE SIZE IN PIXELS FOR IMAGE RES
        // TO BE DISPLAYED WHEN IMAGES ARE LOADED ON WEB PAGE
        public const IMAGE_RES = [
            'base' => '800',
                // ..phones and other small handhield devices

            'min-width:800px' => '1200',
                // ..netbooks and other medium sized mobile devices

            'min-width:1200px' => '1600',
                // ..desktops, laptops etc
        ];

        // ALLOWED FILETYPES FOR FILE UPLOAD
        public const FILE_EXT_ALLOWED = [
            'image' => array('jpg','jpeg','png','tiff','gif','bmp'),
            'text' => array('txt','md','log')
        ];

        // MAX ALLOWED FILESIZE IN BYTES FOR IMAGE UPLOAD
        public const IMAGE_MAX_FILESIZE = [
            'upload' => 52428800
        ];

        public const IMAGE_SCRIPTS = [
            'resize_image' => '/admin/resize_image.py'

        ];

    }


    // ALLOW REMOTE CONNECTION TO REQUEST CONFIG VALUES WITH HTTP GET
    // SENSITIVE DATA SHOULD BE CAREFULLY CONSIDERED BEFORE LISTED HERE
    if(
        isset($_GET['config']) and
        in_array($_SERVER['REMOTE_ADDR'] , Config::CONFIG_FETCH_ALLOWED_HOSTS)
    ) {

        // URL ./admin/config.inc.php?config=IMAGE_MAX_FILESIZE
        if($_GET['config'] == 'IMAGE_MAX_FILESIZE') {
            header("Content-Type: application/json");
            echo json_encode(IMAGE_MAX_FILESIZE);
        }
        // URL ./admin/config.inc.php?config=IMAGE_RES
        if($_GET['config'] == 'IMAGE_RES') {
            header("Content-Type: application/json");
            echo json_encode(Config::IMAGE_RES);
        }

    }


    // ALLOW FETCHING CONFIG VALUES FROM SHELL COMMAND (PHP CLI)
    if(isset($argv[1])) {
        $arg = $argv[1];

        if($arg == 'IMAGE_RES') {
            foreach(Config::IMAGE_RES as $res) {
                echo ',' . $res;
            }
        }
    }
